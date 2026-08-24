@props(['title', 'description' => null])
<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title">{{ $title }}</h1>
        @if($description)<p class="admin-page-description">{{ $description }}</p>@endif
    </div>
    @if(isset($actions))<div class="flex flex-wrap items-center gap-2">{{ $actions }}</div>@endif
</div>
