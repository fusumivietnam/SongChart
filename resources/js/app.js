import Alpine from 'alpinejs';

const recentStateKey = 'songchart.recent-state.v1';
const recentStateLimit = 8;
const recentStateTtlMs = 30 * 24 * 60 * 60 * 1000;

window.Alpine = Alpine;

Alpine.store('recentState', {
    ready: false,
    searches: [],
    entities: [],

    init() {
        this.load();
    },

    safeUrl(value) {
        if (typeof value !== 'string' || value === '' || !value.startsWith('/') || value.startsWith('//')) {
            return null;
        }

        try {
            const url = new URL(value, window.location.origin);

            if (url.origin !== window.location.origin) {
                return null;
            }

            return `${url.pathname}${url.search}${url.hash}`;
        } catch {
            return null;
        }
    },

    isFresh(value) {
        return Number.isFinite(value)
            && value <= Date.now()
            && Date.now() - value <= recentStateTtlMs;
    },

    normalizeSearches(items) {
        if (!Array.isArray(items)) {
            return [];
        }

        const seen = new Set();

        return items
            .filter((item) => item && typeof item === 'object')
            .map((item) => ({
                query: typeof item.query === 'string' ? item.query.trim() : '',
                type: typeof item.type === 'string' ? item.type : 'all',
                sort: typeof item.sort === 'string' ? item.sort : 'relevance',
                url: this.safeUrl(item.url),
                visitedAt: Number(item.visitedAt),
            }))
            .filter((item) => item.query !== '' && item.url !== null && this.isFresh(item.visitedAt))
            .filter((item) => {
                const key = `${item.query.toLocaleLowerCase()}\u0000${item.type}\u0000${item.sort}`;

                if (seen.has(key)) {
                    return false;
                }

                seen.add(key);

                return true;
            })
            .slice(0, recentStateLimit);
    },

    normalizeEntities(items) {
        if (!Array.isArray(items)) {
            return [];
        }

        const seen = new Set();

        return items
            .filter((item) => item && typeof item === 'object')
            .map((item) => ({
                title: typeof item.title === 'string' ? item.title.trim() : '',
                label: typeof item.label === 'string' ? item.label.trim() : '',
                type: typeof item.type === 'string' ? item.type : '',
                url: this.safeUrl(item.url),
                visitedAt: Number(item.visitedAt),
            }))
            .filter((item) => item.title !== '' && item.url !== null && this.isFresh(item.visitedAt))
            .filter((item) => {
                if (seen.has(item.url)) {
                    return false;
                }

                seen.add(item.url);

                return true;
            })
            .slice(0, recentStateLimit);
    },

    load() {
        try {
            const payload = JSON.parse(window.localStorage.getItem(recentStateKey) ?? '{}');
            this.searches = this.normalizeSearches(payload.searches);
            this.entities = this.normalizeEntities(payload.entities);
        } catch {
            this.searches = [];
            this.entities = [];
        }

        this.ready = true;
        this.persist();
    },

    persist() {
        try {
            window.localStorage.setItem(recentStateKey, JSON.stringify({
                version: 1,
                searches: this.searches,
                entities: this.entities,
            }));
        } catch {
            // Browser storage can be unavailable or quota-restricted. Recent state is optional.
        }
    },

    addSearch({ query, type = 'all', sort = 'relevance', url }) {
        const normalizedQuery = typeof query === 'string' ? query.trim().replace(/\s+/g, ' ') : '';
        const normalizedUrl = this.safeUrl(url);

        if (normalizedQuery === '' || normalizedUrl === null) {
            return;
        }

        const normalizedType = typeof type === 'string' && type !== '' ? type : 'all';
        const normalizedSort = typeof sort === 'string' && sort !== '' ? sort : 'relevance';
        const key = `${normalizedQuery.toLocaleLowerCase()}\u0000${normalizedType}\u0000${normalizedSort}`;

        this.searches = [
            {
                query: normalizedQuery,
                type: normalizedType,
                sort: normalizedSort,
                url: normalizedUrl,
                visitedAt: Date.now(),
            },
            ...this.searches.filter((item) => `${item.query.toLocaleLowerCase()}\u0000${item.type}\u0000${item.sort}` !== key),
        ].slice(0, recentStateLimit);

        this.persist();
    },

    addEntity({ title, label = '', type = '', url }) {
        const normalizedTitle = typeof title === 'string' ? title.trim() : '';
        const normalizedUrl = this.safeUrl(url);

        if (normalizedTitle === '' || normalizedUrl === null) {
            return;
        }

        this.entities = [
            {
                title: normalizedTitle,
                label: typeof label === 'string' ? label.trim() : '',
                type: typeof type === 'string' ? type : '',
                url: normalizedUrl,
                visitedAt: Date.now(),
            },
            ...this.entities.filter((item) => item.url !== normalizedUrl),
        ].slice(0, recentStateLimit);

        this.persist();
    },

    clear() {
        this.searches = [];
        this.entities = [];
        this.persist();
    },
});

Alpine.start();

const captureRecentState = () => {
    const store = Alpine.store('recentState');

    document.querySelectorAll('[data-songchart-recent-search]').forEach((element) => {
        store.addSearch({
            query: element.dataset.recentQuery ?? '',
            type: element.dataset.recentType ?? 'all',
            sort: element.dataset.recentSort ?? 'relevance',
            url: element.dataset.recentUrl ?? '',
        });
    });

    document.querySelectorAll('[data-songchart-recent-entity]').forEach((element) => {
        store.addEntity({
            title: element.dataset.recentTitle ?? '',
            label: element.dataset.recentLabel ?? '',
            type: element.dataset.recentType ?? '',
            url: element.dataset.recentUrl ?? '',
        });
    });
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', captureRecentState, { once: true });
} else {
    captureRecentState();
}
