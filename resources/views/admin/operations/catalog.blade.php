@extends('layouts.admin')
@section('content')
<x-admin.page-header :title="$title" :description="$description" />
@include('admin.operations._metrics')
<section class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-3" data-admin-section="catalog">
@foreach($entities as $entity)
<x-ui.card><a href="{{ route('admin.catalog.entities.index', $entity['routeType']) }}" class="block"><div class="flex items-center justify-between"><div><h2 class="font-bold">{{ $entity['label'] }}</h2><p class="mt-1 text-sm text-slate-500">{{ $entity['table'] }}</p></div><strong class="text-2xl">{{ number_format($entity['count']) }}</strong></div></a></x-ui.card>
@endforeach
</section>
@endsection
