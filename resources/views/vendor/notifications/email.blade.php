<x-mail::message>
{{-- Logo / Header --}}
<div style="text-align:center;margin-bottom:24px;">
    <img src="{{ asset('images/logo-ubt.png') }}" alt="UBT" style="height:60px;">
</div>

{{-- Greeting --}}
@if (! empty($greeting))
# {{ $greeting }}
@else
@if ($level === 'error')
# @lang('Whoops!')
@else
# @lang('Halo!')
@endif
@endif

{{-- Intro Lines --}}
@foreach ($introLines as $line)
{{ $line }}

@endforeach

{{-- Action Button --}}
@isset($actionText)
<?php
    $color = match ($level) {
        'success', 'error' => $level,
        default => 'primary',
    };
?>
<x-mail::button :url="$actionUrl" :color="$color">
{{ $actionText }}
</x-mail::button>
@endisset

{{-- Outro Lines --}}
@foreach ($outroLines as $line)
{{ $line }}

@endforeach

{{-- Salutation --}}
@if (! empty($salutation))
{{ $salutation }}
@else
@lang('Salam hormat,')<br>
{{ config('app.name') }}
@endif

<hr style="margin-top:32px;border-color:#E5E7EB;">

<div style="text-align:center;font-size:12px;color:#9CA3AF;">
    &copy; {{ date('Y') }} LPPM Universitas Borneo Tarakan<br>
    Jalan Amal Lama No. 1, Kota Tarakan, Kalimantan Utara 77115
</div>

{{-- Subcopy --}}
@isset($actionText)
<x-slot:subcopy>
@lang(
    "Jika tombol \":actionText\" tidak berfungsi, salin dan tempel URL di bawah ini\n".
    'ke browser Anda:',
    [
        'actionText' => $actionText,
    ]
) <span class="break-all">[{{ $displayableActionUrl }}]({{ $actionUrl }})</span>
</x-slot:subcopy>
@endisset
</x-mail::message>
