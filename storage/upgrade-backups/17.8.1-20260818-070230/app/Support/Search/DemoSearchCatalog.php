<?php

declare(strict_types=1);

namespace App\Support\Search;

use App\Contracts\Search\SearchCatalog;
use App\Support\Catalog\PublicEntityUrl;
use Illuminate\Support\Str;

final class DemoSearchCatalog implements SearchCatalog
{
    /** @var list<array<string,mixed>> */
    private array $items = [
        ['type' => 'artist', 'label' => 'Nghệ sĩ', 'slug' => 'radiohead', 'title' => 'Radiohead', 'context' => 'Ban nhạc alternative rock · Anh', 'meta' => 'Thành lập 1985 · Canonical identity', 'year' => 1985, 'verified' => true, 'description' => 'Ban nhạc rock Anh được biết đến với cách tiếp cận thử nghiệm và nhiều giai đoạn âm thanh khác nhau.'],
        ['type' => 'release', 'label' => 'Album', 'slug' => 'ok-computer', 'title' => 'OK Computer', 'context' => 'Radiohead', 'meta' => 'Album · 1997 · 12 bản thu', 'year' => 1997, 'verified' => true, 'description' => 'Album phòng thu thứ ba của Radiohead.'],
        ['type' => 'recording', 'label' => 'Bản thu', 'slug' => 'paranoid-android', 'title' => 'Paranoid Android', 'context' => 'Radiohead · OK Computer', 'meta' => '6:23 · Phát hành 1997', 'year' => 1997, 'verified' => true, 'description' => 'Một bản thu cụ thể nằm trong album OK Computer.'],
        ['type' => 'work', 'label' => 'Tác phẩm', 'slug' => 'creep', 'title' => 'Creep', 'context' => 'Sáng tác: Radiohead, Albert Hammond, Mike Hazlewood', 'meta' => 'Tác phẩm · 1992 · Có nhiều bản thu', 'year' => 1992, 'verified' => false, 'description' => 'Tác phẩm âm nhạc trừu tượng, tách biệt với từng bản thu hoặc bản phát hành.'],
        ['type' => 'version', 'label' => 'Phiên bản', 'slug' => 'creep-acoustic', 'title' => 'Creep — Acoustic', 'context' => 'Radiohead · Acoustic version', 'meta' => 'Phiên bản · 1993', 'year' => 1993, 'verified' => false, 'description' => 'Một phiên bản acoustic được phân biệt với bản thu gốc.'],
        ['type' => 'collection', 'label' => 'Bộ sưu tập', 'slug' => 'alternative-essentials', 'title' => 'Alternative Essentials', 'context' => 'SongChart Editorial', 'meta' => 'Bộ sưu tập · 24 mục · Cập nhật 2026', 'year' => 2026, 'verified' => true, 'description' => 'Bộ sưu tập biên tập gồm nhiều loại thực thể.'],
        ['type' => 'artist', 'label' => 'Nghệ sĩ', 'slug' => 'nirvana', 'title' => 'Nirvana', 'context' => 'Ban nhạc grunge · Hoa Kỳ', 'meta' => 'Thành lập 1987 · Canonical identity', 'year' => 1987, 'verified' => true, 'description' => 'Ban nhạc rock Mỹ có ảnh hưởng lớn đến grunge.'],

        ['type' => 'recording', 'label' => 'Bản thu', 'slug' => 'creep-radiohead', 'title' => 'Creep', 'context' => 'Radiohead · Pablo Honey', 'meta' => '3:58 · Phát hành 1992', 'year' => 1992, 'verified' => true, 'description' => 'Bản thu phòng thu nổi tiếng của Radiohead.'],
        ['type' => 'release', 'label' => 'Album', 'slug' => 'in-rainbows', 'title' => 'In Rainbows', 'context' => 'Radiohead', 'meta' => 'Album · 2007 · 10 bản thu', 'year' => 2007, 'verified' => true, 'description' => 'Album phòng thu thứ bảy của Radiohead.'],
        ['type' => 'recording', 'label' => 'Bản thu', 'slug' => 'karma-police', 'title' => 'Karma Police', 'context' => 'Radiohead · OK Computer', 'meta' => '4:21 · Phát hành 1997', 'year' => 1997, 'verified' => true, 'description' => 'Bản thu thuộc OK Computer.'],
        ['type' => 'release', 'label' => 'Album', 'slug' => 'kid-a', 'title' => 'Kid A', 'context' => 'Radiohead', 'meta' => 'Album · 2000 · 10 bản thu', 'year' => 2000, 'verified' => true, 'description' => 'Album phòng thu thứ tư của Radiohead.'],
        ['type' => 'recording', 'label' => 'Bản thu', 'slug' => 'no-surprises', 'title' => 'No Surprises', 'context' => 'Radiohead · OK Computer', 'meta' => '3:49 · Phát hành 1997', 'year' => 1997, 'verified' => true, 'description' => 'Bản thu thuộc OK Computer.'],
        ['type' => 'version', 'label' => 'Phiên bản', 'slug' => 'true-love-waits-live', 'title' => 'True Love Waits — Live', 'context' => 'Radiohead · Live version', 'meta' => 'Phiên bản · 2001 · Metadata chưa đầy đủ', 'year' => 2001, 'verified' => false, 'description' => 'Một phiên bản biểu diễn trực tiếp cần tiếp tục xác minh.'],
        ['type' => 'release', 'label' => 'Album', 'slug' => 'nevermind', 'title' => 'Nevermind', 'context' => 'Nirvana', 'meta' => 'Album · 1991 · 12 bản thu', 'year' => 1991, 'verified' => true, 'description' => 'Album phòng thu thứ hai của Nirvana.'],
    ];

    public function search(string $query, string $type = 'all', string $sort = 'relevance', int $page = 1): array
    {
        $needle = Str::lower(trim($query));
        $matched = array_values(array_filter($this->items, function (array $item) use ($needle): bool {
            $haystack = Str::lower($item['title'].' '.$item['context'].' '.$item['meta']);

            return $needle === '' || Str::contains($haystack, $needle);
        }));

        $counts = ['all' => count($matched)];
        foreach (['artist', 'recording', 'release', 'version', 'work', 'collection'] as $entityType) {
            $counts[$entityType] = count(array_filter($matched, fn (array $item): bool => $item['type'] === $entityType));
        }

        $items = $type === 'all'
            ? $matched
            : array_values(array_filter($matched, fn (array $item): bool => $item['type'] === $type));

        usort($items, match ($sort) {
            'title' => fn (array $a, array $b): int => strcasecmp($a['title'], $b['title']),
            'year_desc' => fn (array $a, array $b): int => $b['year'] <=> $a['year'],
            default => fn (array $a, array $b): int => $this->score($b, $needle) <=> $this->score($a, $needle),
        });

        $perPage = 5;
        $total = count($items);
        $lastPage = max(1, (int) ceil($total / $perPage));
        $offset = ($page - 1) * $perPage;
        $pageItems = array_map(fn (array $item): array => array_merge($item, ['url' => PublicEntityUrl::to((string) $item['type'], (string) $item['slug'])]), array_slice($items, $offset, $perPage));

        return [
            'items' => $pageItems,
            'total' => $total,
            'total_all' => count($matched),
            'counts' => $counts,
            'related' => ['Radiohead', 'Nirvana', 'OK Computer'],
            'page' => $page,
            'per_page' => $perPage,
            'last_page' => $lastPage,
            'from' => $total === 0 ? 0 : $offset + 1,
            'to' => min($offset + $perPage, $total),
        ];
    }

    public function homepage(): array
    {
        $featuredKeys = [
            ['artist', 'radiohead'],
            ['release', 'ok-computer'],
            ['recording', 'paranoid-android'],
        ];

        $featured = [];
        foreach ($featuredKeys as [$type, $slug]) {
            $item = $this->find($type, $slug);
            if ($item !== null) {
                unset($item['providers'], $item['sources']);
                $featured[] = $item;
            }
        }

        return [
            'examples' => ['Radiohead', 'OK Computer', 'Creep'],
            'entity_entries' => [
                ['type' => 'artist', 'label' => 'Nghệ sĩ', 'description' => 'Xác định đúng nghệ sĩ và các định danh canonical.', 'example' => 'Radiohead'],
                ['type' => 'recording', 'label' => 'Bản thu', 'description' => 'Tìm một bản thu cụ thể, tách khỏi tác phẩm và phiên bản.', 'example' => 'Paranoid Android'],
                ['type' => 'release', 'label' => 'Album', 'description' => 'Khám phá album hoặc bản phát hành theo nghệ sĩ và năm.', 'example' => 'OK Computer'],
                ['type' => 'version', 'label' => 'Phiên bản', 'description' => 'Phân biệt acoustic, live, remix và các biến thể khác.', 'example' => 'Creep Acoustic'],
                ['type' => 'work', 'label' => 'Tác phẩm', 'description' => 'Theo dõi tác phẩm sáng tác qua nhiều bản thu.', 'example' => 'Creep'],
                ['type' => 'collection', 'label' => 'Bộ sưu tập', 'description' => 'Đi theo tuyển chọn biên tập có ngữ cảnh rõ ràng.', 'example' => 'Alternative Essentials'],
            ],
            'featured' => $featured,
            'editorial' => [
                'type' => 'collection',
                'label' => 'Bộ sưu tập',
                'slug' => 'alternative-essentials',
                'title' => 'Alternative Essentials',
                'description' => 'Một điểm bắt đầu có chủ đích để đi từ nghệ sĩ sang album, bản thu và tác phẩm liên quan.',
                'items' => ['Radiohead', 'Nirvana', 'OK Computer'],
                'provenance' => 'SongChart Editorial · fixture nội bộ',
                'url' => PublicEntityUrl::to('collection', 'alternative-essentials'),
            ],
        ];
    }

    public function find(string $type, string $slug): ?array
    {
        foreach ($this->items as $item) {
            if ($item['type'] !== $type || $item['slug'] !== $slug) {
                continue;
            }

            return array_merge($item, ['url' => PublicEntityUrl::to((string) $item['type'], (string) $item['slug'])], $this->detailPayload($item));
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $item
     * @return array<string, mixed>
     */
    private function detailPayload(array $item): array
    {
        $details = match ($item['type']) {
            'artist' => [
                'eyebrow' => 'Canonical artist identity',
                'facts' => [['label' => 'Loại', 'value' => 'Nhóm nhạc'], ['label' => 'Quốc gia', 'value' => 'Vương quốc Anh'], ['label' => 'Hoạt động', 'value' => '1985–nay']],
                'identifiers' => [['scheme' => 'MusicBrainz Artist ID', 'value' => 'a74b1b7f-71a5-4011-9441-d0b5e4122711']],
                'relationships' => $this->related([['release', 'ok-computer'], ['release', 'in-rainbows'], ['recording', 'paranoid-android']]),
                'relationship_title' => 'Bản phát hành và bản thu tiêu biểu',
            ],
            'release' => [
                'eyebrow' => 'Canonical release identity',
                'facts' => [['label' => 'Nghệ sĩ', 'value' => $item['context']], ['label' => 'Năm', 'value' => (string) $item['year']], ['label' => 'Loại', 'value' => 'Album']],
                'identifiers' => [['scheme' => 'MusicBrainz Release Group ID', 'value' => 'b1392450-e666-3926-a536-22c65f834433']],
                'relationships' => $this->related([['artist', 'radiohead'], ['recording', 'paranoid-android'], ['recording', 'karma-police'], ['recording', 'no-surprises']]),
                'relationship_title' => 'Nghệ sĩ và danh sách bản thu',
            ],
            'recording' => [
                'eyebrow' => 'Canonical recording identity',
                'facts' => [['label' => 'Nghệ sĩ', 'value' => 'Radiohead'], ['label' => 'Thời lượng', 'value' => str_contains($item['meta'], '6:23') ? '6:23' : 'Chưa xác minh'], ['label' => 'Năm', 'value' => (string) $item['year']]],
                'identifiers' => [['scheme' => 'ISRC', 'value' => 'GBAYE9701368']],
                'relationships' => $this->related([['artist', 'radiohead'], ['release', 'ok-computer'], ['work', 'creep']]),
                'relationship_title' => 'Nghệ sĩ, phát hành và tác phẩm liên quan',
            ],
            'work' => [
                'eyebrow' => 'Abstract musical work',
                'facts' => [['label' => 'Loại', 'value' => 'Tác phẩm âm nhạc'], ['label' => 'Năm', 'value' => (string) $item['year']], ['label' => 'Xác minh', 'value' => 'Đang bổ sung quan hệ tác giả']],
                'identifiers' => [],
                'relationships' => $this->related([['recording', 'creep-radiohead'], ['version', 'creep-acoustic']]),
                'relationship_title' => 'Bản thu và phiên bản của tác phẩm',
            ],
            'version' => [
                'eyebrow' => 'Distinct performance version',
                'facts' => [['label' => 'Dạng', 'value' => 'Acoustic / live variation'], ['label' => 'Năm', 'value' => (string) $item['year']], ['label' => 'Xác minh', 'value' => 'Metadata chưa đầy đủ']],
                'identifiers' => [],
                'relationships' => $this->related([['work', 'creep'], ['recording', 'creep-radiohead'], ['artist', 'radiohead']]),
                'relationship_title' => 'Tác phẩm, bản thu gốc và nghệ sĩ',
            ],
            'collection' => [
                'eyebrow' => 'SongChart editorial collection',
                'facts' => [['label' => 'Chủ biên', 'value' => 'SongChart Editorial'], ['label' => 'Phạm vi', 'value' => 'Nhiều loại thực thể'], ['label' => 'Cập nhật', 'value' => '2026']],
                'identifiers' => [],
                'relationships' => $this->related([['artist', 'radiohead'], ['artist', 'nirvana'], ['release', 'ok-computer']]),
                'relationship_title' => 'Các mục trong bộ sưu tập',
            ],
            default => ['eyebrow' => 'Canonical entity', 'facts' => [], 'identifiers' => [], 'relationships' => [], 'relationship_title' => 'Liên quan'],
        };

        return array_merge($details, [
            'passport' => [
                'coverage' => 100,
                'confidence' => $item['verified'] ? 95 : 70,
                'assertion_count' => 3,
                'identifier_count' => count($details['identifiers']),
                'open_conflict_count' => 0,
                'approved_destination_count' => 0,
                'fields' => [],
            ],
            'providers' => [
                [
                    'key' => 'youtube', 'name' => 'YouTube', 'capability' => 'outbound_search', 'capability_label' => 'Tìm kiếm video chính thức',
                    'status' => 'available', 'status_label' => 'Có sẵn', 'availability_reason' => 'Có destination HTTPS thuộc miền YouTube được cho phép.',
                    'url' => 'https://www.youtube.com/results?search_query='.rawurlencode($item['title']),
                    'market' => 'Toàn cầu; kết quả có thể thay đổi theo khu vực', 'checked_at' => '2026-08-03', 'expires_at' => '2026-08-10',
                    'attribution' => 'YouTube', 'compliance_state' => 'approved', 'action_label' => 'Mở YouTube',
                ],
                [
                    'key' => 'spotify', 'name' => 'Spotify', 'capability' => 'deep_link', 'capability_label' => 'Liên kết catalog',
                    'status' => 'unknown', 'status_label' => 'Chưa xác định', 'availability_reason' => 'Chưa có provider entity và market availability đã xác minh.',
                    'url' => null, 'market' => 'Chưa xác định', 'checked_at' => null, 'expires_at' => null,
                    'attribution' => 'Spotify', 'compliance_state' => 'pending', 'action_label' => 'Mở Spotify',
                ],
                [
                    'key' => 'soundcloud', 'name' => 'SoundCloud', 'capability' => 'deep_link', 'capability_label' => 'Liên kết track',
                    'status' => 'stale', 'status_label' => 'Cần kiểm tra lại', 'availability_reason' => 'Dữ liệu destination đã quá hạn và không được phép mở.',
                    'url' => 'https://soundcloud.com/example/stale-link', 'market' => 'Không xác định', 'checked_at' => '2026-07-01', 'expires_at' => '2026-07-08',
                    'attribution' => 'SoundCloud', 'compliance_state' => 'approved', 'action_label' => 'Mở SoundCloud',
                ],
            ],
            'sources' => [
                ['name' => 'MusicBrainz canonical metadata', 'status' => 'Fixture nội bộ', 'checked_at' => '2026-08-03'],
                ['name' => 'SongChart relationship mapping', 'status' => 'Deterministic fixture', 'checked_at' => '2026-08-03'],
            ],
        ]);
    }

    /**
     * @param  list<array{0: string, 1: string}>  $keys
     * @return list<array<string, mixed>>
     */
    private function related(array $keys): array
    {
        $related = [];
        foreach ($keys as [$type, $slug]) {
            foreach ($this->items as $candidate) {
                if ($candidate['type'] === $type && $candidate['slug'] === $slug) {
                    $related[] = $candidate;
                    break;
                }
            }
        }

        return $related;
    }

    /** @param array<string, mixed> $item */
    private function score(array $item, string $needle): int
    {
        if ($needle === '') {
            return 0;
        }
        $title = Str::lower($item['title']);
        if ($title === $needle) {
            return 100;
        }
        if (Str::startsWith($title, $needle)) {
            return 70;
        }

        return Str::contains($title, $needle) ? 50 : 10;
    }
}
