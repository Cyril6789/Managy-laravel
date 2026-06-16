import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';

window.Alpine = Alpine;

Alpine.plugin(collapse);

/**
 * Global theme store: light / dark with persistence and OS-preference fallback.
 * The initial class is applied inline in <head> (see layout) to avoid FOUC.
 */
Alpine.store('theme', {
    dark: document.documentElement.classList.contains('dark'),

    toggle() {
        this.dark = !this.dark;
        this.apply();
    },

    apply() {
        document.documentElement.classList.toggle('dark', this.dark);
        localStorage.setItem('theme', this.dark ? 'dark' : 'light');
    },
});

/**
 * Mobile-friendly searchable single-select. Holds the value in a hidden input
 * and dispatches a native `change` event so dependent code can react.
 */
Alpine.data('searchableSelect', (cfg = {}) => ({
    options: cfg.options || [],
    value: cfg.selected != null ? String(cfg.selected) : '',
    allowEmpty: cfg.allowEmpty !== false,
    open: false,
    query: '',

    toggle() {
        this.open = !this.open;
        if (this.open) {
            this.$nextTick(() => this.$refs.search && this.$refs.search.focus());
        }
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
        if (!q) return this.options;
        return this.options.filter((o) => o.label.toLowerCase().includes(q));
    },
}));

/**
 * Client picker: live remote search + inline creation, for the intervention form.
 * On selection it dispatches `client-selected` (detail = client id) on the element.
 */
Alpine.data('clientSelect', (cfg = {}) => ({
    value: cfg.selected != null ? String(cfg.selected) : '',
    label: cfg.selectedLabel || '',
    searchUrl: cfg.searchUrl,
    createUrl: cfg.createUrl,
    open: false,
    query: '',
    results: [],
    loading: false,
    creating: false,
    newName: '',

    toggle() {
        this.open = !this.open;
        if (this.open) this.$nextTick(() => this.$refs.search && this.$refs.search.focus());
    },
    async search() {
        const q = this.query.trim();
        if (q.length < 2) { this.results = []; return; }
        this.loading = true;
        try {
            const r = await fetch(`${this.searchUrl}?q=${encodeURIComponent(q)}`, {
                headers: { Accept: 'application/json' },
            });
            this.results = await r.json();
        } finally {
            this.loading = false;
        }
    },
    pick(client) {
        this.value = String(client.id);
        this.label = client.label;
        this.open = false;
        this.emit();
    },
    async create() {
        const name = (this.newName || this.query).trim();
        if (!name) return;
        this.creating = true;
        try {
            const r = await fetch(this.createUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                },
                body: JSON.stringify({ nom: name }),
            });
            if (r.ok) {
                this.pick(await r.json());
                this.newName = '';
            }
        } finally {
            this.creating = false;
        }
    },
    emit() {
        this.$refs.input.dispatchEvent(new Event('change', { bubbles: true }));
        this.$el.dispatchEvent(new CustomEvent('client-selected', { detail: this.value, bubbles: true }));
    },
}));

/**
 * Intervention create/edit form: fetches the selected client's context
 * (maintenance pack balance + history values to prefill text areas).
 */
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

/**
 * Inserts (replace or append) text from a reference list into a textarea.
 */
window.fillTextarea = (targetId, value, mode = 'replace') => {
    const t = document.getElementById(targetId);
    if (!t || !value) return;
    t.value = mode === 'append' && t.value.trim() ? `${t.value}\n${value}` : value;
    t.dispatchEvent(new Event('input', { bubbles: true }));
};

Alpine.start();
