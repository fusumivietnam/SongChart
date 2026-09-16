@extends('layouts.frontend')
@php($activeNav = 'saved')
@section('content')
<x-account.shell active="saved" title="Đã lưu" description="Các thực thể canonical bạn đã lưu để quay lại sau.">
    @if(session('status') === 'saved-entity-added')
        <x-ui.alert variant="success" class="mb-5">Đã lưu vào tài khoản.</x-ui.alert>
    @elseif(session('status') === 'saved-entity-removed')
        <x-ui.alert variant="success" class="mb-5">Đã bỏ khỏi danh sách đã lưu.</x-ui.alert>
    @endif

    <div class="space-y-3">
        @forelse($savedEntities as $saved)
            <x-ui.card class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0">
                    <p class="sc-caption">{{ $saved['type']->label() }}</p>
                    @if($saved['available'] && $saved['url'])
                        <a class="mt-1 block break-words text-lg font-bold hover:underline" href="{{ $saved['url'] }}">{{ $saved['title'] }}</a>
                    @else
                        <p class="mt-1 break-words text-lg font-bold text-[var(--sc-text-secondary)]">{{ $saved['title'] }}</p>
                        <p class="mt-1 text-sm text-[var(--sc-text-muted)]">Bản ghi đã lưu không còn resolve được tới public canonical entity.</p>
                    @endif
                </div>
                <form method="POST" action="{{ route('account.saved.destroy', ['type' => $saved['type']->value, 'id' => $saved['entity_id']]) }}">
                    @csrf
                    @method('DELETE')
                    <x-ui.button type="submit" variant="secondary">Bỏ lưu</x-ui.button>
                </form>
            </x-ui.card>
        @empty
            <x-ui.card>
                <p class="font-semibold">Chưa có nội dung đã lưu.</p>
                <p class="mt-2 text-sm text-[var(--sc-text-secondary)]">Mở một trang canonical để lưu lại cho lần xem sau.</p>
            </x-ui.card>
        @endforelse
    </div>
</x-account.shell>
@endsection
