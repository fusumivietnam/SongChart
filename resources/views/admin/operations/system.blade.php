@extends('layouts.admin')
@section('content')
<x-admin.page-header :title="$title" :description="$description" />
<section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3" data-admin-section="system-health">@foreach($checks as $check)<x-ui.card><p class="text-sm text-slate-500">{{ $check['label'] }}</p><p class="mt-2 text-xl font-bold">{{ $check['value'] }}</p><p class="mt-2 text-xs uppercase tracking-wide text-slate-400">{{ $check['state'] }}</p></x-ui.card>@endforeach</section>
@endsection
