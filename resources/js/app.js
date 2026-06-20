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

    // Mobile-friendly searchable single-select (hidden input + native change event).
    Alpine.data('searchableSelect', (cfg = {}) => ({
        options: cfg.options || [],
        value: cfg.selected != null ? String(cfg.selected) : '',
        allowEmpty: cfg.allowEmpty !== false,
        open: false,
        query: '',
        toggle() {
            this.open = !this.open;
            if (this.open) this.$nextTick(() => this.$refs.search && this.$refs.search.focus());
        },
        pick(v) {
            this.value = String(v);
            this.open = false;
            this.query = '';
            this.$refs.input.dispatchEvent(new Event('change', { bubbles: true }));
        },
        label() {
            const o = this.options.find((o) => String(o.value) === this.value);
            return o ? o.label : '';
        },
        filtered() {
            const q = this.query.toLowerCase().trim();
            return q ? this.options.filter((o) => o.label.toLowerCase().includes(q)) : this.options;
        },
    }));

    // Intervention create/edit form: fetch the selected client's context.
    Alpine.data('interventionForm', (cfg = {}) => ({
        contextUrl: cfg.contextUrl,
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

        // Modal + billing state
        open: false,
        km: 0,
        deplacement: 0,
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
        },
        get isDomicile() {
            return this.lieu === 'domicile';
        },
        fmt(n) {
            return (Number(n) || 0).toFixed(2).replace('.', ',') + ' €';
        },
        computeDeplacement() {
            if (this.deplGratuit) return 0;
            if (this.deplMode === 'forfait') return Math.round(this.deplForfait * 100) / 100;
            if (this.deplMode === 'km') return Math.round(this.deplPrixKm * (Number(this.km) || 0) * 100) / 100;
            return 0;
        },
        onKm() {
            this.deplacement = this.computeDeplacement();
        },
        get sousTotal() {
            return Math.round((this.prestaNet + this.piecesNet) * 100) / 100;
        },
        get remiseMontant() {
            if (!this.canRistourne || !(Number(this.remiseValeur) > 0)) return 0;
            const v = Number(this.remiseValeur) || 0;
            const m = this.remiseType === 'pourcent' ? (this.sousTotal * v) / 100 : Math.min(v, this.sousTotal);
            return Math.round(m * 100) / 100;
        },
        get total() {
            const depl = this.isDomicile ? Number(this.deplacement) || 0 : 0;
            return Math.round((this.sousTotal - this.remiseMontant + depl) * 100) / 100;
        },
        openModal() {
            this.end(); // capture any in-progress stroke
            if (!this.montantPaye) this.montantPaye = this.total;
            this.open = true;
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
