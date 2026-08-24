<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

final class DesignLabController extends Controller
{
    /**
     * @var array<string, array{name: string, direction: string, strength: string}>
     */
    private const CONCEPTS = [
        '01-editorial-library' => [
            'name' => 'Editorial Library',
            'direction' => 'Tạp chí biên tập kết hợp thư viện âm nhạc',
            'strength' => 'Cân bằng discovery, nội dung và CTA nghe',
        ],
        '02-search-first' => [
            'name' => 'Search First',
            'direction' => 'Tìm kiếm là trung tâm của toàn bộ trải nghiệm',
            'strength' => 'Nhanh, rõ ý định và phù hợp SEO landing',
        ],
        '03-artwork-gallery' => [
            'name' => 'Artwork Gallery',
            'direction' => 'Artwork lớn và trải nghiệm khám phá trực quan',
            'strength' => 'Nhận diện mạnh, giàu cảm xúc, dễ duyệt',
        ],
        '04-knowledge-graph' => [
            'name' => 'Knowledge Graph',
            'direction' => 'Quan hệ giữa artist, release, recording và work',
            'strength' => 'Khác biệt rõ với website streaming thông thường',
        ],
        '05-calm-minimal' => [
            'name' => 'Calm Minimal',
            'direction' => 'Tối giản, typography và khoảng trắng',
            'strength' => 'Tải nhanh, dễ đọc, ít nhiễu',
        ],
        '06-music-magazine' => [
            'name' => 'Music Magazine',
            'direction' => 'Trang chủ dạng tạp chí âm nhạc hiện đại',
            'strength' => 'Phù hợp editorial, SEO và nội dung dài',
        ],
        '07-cinematic-dark' => [
            'name' => 'Cinematic Dark',
            'direction' => 'Dark-first, hero giàu chiều sâu và điểm nhấn',
            'strength' => 'Cảm giác premium và tập trung artwork',
        ],
        '08-utility-discovery' => [
            'name' => 'Utility Discovery',
            'direction' => 'Mật độ cao, tác vụ nhanh, ít trang trí',
            'strength' => 'Hiệu quả cho người dùng quay lại thường xuyên',
        ],
        '09-community-shelves' => [
            'name' => 'Community Shelves',
            'direction' => 'Bộ sưu tập, curator và ngữ cảnh cộng đồng',
            'strength' => 'Tăng retention mà không biến thành mạng xã hội',
        ],
        '10-provider-first' => [
            'name' => 'Provider First',
            'direction' => 'Ưu tiên trả lời “nghe ở đâu” thật nhanh',
            'strength' => 'CTR outbound rõ ràng và conversion trực tiếp',
        ],
    ];

    public function index(): View
    {
        return view('design-lab.index', ['concepts' => self::CONCEPTS]);
    }

    public function show(string $concept): View
    {
        abort_unless(array_key_exists($concept, self::CONCEPTS), 404);

        return view("design-lab.concepts.{$concept}", [
            'conceptKey' => $concept,
            'concept' => self::CONCEPTS[$concept],
            'sample' => $this->sampleData(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function sampleData(): array
    {
        return [
            'featuredArtist' => [
                'name' => 'Luna Vale',
                'type' => 'Nghệ sĩ',
                'summary' => 'Dream pop giàu lớp âm thanh, kết hợp synth mềm và ca từ về ký ức đô thị.',
                'meta' => 'Hoạt động từ 2018 · Bangkok / London',
            ],
            'artists' => [
                ['name' => 'Luna Vale', 'meta' => 'Dream pop', 'initials' => 'LV'],
                ['name' => 'North Arcade', 'meta' => 'Indie rock', 'initials' => 'NA'],
                ['name' => 'Mira Sol', 'meta' => 'Alternative R&B', 'initials' => 'MS'],
                ['name' => 'Glass Harbour', 'meta' => 'Ambient electronic', 'initials' => 'GH'],
                ['name' => 'The Paper Moons', 'meta' => 'Indie folk', 'initials' => 'PM'],
            ],
            'releases' => [
                ['title' => 'Afterglow District', 'artist' => 'Luna Vale', 'year' => '2026', 'type' => 'Album'],
                ['title' => 'Static Gardens', 'artist' => 'North Arcade', 'year' => '2026', 'type' => 'EP'],
                ['title' => 'Blue Hour Letters', 'artist' => 'Mira Sol', 'year' => '2025', 'type' => 'Album'],
                ['title' => 'Tidal Memory', 'artist' => 'Glass Harbour', 'year' => '2025', 'type' => 'Single'],
                ['title' => 'Rooms We Left', 'artist' => 'The Paper Moons', 'year' => '2024', 'type' => 'Album'],
                ['title' => 'Soft Signals', 'artist' => 'Luna Vale', 'year' => '2024', 'type' => 'EP'],
            ],
            'recordings' => [
                ['title' => 'Neon Weather', 'artist' => 'Luna Vale', 'duration' => '3:48'],
                ['title' => 'Satellite Hearts', 'artist' => 'North Arcade', 'duration' => '4:11'],
                ['title' => 'Half Awake', 'artist' => 'Mira Sol', 'duration' => '3:29'],
                ['title' => 'Low Tide Signal', 'artist' => 'Glass Harbour', 'duration' => '5:02'],
                ['title' => 'Paper Constellations', 'artist' => 'The Paper Moons', 'duration' => '4:06'],
            ],
            'providers' => [
                ['name' => 'YouTube', 'label' => 'Xem video chính thức'],
                ['name' => 'Apple Music', 'label' => 'Mở album'],
                ['name' => 'SoundCloud', 'label' => 'Nghe bản đăng chính thức'],
            ],
            'collections' => [
                ['title' => 'Đêm thành phố, ánh đèn mờ', 'count' => 24, 'curator' => 'SongChart Editorial'],
                ['title' => 'Indie Đông Nam Á đáng chú ý', 'count' => 38, 'curator' => 'Mina Tran'],
                ['title' => 'Âm thanh cho ngày mưa', 'count' => 19, 'curator' => 'Tuan Le'],
            ],
        ];
    }
}
