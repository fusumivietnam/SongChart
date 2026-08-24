@extends('layouts.admin')
@section('content')
<x-admin.page-header :title="$title" :description="$description" />
@include('admin.operations._metrics')
<section class="mt-6" data-admin-section="users"><x-ui.card><div class="overflow-x-auto"><table class="min-w-full text-left text-sm"><thead><tr class="border-b"><th class="p-3">User</th><th class="p-3">Role</th><th class="p-3">Active</th><th class="p-3">Verified</th><th class="p-3">Created</th></tr></thead><tbody class="divide-y">@forelse($users as $user)<tr><td class="p-3 font-semibold">{{ $user->name }}<div class="text-xs text-slate-500">{{ $user->email }}</div></td><td class="p-3">{{ $user->role->label() }}</td><td class="p-3">{{ $user->is_active ? 'Có' : 'Không' }}</td><td class="p-3">{{ $user->email_verified_at ? 'Có' : 'Không' }}</td><td class="p-3">{{ $user->created_at?->format('d/m/Y') }}</td></tr>@empty<tr><td colspan="5" class="p-8 text-center text-slate-500">Chưa có tài khoản.</td></tr>@endforelse</tbody></table></div></x-ui.card></section>
@endsection
