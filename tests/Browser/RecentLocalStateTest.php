<?php

declare(strict_types=1);

it('keeps recent discovery state browser-local bounded and safe', function (): void {
    visit('/search')
        ->assertSee('Bắt đầu bằng một từ khóa')
        ->assertScript(
            "(() => { const store = window.Alpine.store('recentState'); store.clear(); for (let i = 0; i < 12; i += 1) { store.addSearch({ query: `Query ${i}`, type: 'all', sort: 'relevance', url: `/search?q=Query+${i}` }); } return store.searches.length === 8 && store.searches[0].query === 'Query 11' && store.searches[7].query === 'Query 4'; })()",
            true,
        )
        ->assertScript(
            "(() => { const store = window.Alpine.store('recentState'); store.clear(); store.addEntity({ title: 'Safe entity', label: 'Bản thu', type: 'recording', url: 'https://example.com/not-local' }); store.addEntity({ title: 'Unsafe entity', label: 'Bản thu', type: 'recording', url: 'javascript:alert(1)' }); store.addEntity({ title: 'Local entity', label: 'Bản thu', type: 'recording', url: '/recordings/local-entity' }); return store.entities.length === 1 && store.entities[0].url === '/recordings/local-entity'; })()",
            true,
        )
        ->assertNoSmoke();
});

it('expires stale recent discovery state without server identity', function (): void {
    visit('/search')
        ->assertScript(
            "(() => { const key = 'songchart.recent-state.v1'; const stale = Date.now() - (31 * 24 * 60 * 60 * 1000); localStorage.setItem(key, JSON.stringify({ version: 1, searches: [{ query: 'Old query', type: 'all', sort: 'relevance', url: '/search?q=Old+query', visitedAt: stale }], entities: [{ title: 'Old entity', label: 'Bản thu', type: 'recording', url: '/recordings/old-entity', visitedAt: stale }] })); const store = window.Alpine.store('recentState'); store.load(); return store.searches.length === 0 && store.entities.length === 0 && JSON.parse(localStorage.getItem(key)).searches.length === 0; })()",
            true,
        )
        ->assertNoSmoke();
});

it('renders local recent state through text bindings and allows explicit clearing', function (): void {
    visit('/search')
        ->assertScript(
            "(() => { const store = window.Alpine.store('recentState'); store.clear(); store.addSearch({ query: '<img src=x onerror=alert(1)>', type: 'all', sort: 'relevance', url: '/search?q=safe' }); store.addEntity({ title: '<script>alert(1)</script>', label: 'Bản thu', type: 'recording', url: '/recordings/safe' }); return store.searches.length === 1 && store.entities.length === 1 && document.querySelector('section[aria-labelledby=\"recent-discovery-title\"]') !== null; })()",
            true,
        )
        ->assertScript(
            "(() => { const store = window.Alpine.store('recentState'); store.clear(); const payload = JSON.parse(localStorage.getItem('songchart.recent-state.v1')); return store.searches.length === 0 && store.entities.length === 0 && payload.searches.length === 0 && payload.entities.length === 0; })()",
            true,
        )
        ->assertNoSmoke();
});
