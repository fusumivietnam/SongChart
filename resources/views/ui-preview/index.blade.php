@extends('layouts.ui-preview')

@section('content')
<section class="ui-preview-intro">
    <div>
        <x-ui.badge variant="primary">Giai đoạn 3</x-ui.badge>
        <h1 class="sc-page-title mt-4">{{ $activeMeta['label'] }}</h1>
        <p class="mt-3 max-w-3xl text-lg leading-8 text-[var(--sc-text-secondary)]">{{ $activeMeta['description'] }} Đây là inventory kiểm chứng contract, không phải trang sản phẩm và không sử dụng dữ liệu provider trực tiếp.</p>
    </div>
    <div class="ui-preview-contract-note">
        <strong>Nguồn sự thật</strong>
        <span>Design contract → tokens → shared components → preview → feature pages</span>
    </div>
</section>

@include("ui-preview.sections.{$activeSection}")
@endsection
