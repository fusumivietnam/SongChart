@extends('layouts.admin')
@php($activeAdminNav = 'system')

@section('content')
<x-admin.page-header title="Nhật ký thao tác đặc quyền" description="Lịch sử các thay đổi quản trị có tác động đến dữ liệu, quyền truy cập và vận hành hệ thống." />

<x-ui.card>
    <div class="p-5">
        <form method="get" class="grid gap-3 md:grid-cols-[1fr_1fr_auto]">
            <label class="text-sm font-semibold">
                Sự kiện
                <input class="mt-1 w-full rounded border px-3 py-2" name="event" value="{{ $filters['event'] ?? '' }}" placeholder="provider.disable">
            </label>
            <label class="text-sm font-semibold">
                Người thực hiện
                <input class="mt-1 w-full rounded border px-3 py-2" name="actor" value="{{ $filters['actor'] ?? '' }}" placeholder="email hoặc tên">
            </label>
            <div class="flex items-end"><x-ui.button type="submit">Lọc</x-ui.button></div>
        </form>
    </div>
</x-ui.card>

<div class="mt-6 space-y-3" data-privileged-audit-list>
    @forelse($activities as $activity)
        <x-ui.card>
            <div class="p-5">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <p class="font-bold">{{ $activity->description }}</p>
                        <p class="mt-1 text-sm text-slate-500">
                            {{ $activity->event ?? 'event' }}
                            · {{ $activity->causer?->email ?? 'system' }}
                            · {{ $activity->created_at?->format('Y-m-d H:i:s') }}
                        </p>
                    </div>
                    @if($activity->subject)
                        <span class="rounded bg-slate-100 px-2 py-1 text-xs font-semibold">
                            {{ class_basename($activity->subject_type) }} · {{ $activity->subject_id }}
                        </span>
                    @endif
                </div>

                @php($properties = $activity->properties ?? collect())
                @if($properties->get('rationale'))
                    <p class="mt-3 rounded border border-slate-200 bg-slate-50 p-3 text-sm">
                        <strong>Lý do:</strong> {{ $properties->get('rationale') }}
                    </p>
                @endif

                @if($properties->get('before') || $properties->get('after'))
                    <details class="mt-3">
                        <summary class="cursor-pointer text-sm font-semibold">Thay đổi</summary>
                        <pre class="mt-2 overflow-auto rounded bg-slate-950 p-3 text-xs text-slate-100">{{ json_encode(['before' => $properties->get('before'), 'after' => $properties->get('after')], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
                    </details>
                @endif
            </div>
        </x-ui.card>
    @empty
        <x-ui.empty-state title="Chưa có thao tác đặc quyền" description="Các thao tác quản trị quan trọng sẽ xuất hiện ở đây." />
    @endforelse
</div>

<div class="mt-6">{{ $activities->links() }}</div>
@endsection
