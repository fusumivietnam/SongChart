@extends('layouts.admin')

@section('content')
<x-admin.page-header :title="$title" :description="$description" />
@include('admin.operations._metrics')

@if (session('status'))
    <div class="mt-6 rounded border p-4 text-sm">{{ session('status') }}</div>
@endif

@if ($errors->any())
    <div class="mt-6 rounded border p-4 text-sm">
        <ul class="list-disc pl-5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<section class="mt-6" data-admin-section="users">
    <x-ui.card>
        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead>
                    <tr class="border-b">
                        <th class="p-3">User</th>
                        <th class="p-3">Role</th>
                        <th class="p-3">Active</th>
                        <th class="p-3">Verified</th>
                        <th class="p-3">Created</th>
                        <th class="p-3">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($users as $user)
                        <tr>
                            <td class="p-3 font-semibold">
                                {{ $user->name }}
                                <div class="text-xs text-slate-500">{{ $user->email }}</div>
                            </td>
                            <td class="p-3">{{ $user->role->label() }}</td>
                            <td class="p-3">{{ $user->is_active ? 'Có' : 'Không' }}</td>
                            <td class="p-3">{{ $user->email_verified_at ? 'Có' : 'Không' }}</td>
                            <td class="p-3">{{ $user->created_at?->format('d/m/Y') }}</td>
                            <td class="p-3">
                                <div class="space-y-3">
                                    @if ($canManageRoles)
                                        <form method="POST" action="{{ route('admin.users.role.update', $user) }}" class="space-y-2">
                                            @csrf
                                            @method('PATCH')
                                            <select name="role" class="w-full rounded border px-2 py-1">
                                                @foreach ($roles as $role)
                                                    <option value="{{ $role->value }}" @selected($user->role === $role)>
                                                        {{ $role->label() }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <input type="text" name="rationale" required maxlength="500" placeholder="Lý do thay đổi vai trò" class="w-full rounded border px-2 py-1">
                                            <button type="submit" class="text-sm underline">Cập nhật vai trò</button>
                                        </form>
                                    @endif

                                    @if ($canManageActivation)
                                        <form method="POST" action="{{ route('admin.users.active.update', $user) }}" class="space-y-2">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="active" value="{{ $user->is_active ? '0' : '1' }}">
                                            <input type="text" name="rationale" required maxlength="500" placeholder="Lý do thay đổi trạng thái" class="w-full rounded border px-2 py-1">
                                            <button type="submit" class="text-sm underline">{{ $user->is_active ? 'Vô hiệu hóa' : 'Kích hoạt' }}</button>
                                        </form>
                                    @endif

                                    @if (! $canManageRoles && ! $canManageActivation)
                                        <span class="text-xs text-slate-500">Không có quyền thao tác.</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="p-8 text-center text-slate-500">Chưa có tài khoản.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-ui.card>
</section>
@endsection
