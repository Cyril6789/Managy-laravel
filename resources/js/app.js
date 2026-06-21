/**
 * Alpine is bundled with Livewire, so we DON'T import/start it ourselves.
 * Custom stores / data / helpers are registered on the `alpine:init` event.
 */
document.addEventListener('alpine:init', () => {
    const Alpine = window.Alpine;

    // Theme store: light / dark with persistence (initial class applied inline in <head>).
    Alpine.store('theme', {
        dark: document.documentElement.classList.contains('dark'),
        toggle() {
            this.dark = !this.dark;
            document.documentElement.classList.toggle('dark', this.dark);
            localStorage.setItem('theme', this.dark ? 'dark' : 'light');
        },
    });

    /**
     * Address autocomplete backed by the French Base Adresse Nationale (BAN)
     * — https://api-adresse.data.gouv.fr — free, no API key, CORS-enabled, so
     * the lookup happens directly from the browser. On selection it emits an
     * `address-picked` event whose detail carries the parsed street / postcode /
     * city, letting the surrounding form fill its fields.
     */
    Alpine.data('addressAutocomplete', (cfg = {}) => ({
        url: cfg.url || '/adresse/recherche',
        query: '',
        results: [],
        open: false,
        loading: false,
        active: -1,
        controller: null,
        async search() {
            const q = this.query.trim();
            if (q.length < 3) {
                this.results = [];
                this.open = false;
                return;
            }
            this.loading = true;
            if (this.controller) this.controller.abort();
            this.controller = new AbortController();
            try {
                // Same-origin proxy to the Base Adresse Nationale (avoids CORS / CSP).
                const sep = this.url.includes('?') ? '&' : '?';
                const r = await fetch(`${this.url}${sep}q=${encodeURIComponent(q)}`, {
                    signal: this.controller.signal,
                    headers: { Accept: 'application/json' },
                });
                if (!r.ok) throw new Error('address');
                this.results = await r.json();
                this.open = true;
                this.active = -1;
            } catch (e) {
                if (e.name !== 'AbortError') {
                    this.results = [];
                    this.open = false;
                }
            } finally {
                this.loading = false;
            }
        },
        move(dir) {
            if (!this.open || !this.results.length) return;
            this.active = (this.active + dir + this.results.length) % this.results.length;
        },
        pick(r) {
            if (!r) return;
            this.open = false;
            this.query = '';
            this.results = [];
            this.active = -1;
            this.$dispatch('address-picked', r);
        },
        enter() {
            if (this.active >= 0) this.pick(this.results[this.active]);
        },
    }));

    // Intervention create/edit form: fetch the selected client's context.
    Alpine.data('interventionForm', (cfg = {}) => ({
        contextUrl: cfg.contextUrl,
        lieu: cfg.lieu || 'atelier',
        maintenance: null,
        hist: { materiels: [], pannes: [], notes: [] },
        init() {
            if (cfg.clientId) this.onClient(cfg.clientId);
        },
        async onClient(id) {
            if (!id) {
                this.maintenance = null;
                this.hist = { materiels: [], pannes: [], notes: [] };
                return;
            }
            const r = await fetch(`${this.contextUrl}/${id}`, { headers: { Accept: 'application/json' } });
            if (!r.ok) return;
            const d = await r.json();
            this.maintenance = d.maintenance;
            this.hist = { materiels: d.materiels, pannes: d.pannes, notes: d.notes };
        },
    }));
});

document.addEventListener('alpine:init', () => {
    /** Touch / mouse signature pad drawing into a canvas; exposes a PNG data URL. */
    window.Alpine.data('signaturePad', () => ({
        drawing: false,
        hasSignature: false,
        value: '',
        ctx: null,
        last: { x: 0, y: 0 },

        init() {
            const canvas = this.$refs.canvas;
            const ratio = window.devicePixelRatio || 1;
            const rect = canvas.getBoundingClientRect();
            canvas.width = rect.width * ratio;
            canvas.height = rect.height * ratio;
            this.ctx = canvas.getContext('2d');
            this.ctx.scale(ratio, ratio);
            this.ctx.lineWidth = 2;
            this.ctx.lineCap = 'round';
            this.ctx.strokeStyle = '#111827';
        },
        pos(e) {
            const r = this.$refs.canvas.getBoundingClientRect();
            const p = e.touches ? e.touches[0] : e;
            return { x: p.clientX - r.left, y: p.clientY - r.top };
        },
        start(e) {
            e.preventDefault();
            this.drawing = true;
            this.last = this.pos(e);
        },
        move(e) {
            if (!this.drawing) return;
            e.preventDefault();
            const p = this.pos(e);
            this.ctx.beginPath();
            this.ctx.moveTo(this.last.x, this.last.y);
            this.ctx.lineTo(p.x, p.y);
            this.ctx.stroke();
            this.last = p;
            this.hasSignature = true;
        },
        end() {
            if (!this.drawing) return;
            this.drawing = false;
            this.value = this.hasSignature ? this.$refs.canvas.toDataURL('image/png') : '';
        },
        clear() {
            this.ctx.clearRect(0, 0, this.$refs.canvas.width, this.$refs.canvas.height);
            this.hasSignature = false;
            this.value = '';
        },
    }));

    /**
     * Restitution & closing: a signature pad plus a billing modal. Prices come
     * from the server (catalogue services net of the customer's discount + parts);
     * the modal lets the technician apply an optional ristourne, set the travel fee
     * (on-site) and record whether the job was invoiced / paid.
     */
    window.Alpine.data('restitution', (cfg = {}) => ({
        // Config (from the server) — already net of the customer's % discounts.
        lieu: cfg.lieu || 'atelier',
        prestaNet: Number(cfg.prestaNet || 0),
        piecesNet: Number(cfg.piecesNet || 0),
        deplMode: cfg.deplMode || 'aucun',
        deplGratuit: !!cfg.deplGratuit,
        deplForfait: Number(cfg.deplForfait || 0),
        deplPrixKm: Number(cfg.deplPrixKm || 0),
        deplDefault: Number(cfg.deplDefault || 0),
        canRistourne: !!cfg.canRistourne,
        // When true, the technician first sets the amounts (ristourne / travel)
        // out of the customer's sight, then reveals the signature screen.
        needsPrepare: !!cfg.needsPrepare,
        signing: false,

        // Billing state
        km: 0,
        deplacement: 0,
        waiveDepl: false,
        remiseType: 'euro',
        remiseValeur: 0,
        payee: false,
        montantPaye: 0,
        paiementMode: 'especes',
        facturee: false,

        // Signature pad state
        drawing: false,
        hasSignature: false,
        value: '',
        ctx: null,
        last: { x: 0, y: 0 },

        init() {
            const canvas = this.$refs.canvas;
            const ratio = window.devicePixelRatio || 1;
            const rect = canvas.getBoundingClientRect();
            canvas.width = rect.width * ratio;
            canvas.height = rect.height * ratio;
            this.ctx = canvas.getContext('2d');
            this.ctx.scale(ratio, ratio);
            this.ctx.lineWidth = 2;
            this.ctx.lineCap = 'round';
            this.ctx.strokeStyle = '#111827';
            this.deplacement = this.deplDefault;
            // Atelier / warranty jobs have no amounts to set: go straight to signing.
            this.signing = !this.needsPrepare;
        },
        get isDomicile() {
            return this.lieu === 'domicile';
        },
        fmt(n) {
            return (Number(n) || 0).toFixed(2).replace('.', ',') + ' €';
        },
        // Parse a user-typed decimal, tolerating the French comma separator
        // (and stray spaces used as thousands separators), e.g. "1 234,50".
        num(v) {
            if (typeof v === 'number') return isNaN(v) ? 0 : v;
            const n = parseFloat(String(v ?? '').replace(/\s/g, '').replace(',', '.'));
            return isNaN(n) ? 0 : n;
        },
        computeDeplacement() {
            if (this.deplGratuit) return 0;
            if (this.deplMode === 'forfait') return Math.round(this.deplForfait * 100) / 100;
            if (this.deplMode === 'km') return Math.round(this.deplPrixKm * this.num(this.km) * 100) / 100;
            return 0;
        },
        onKm() {
            this.deplacement = this.computeDeplacement();
        },
        get effectiveDepl() {
            if (!this.isDomicile || this.waiveDepl) return 0;
            return this.num(this.deplacement);
        },
        // Amount actually recorded as paid (comma-aware, fed to the hidden field).
        get montantPayeNet() {
            return this.num(this.montantPaye);
        },
        get sousTotal() {
            return Math.round((this.prestaNet + this.piecesNet) * 100) / 100;
        },
        get remiseMontant() {
            const v = this.num(this.remiseValeur);
            if (!this.canRistourne || !(v > 0)) return 0;
            const m = this.remiseType === 'pourcent' ? (this.sousTotal * v) / 100 : Math.min(v, this.sousTotal);
            return Math.round(m * 100) / 100;
        },
        get total() {
            return Math.round((this.sousTotal - this.remiseMontant + this.effectiveDepl) * 100) / 100;
        },
        beforeSubmit() {
            this.end(); // capture any in-progress stroke
            if (this.payee && !this.montantPayeNet) this.montantPaye = this.total;
        },
        // --- signature pad ---
        pos(e) {
            const r = this.$refs.canvas.getBoundingClientRect();
            const p = e.touches ? e.touches[0] : e;
            return { x: p.clientX - r.left, y: p.clientY - r.top };
        },
        start(e) {
            e.preventDefault();
            this.drawing = true;
            this.last = this.pos(e);
        },
        move(e) {
            if (!this.drawing) return;
            e.preventDefault();
            const p = this.pos(e);
            this.ctx.beginPath();
            this.ctx.moveTo(this.last.x, this.last.y);
            this.ctx.lineTo(p.x, p.y);
            this.ctx.stroke();
            this.last = p;
            this.hasSignature = true;
        },
        end() {
            if (!this.drawing) return;
            this.drawing = false;
            this.value = this.hasSignature ? this.$refs.canvas.toDataURL('image/png') : '';
        },
        clear() {
            this.ctx.clearRect(0, 0, this.$refs.canvas.width, this.$refs.canvas.height);
            this.hasSignature = false;
            this.value = '';
        },
    }));
});

/** Inserts (replace or append) text from a reference list into a textarea. */
window.fillTextarea = (targetId, value, mode = 'replace') => {
    const t = document.getElementById(targetId);
    if (!t || !value) return;
    t.value = mode === 'append' && t.value.trim() ? `${t.value}\n${value}` : value;
    t.dispatchEvent(new Event('input', { bubbles: true }));
};

document.addEventListener('alpine:init', () => {
    /**
     * Intervention photos uploader. Phone pictures are often several MB, which
     * routinely exceeds the server's upload limits and leaves Livewire's upload
     * spinner stuck. We resize / re-encode each image to a sane size in the
     * browser first, then hand the lightweight files to a hidden Livewire input
     * (which performs the real upload, so progress / error events still fire).
     */
    window.Alpine.data('photoUploader', () => ({
        uploading: false,
        preparing: false,
        progress: 0,
        error: null,
        watchdog: null,

        async pick(event) {
            const files = Array.from(event.target.files || []);
            event.target.value = '';            // allow re-picking the same file
            if (!files.length) return;

            this.error = null;
            this.uploading = true;
            this.preparing = true;
            try {
                const prepared = [];
                for (const file of files) {
                    prepared.push(await this.compress(file));
                }

                const data = new DataTransfer();
                prepared.forEach((f) => data.items.add(f));
                const target = this.$refs.target;
                target.files = data.files;
                // Hands over to wire:model — fires livewire-upload-start/progress/finish.
                target.dispatchEvent(new Event('change', { bubbles: true }));

                // Safety net: never leave the spinner stuck if no event comes back.
                clearTimeout(this.watchdog);
                this.watchdog = setTimeout(() => {
                    if (this.uploading) {
                        this.uploading = false;
                        this.progress = 0;
                        this.error = 'Délai dépassé. Réessayez avec une connexion stable.';
                    }
                }, 120000);
            } catch (e) {
                this.uploading = false;
                this.error = 'Impossible de préparer ces photos. Réessayez.';
            } finally {
                this.preparing = false;
            }
        },

        /** Downscale to a max edge and re-encode as JPEG; falls back to the original. */
        async compress(file, maxEdge = 1800, quality = 0.82) {
            if (!file.type || !file.type.startsWith('image/')) return file;

            let bitmap;
            try {
                bitmap = await createImageBitmap(file, { imageOrientation: 'from-image' });
            } catch (e) {
                return file;                    // unsupported (e.g. HEIC on some browsers)
            }

            const scale = Math.min(1, maxEdge / Math.max(bitmap.width, bitmap.height));
            const width = Math.round(bitmap.width * scale);
            const height = Math.round(bitmap.height * scale);

            const canvas = document.createElement('canvas');
            canvas.width = width;
            canvas.height = height;
            canvas.getContext('2d').drawImage(bitmap, 0, 0, width, height);
            if (bitmap.close) bitmap.close();

            const blob = await new Promise((resolve) => canvas.toBlob(resolve, 'image/jpeg', quality));
            if (!blob) return file;

            const name = file.name.replace(/\.[^.]+$/, '') + '.jpg';
            return new File([blob], name, { type: 'image/jpeg', lastModified: Date.now() });
        },
    }));
});
