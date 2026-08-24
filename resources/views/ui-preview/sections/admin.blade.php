<div class="ui-preview-stack admin-preview-surface">
    <div class="admin-page-header">
        <div><span class="sc-caption">OPERATIONS FIRST</span><h1 class="admin-page-title mt-2">Tổng quan vận hành</h1><p class="admin-page-description">Pattern preview dùng fixture minh họa; dashboard production chỉ hiển thị dữ liệu runtime thật.</p></div>
    </div>
    <div class="ui-admin-metrics">
        @foreach([['Người dùng','24','info'],['Provider cần chú ý','2','warning'],['Đồng bộ lỗi','1','danger'],['Tác vụ đang chạy','3','primary']] as [$label,$value,$variant])
            <x-ui.card><div data-dashboard-metric="preview-{{ $loop->index }}"><x-ui.badge :variant="$variant">{{ $label }}</x-ui.badge><strong>{{ $value }}</strong><span>fixture UI Preview</span></div></x-ui.card>
        @endforeach
    </div>
    <div class="grid gap-6 xl:grid-cols-[1.4fr_1fr]">
        <x-ui.card>
            <div class="ui-preview-section-heading"><div><span class="sc-caption">WORK QUEUE</span><h2 class="sc-section-title">Công việc cần xử lý</h2></div></div>
            <div class="ui-admin-task-list">
                @foreach([['Provider cần rà soát','2','warning'],['Lượt đồng bộ thất bại','1','danger'],['Tác vụ extension chưa hoàn tất','3','primary']] as [$title,$count,$variant])
                    <div data-work-item="preview-{{ $loop->index }}"><x-ui.badge :variant="$variant">{{ $count }}</x-ui.badge><strong>{{ $title }}</strong><span>Pattern minh họa</span></div>
                @endforeach
            </div>
        </x-ui.card>
        <x-ui.card>
            <div class="ui-preview-section-heading"><div><span class="sc-caption">SYSTEM HEALTH</span><h2 class="sc-section-title">Cảnh báo hệ thống</h2></div></div>
            <div class="mt-5 space-y-3"><x-ui.alert variant="danger">Có lượt đồng bộ provider thất bại.</x-ui.alert><x-ui.alert variant="warning">Một provider chưa hoàn tất rà soát policy.</x-ui.alert><x-ui.alert variant="success">Không có cảnh báo extension nghiêm trọng.</x-ui.alert></div>
        </x-ui.card>
    </div>
</div>
