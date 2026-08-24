@extends('layouts.admin')
@php($activeAdminNav = 'dashboard')
@section('content')
<x-admin.page-header title="Admin shell preview" description="Kiểm tra dark sidebar, responsive drawer, top header và workspace."><x-slot:actions><x-ui.button size="sm">Primary action</x-ui.button></x-slot:actions></x-admin.page-header>
<div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">@foreach(['Sidebar','Top header','Workspace','Responsive drawer'] as $item)<x-ui.card><h2 class="font-bold">{{ $item }}</h2><p class="mt-2 text-sm text-slate-500">Shared admin shell component.</p></x-ui.card>@endforeach</div>
@endsection
