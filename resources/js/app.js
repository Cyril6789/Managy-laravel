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
});

/** Inserts (replace or append) text from a reference list into a textarea. */
window.fillTextarea = (targetId, value, mode = 'replace') => {
    const t = document.getElementById(targetId);
    if (!t || !value) return;
    t.value = mode === 'append' && t.value.trim() ? `${t.value}\n${value}` : value;
    t.dispatchEvent(new Event('input', { bubbles: true }));
};
