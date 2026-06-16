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

/** Inserts (replace or append) text from a reference list into a textarea. */
window.fillTextarea = (targetId, value, mode = 'replace') => {
    const t = document.getElementById(targetId);
    if (!t || !value) return;
    t.value = mode === 'append' && t.value.trim() ? `${t.value}\n${value}` : value;
    t.dispatchEvent(new Event('input', { bubbles: true }));
};
