@php
    $displayValue = $value;

    if (is_array($displayValue) && array_key_exists('value', $displayValue)) {
        $displayValue = $displayValue['value'];
    }
@endphp

@if($displayValue === null)
    Không có giá trị
@elseif(is_scalar($displayValue))
    {{ (string) $displayValue }}
@else
    {{ json_encode($displayValue, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}
@endif
