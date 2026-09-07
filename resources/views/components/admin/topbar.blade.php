<header class="admin-topbar">
    <div class="flex min-w-0 items-center gap-3">
        <button type="button" class="admin-topbar-icon lg:hidden" @click="sidebarOpen=true" :aria-expanded="sidebarOpen.toString()" aria-controls="admin-sidebar" aria-label="Mở menu quản trị"><x-icons.icon name="menu" /></button>
        <p class="hidden text-sm font-medium text-slate-600 md:block">Quản trị nội dung và dữ liệu SongChart</p>
    </div>
    <div class="flex items-center gap-2 sm:gap-3">
        <a href="{{ route('home') }}" class="admin-topbar-icon" aria-label="Mở trang công khai"><x-icons.icon name="external" /></a>
        <div class="admin-user-menu">
            <span class="sc-avatar" aria-hidden="true">{{ auth()->check() ? str(auth()->user()->name)->substr(0, 1)->upper() : 'A' }}</span>
            <span class="hidden text-left sm:block"><strong>{{ auth()->user()->name ?? 'Admin' }}</strong><small>{{ auth()->user()->role ?? 'super_admin' }}</small></span>
        </div>
    </div>
</header>
