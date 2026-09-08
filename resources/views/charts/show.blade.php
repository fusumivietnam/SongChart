@extends('layouts.frontend')
@php($activeNav = 'discover')
@section('content')
<section class="sc-home-section">
    <div class="sc-container">
        <div class="sc-home-section-heading">
            <div>
                <p class="sc-caption">SONGCHART CHART</p>
                <h1 class="sc-section-title mt-2">{{ $chart['chart_id'] }}</h1>
            </div>
            <p>Metric: {{ $chart['metric'] }} · Snapshot: {{ $chart['snapshot_at'] }} · Calculation: {{ $chart['calculation_version'] }}</p>
        </div>

        <div class="mt-7 overflow-hidden rounded-[var(--sc-radius-panel)] border border-[var(--sc-border)] bg-white">
            <ol class="divide-y divide-[var(--sc-border)]">
                @foreach($chart['rows'] as $row)
                    <li class="grid gap-3 p-5 md:grid-cols-[4rem_minmax(0,1fr)_auto] md:items-center">
                        <div class="text-2xl font-bold">#{{ $row['rank'] }}</div>
                        <div>
                            <a class="font-bold hover:text-[var(--sc-primary)]" href="{{ $row['url'] }}">{{ $row['title'] }}</a>
                            <p class="mt-1 text-sm text-[var(--sc-text-secondary)]">Canonical recording: {{ $row['canonical_recording_id'] }}</p>
                        </div>
                        <div class="text-sm text-[var(--sc-text-secondary)]">Score: {{ $row['score'] }}</div>
                    </li>
                @endforeach
            </ol>
        </div>

        <p class="mt-5 text-sm text-[var(--sc-text-muted)]">Chart rows are published only from persisted, calculation-versioned snapshots with retained provider observation provenance. SongChart does not fabricate popularity or listener data.</p>
    </div>
</section>
@endsection
