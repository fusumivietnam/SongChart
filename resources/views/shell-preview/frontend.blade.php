@extends('layouts.frontend')
@php($activeNav = 'discover')
@section('content')
<section class="sc-container py-12">
    <p class="font-semibold text-brand-600">Phase 2 shell preview</p>
    <h1 class="sc-page-title mt-3">Frontend shell</h1>
    <p class="mt-4 max-w-2xl text-secondary">Kiểm tra header, global search, account controls, responsive container và mobile bottom navigation.</p>
    <div class="mt-8 grid gap-4 md:grid-cols-3">@foreach(['Header desktop','Search control','Mobile navigation'] as $item)<x-ui.card><h2 class="font-bold">{{ $item }}</h2><p class="mt-2 text-sm text-slate-500">Shared shell component approved for production composition.</p></x-ui.card>@endforeach</div>
</section>
@endsection
