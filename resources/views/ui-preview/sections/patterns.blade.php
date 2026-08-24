<div class="ui-preview-stack">
<x-ui.card><div class="ui-preview-section-heading"><div><span class="sc-caption">SEARCH FIRST</span><h2 class="sc-section-title">Global search pattern</h2></div><p>Search phải là tương tác nổi bật nhất.</p></div><div class="ui-search-pattern"><div class="sc-search-control"><x-icons.icon name="search"/><input value="Midnight Echoes" aria-label="Từ khóa preview"><kbd>⌘ K</kbd></div><x-ui.button>Tìm kiếm</x-ui.button></div><div class="mt-4 flex flex-wrap gap-2">@foreach(['Tất cả','Nghệ sĩ','Bài hát','Album','Phiên bản','Tác phẩm'] as $filter)<x-ui.badge :variant="$loop->first ? 'primary' : 'neutral'">{{ $filter }}</x-ui.badge>@endforeach</div></x-ui.card>
<x-ui.card><div class="ui-preview-section-heading"><div><span class="sc-caption">ENTITY ROW</span><h2 class="sc-section-title">Kết quả có định danh rõ</h2></div></div><div class="ui-entity-list">@foreach([['Midnight Echoes','Neon Weather · 2025','recording','Bản thu'],['Neon Weather','Nghệ sĩ · Việt Nam','artist','Nghệ sĩ'],['Afterglow','Album · 12 bản thu','release','Phát hành']] as [$title,$meta,$variant,$label])<article class="ui-entity-row"><div class="ui-entity-art">{{ mb_substr($title,0,1) }}</div><div><h3>{{ $title }}</h3><p>{{ $meta }}</p></div><x-ui.badge :variant="$variant">{{ $label }}</x-ui.badge><a href="{{ \App\Support\Catalog\PublicEntityUrl::to($variant, \Illuminate\Support\Str::slug($title)) }}" aria-label="Mở {{ $title }}">→</a></article>@endforeach</div></x-ui.card>
@php($providerPreviewEntity=[
    'providers'=>[
        ['key'=>'youtube','name'=>'YouTube','capability'=>'outbound_search','capability_label'=>'Tìm kiếm video chính thức','status'=>'available','status_label'=>'Có sẵn','availability_reason'=>'Destination HTTPS đã được xác minh.','url'=>'https://www.youtube.com/results?search_query=Radiohead','market'=>'Toàn cầu','checked_at'=>'2026-08-03','expires_at'=>'2026-08-10','attribution'=>'YouTube','compliance_state'=>'approved','action_label'=>'Mở YouTube'],
        ['key'=>'spotify','name'=>'Spotify','capability'=>'deep_link','capability_label'=>'Liên kết catalog','status'=>'unknown','status_label'=>'Chưa xác định','availability_reason'=>'Chưa có market availability đã xác minh.','url'=>null,'market'=>'Chưa xác định','checked_at'=>null,'expires_at'=>null,'attribution'=>'Spotify','compliance_state'=>'pending','action_label'=>'Mở Spotify'],
    ]
])
<x-provider.chooser :entity="$providerPreviewEntity" />
</div>
@php($phase4PreviewEntity=['type'=>'release','label'=>'Album','slug'=>'ok-computer','title'=>'OK Computer','context'=>'Radiohead','meta'=>'Album · 1997 · 12 bản thu','verified'=>true])
<x-ui.card><div class="ui-preview-section-heading"><div><span class="sc-caption">PHASE 4 PRODUCTION PATTERN</span><h2 class="sc-section-title">Canonical search result component</h2></div><p>Component dùng thật trong `/search`, với entity identity và navigation rõ ràng.</p></div><x-entity.result-row :item="$phase4PreviewEntity" /></x-ui.card>

<x-ui.card>
    <div class="ui-preview-section-heading">
        <div><span class="sc-caption">HOMEPAGE DISCOVERY</span><h2 class="sc-section-title">Entity entry pattern</h2></div>
        <p>Homepage discovery giữ rõ entity type, ví dụ truy vấn và không hiển thị popularity giả.</p>
    </div>
    <div class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <x-home.entity-entry :entry="['type'=>'artist','label'=>'Nghệ sĩ','description'=>'Xác định đúng nghệ sĩ và canonical identity.','example'=>'Radiohead']" />
        <x-home.entity-entry :entry="['type'=>'release','label'=>'Album','description'=>'Khám phá bản phát hành theo nghệ sĩ và năm.','example'=>'OK Computer']" />
        <x-home.entity-entry :entry="['type'=>'work','label'=>'Tác phẩm','description'=>'Theo dõi tác phẩm qua nhiều bản thu.','example'=>'Creep']" />
    </div>
</x-ui.card>

<section class="mt-10" aria-labelledby="preview-search-results-heading">
    <h2 id="preview-search-results-heading" class="sc-section-title">Search results composition</h2>
    <p class="mt-2 text-sm text-[var(--sc-text-secondary)]">Facet counts, scoped summary, canonical result rows and pagination share one governed results composition.</p>
    <div class="mt-5 grid gap-5 lg:grid-cols-[15rem_minmax(0,1fr)]">
        <x-ui.card><h3 class="mb-3 font-bold">Loại thực thể</h3><x-search.facets query="Radiohead" active-type="all" sort="relevance" :counts="['all'=>8,'artist'=>1,'recording'=>4,'release'=>2,'version'=>1,'work'=>0,'collection'=>0]" /></x-ui.card>
        <div><x-search.result-summary query="Radiohead" type="all" :result="['total'=>8,'from'=>1,'to'=>5]" /><section class="mt-5 rounded-[var(--sc-radius-card)] border border-[var(--sc-border)] bg-white px-5"><x-entity.result-row :item="['type'=>'version','label'=>'Phiên bản','slug'=>'true-love-waits-live','title'=>'True Love Waits — Live','context'=>'Radiohead · Live version','meta'=>'Phiên bản · 2001 · Metadata chưa đầy đủ','verified'=>false]" /></section></div>
    </div>
</section>

<section class="mt-10" aria-labelledby="preview-entity-detail-heading">
    <h2 id="preview-entity-detail-heading" class="sc-section-title">Entity detail composition</h2>
    <p class="mt-2 text-sm text-[var(--sc-text-secondary)]">Identity, facts, relationships, identifiers and provenance remain separate from provider availability.</p>
    <div class="mt-5 grid gap-5 lg:grid-cols-[minmax(0,1fr)_20rem]">
        <div class="space-y-5">
            <x-ui.card>
                <div class="flex flex-wrap gap-2"><x-ui.badge variant="release">Album</x-ui.badge><x-ui.badge variant="success">Canonical đã xác minh</x-ui.badge></div>
                <h3 class="mt-3 text-2xl font-bold">OK Computer</h3>
                <p class="mt-2 text-[var(--sc-text-secondary)]">Radiohead</p>
                <div class="mt-5"><x-entity.facts :facts="[['label'=>'Nghệ sĩ','value'=>'Radiohead'],['label'=>'Năm','value'=>'1997'],['label'=>'Loại','value'=>'Album']]" /></div>
            </x-ui.card>
        </div>
        <x-ui.card><x-entity.identifiers :identifiers="[['scheme'=>'MusicBrainz Release Group ID','value'=>'b1392450-e666-3926-a536-22c65f834433']]" /></x-ui.card>
    </div>
</section>
