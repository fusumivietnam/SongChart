@extends('layouts.admin')
@section('content')
<x-admin.page-header :title="$title" :description="$description" />

@if(session('status'))
    <div class="mt-4 rounded border p-3 text-sm">{{ session('status') }}</div>
@endif
@if($errors->any())
    <div class="mt-4 rounded border p-3 text-sm"><ul class="list-disc pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif

<div class="mt-6 grid gap-6 xl:grid-cols-3" data-admin-section="identity-conflict-detail">
    <div class="space-y-6 xl:col-span-2">
        <x-ui.card>
            <h2 class="text-lg font-semibold">Conflict</h2>
            <dl class="mt-4 grid gap-3 md:grid-cols-2 text-sm">
                <div><dt class="text-slate-500">Review</dt><dd class="font-mono">{{ $review->id }}</dd></div>
                <div><dt class="text-slate-500">Status</dt><dd>{{ $review->status->value }}</dd></div>
                <div><dt class="text-slate-500">Provider</dt><dd>{{ $review->providerEntity?->provider?->name ?? 'Unknown' }}</dd></div>
                <div><dt class="text-slate-500">External ID</dt><dd class="font-mono">{{ $review->providerEntity?->external_id }}</dd></div>
                <div><dt class="text-slate-500">Entity type</dt><dd>{{ $entityType->label() }}</dd></div>
                <div><dt class="text-slate-500">Opened</dt><dd>{{ $review->opened_at?->format('d/m/Y H:i:s') }}</dd></div>
            </dl>
        </x-ui.card>

        <x-ui.card>
            <h2 class="text-lg font-semibold">Canonical candidates</h2>
            <div class="mt-4 space-y-4">
                @foreach($candidates as $candidate)
                    <article class="rounded border p-4" data-candidate-id="{{ $candidate['id'] }}">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div>
                                <h3 class="font-semibold">{{ $candidate['label'] }}</h3>
                                <div class="mt-1 font-mono text-xs text-slate-500">{{ $candidate['id'] }}</div>
                                <div class="mt-2 text-sm">Match: {{ $candidate['match_status'] instanceof \BackedEnum ? $candidate['match_status']->value : ($candidate['match_status'] ?? 'unknown') }} · {{ $candidate['match_method'] ?? 'unknown' }} · confidence {{ $candidate['confidence'] ?? 'n/a' }}</div>
                            </div>
                            @if($candidate['slug'])<a class="text-sm underline" href="{{ route('admin.catalog.entities.show', [$entityType->value, $candidate['id']]) }}">Canonical detail</a>@endif
                        </div>

                        @can('manage-identity-conflicts')
                            @if($review->status->value !== 'resolved')
                                <div class="mt-4 grid gap-3 md:grid-cols-2">
                                    @foreach(['approve_match' => 'Approve match', 'reject_candidate' => 'Reject candidate'] as $action => $label)
                                        <form method="post" action="{{ route('admin.identity-conflicts.decide', $review) }}" class="rounded border p-3">
                                            @csrf
                                            <input type="hidden" name="action" value="{{ $action }}" />
                                            <input type="hidden" name="selected_entity_id" value="{{ $candidate['id'] }}" />
                                            <label class="text-sm">Rationale<input required minlength="10" maxlength="2000" name="rationale" class="mt-1 w-full rounded border-slate-300 bg-transparent" /></label>
                                            <button class="mt-3 rounded border px-3 py-2 text-sm font-semibold">{{ $label }}</button>
                                        </form>
                                    @endforeach
                                </div>
                            @endif
                        @endcan
                    </article>
                @endforeach
            </div>
        </x-ui.card>

        <x-ui.card>
            <h2 class="text-lg font-semibold">Evidence</h2>
            <pre class="mt-4 overflow-x-auto whitespace-pre-wrap text-xs">{{ json_encode($review->evidence ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
        </x-ui.card>
    </div>

    <div class="space-y-6">
        @can('manage-identity-conflicts')
        <x-ui.card>
            <h2 class="text-lg font-semibold">Review decision</h2>
            @if($review->status->value === 'resolved')
                <form method="post" action="{{ route('admin.identity-conflicts.decide', $review) }}" class="mt-4">
                    @csrf
                    <input type="hidden" name="action" value="reopen" />
                    <label class="text-sm">Rationale<input required minlength="10" maxlength="2000" name="rationale" class="mt-1 w-full rounded border-slate-300 bg-transparent" /></label>
                    <button class="mt-3 rounded border px-3 py-2 text-sm font-semibold">Reopen review</button>
                </form>
            @else
                @foreach(['keep_separate' => 'Keep separate', 'defer_merge' => 'Defer decision'] as $action => $label)
                    <form method="post" action="{{ route('admin.identity-conflicts.decide', $review) }}" class="mt-4 rounded border p-3">
                        @csrf
                        <input type="hidden" name="action" value="{{ $action }}" />
                        <label class="text-sm">Rationale<input required minlength="10" maxlength="2000" name="rationale" class="mt-1 w-full rounded border-slate-300 bg-transparent" /></label>
                        <button class="mt-3 rounded border px-3 py-2 text-sm font-semibold">{{ $label }}</button>
                    </form>
                @endforeach
            @endif
        </x-ui.card>
        @endcan

        <x-ui.card>
            <h2 class="text-lg font-semibold">Decision history</h2>
            <div class="mt-4 space-y-4">
                @forelse($review->decisions->sortByDesc('created_at') as $decision)
                    <article class="border-l pl-3 text-sm">
                        <div class="font-semibold">{{ $decision->action->value }}</div>
                        <div class="text-slate-500">{{ $decision->actor?->name ?? 'System' }} · {{ $decision->created_at?->format('d/m/Y H:i:s') }}</div>
                        @if($decision->selected_entity_id)<div class="mt-1 font-mono text-xs">{{ $decision->selected_entity_id }}</div>@endif
                        @if($decision->rationale)<p class="mt-2">{{ $decision->rationale }}</p>@endif
                    </article>
                @empty
                    <p class="text-sm text-slate-500">No decisions recorded.</p>
                @endforelse
            </div>
        </x-ui.card>
    </div>
</div>
@endsection
