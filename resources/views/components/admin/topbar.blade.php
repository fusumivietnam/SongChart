<header class="admin-topbar">
    <div class="flex min-w-0 items-center gap-3">
        <button type="button" class="admin-topbar-icon lg:hidden" @click="sidebarOpen=true" aria-label="Mở menu quản trị"><x-icons.icon name="menu" /></button>
        <form class="hidden w-[min(32rem,42vw)] md:block" role="search">
            <label class="sc-sr-only" for="admin-search">Tìm kiếm trong quản trị</label>
            <div class="sc-search-control bg-[var(--admin-bg-subtle)]">
                <x-icons.icon name="search" class="h-5 w-5" />
                <input id="admin-search" placeholder="Tìm thực thể, tác vụ, provider…" disabled aria-disabled="true" />
                <kbd>/</kbd>
            </div>
        </form>
    </div>
    <div class="flex items-center gap-2 sm:gap-3">
        <span class="hidden rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-600 xl:inline-flex" data-dashboard-range="7-days">7 ngày gần đây</span>
        <button class="admin-topbar-icon" type="button" aria-label="Thông báo" disabled aria-disabled="true"><x-icons.icon name="bell" /></button>
        <a href="{{ route('home') }}" class="admin-topbar-icon" aria-label="Mở trang công khai"><x-icons.icon name="external" /></a>
        <div class="admin-user-menu">
            <span class="sc-avatar">{{ auth()->check() ? str(auth()->user()->name)->substr(0, 1)->upper() : 'A' }}</span>
            <span class="hidden text-left sm:block"><strong>{{ auth()->user()->name ?? 'Admin' }}</strong><small>{{ auth()->user()->role ?? 'super_admin' }}</small></span>
        </div>
    </div>
</header>
