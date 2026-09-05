@props([
    'icon' => null,
    'color' => 'teal',
    'iconBg' => null,
    'iconColor' => null,
    'iconSize' => 20,
    'value' => null,
    'valueId' => null,
    'valueColor' => null,
    'label' => null,
    'labelEn' => null,
    'subtext' => null,
    'subtextEn' => null,
    'trend' => null,
    'trendType' => 'up',
])

@php
    $colorMaps = [
        'teal' => [
            'bg' => 'var(--teal-100)',
            'color' => 'var(--teal-800)',
        ],
        'primary' => [
            'bg' => 'var(--teal-100)',
            'color' => 'var(--teal-800)',
        ],
        'green' => [
            'bg' => 'var(--green-100)',
            'color' => 'var(--green-ink)',
        ],
        'success' => [
            'bg' => 'var(--green-100)',
            'color' => 'var(--green-ink)',
        ],
        'red' => [
            'bg' => 'var(--red-100)',
            'color' => 'var(--red-600)',
        ],
        'danger' => [
            'bg' => 'var(--red-100)',
            'color' => 'var(--red-600)',
        ],
        'destructive' => [
            'bg' => 'var(--red-100)',
            'color' => 'var(--red-600)',
        ],
        'blue' => [
            'bg' => 'var(--blue-100)',
            'color' => 'var(--blue-ink)',
        ],
        'info' => [
            'bg' => 'var(--blue-100)',
            'color' => 'var(--blue-ink)',
        ],
        'gold' => [
            'bg' => 'var(--gold-100)',
            'color' => 'var(--gold-ink)',
        ],
        'warning' => [
            'bg' => 'var(--gold-100)',
            'color' => 'var(--gold-ink)',
        ],
        'amber' => [
            'bg' => 'var(--gold-100)',
            'color' => 'var(--gold-ink)',
        ],
        'grey' => [
            'bg' => 'var(--paper-line)',
            'color' => 'var(--ink-700)',
        ],
        'secondary' => [
            'bg' => 'var(--paper-line)',
            'color' => 'var(--ink-700)',
        ],
        'neutral' => [
            'bg' => 'var(--paper-line)',
            'color' => 'var(--ink-700)',
        ],
    ];

    $resolvedKey = strtolower(trim((string) $color));
    $palette = $colorMaps[$resolvedKey] ?? $colorMaps['teal'];

    $finalIconBg = $iconBg ?? $palette['bg'];
    $finalIconColor = $iconColor ?? $palette['color'];

    $valueColorMap = [
        'red' => 'var(--red-600)',
        'danger' => 'var(--red-600)',
        'green' => 'var(--green-ink)',
        'success' => 'var(--green-ink)',
        'teal' => 'var(--teal-800)',
        'primary' => 'var(--teal-800)',
        'blue' => 'var(--blue-ink)',
        'info' => 'var(--blue-ink)',
        'gold' => 'var(--gold-ink)',
        'warning' => 'var(--gold-ink)',
    ];

    $finalValueColor = $valueColor ? ($valueColorMap[strtolower(trim((string) $valueColor))] ?? $valueColor) : null;
@endphp

<div {{ $attributes->merge(['class' => 'stat-card']) }}>
    @if ($icon)
        <div class="ic" style="background: {{ $finalIconBg }}; color: {{ $finalIconColor }};">
            <x-core::icon :name="$icon" :size="$iconSize" />
        </div>
    @elseif (isset($iconSlot))
        <div class="ic" style="background: {{ $finalIconBg }}; color: {{ $finalIconColor }};">
            {{ $iconSlot }}
        </div>
    @endif

    <div class="stat-card-details">
        @if ($value !== null)
            <div class="val" @if($valueId) id="{{ $valueId }}" @endif @if($finalValueColor) style="color: {{ $finalValueColor }};" @endif>
                {{ $value }}
            </div>
        @elseif (isset($valueSlot))
            <div class="val" @if($valueId) id="{{ $valueId }}" @endif @if($finalValueColor) style="color: {{ $finalValueColor }};" @endif>
                {{ $valueSlot }}
            </div>
        @endif

        @if ($label && $labelEn)
            <div class="lbl bn">{{ $label }}</div>
            <div class="lbl en" style="display:none;">{{ $labelEn }}</div>
        @elseif ($label)
            <div class="lbl bn">{{ $label }}</div>
        @elseif ($labelEn)
            <div class="lbl en">{{ $labelEn }}</div>
        @endif

        @if ($subtext && $subtextEn)
            <div class="stat-card-subtext">
                <span class="bn">{{ $subtext }}</span>
                <span class="en" style="display:none;">{{ $subtextEn }}</span>
            </div>
        @elseif ($subtext)
            <div class="stat-card-subtext">
                <span class="bn">{{ $subtext }}</span>
            </div>
        @elseif ($subtextEn)
            <div class="stat-card-subtext">
                <span class="en">{{ $subtextEn }}</span>
            </div>
        @elseif (isset($subtextSlot))
            <div class="stat-card-subtext">
                {{ $subtextSlot }}
            </div>
        @endif

        {{ $slot }}
    </div>

    @if ($trend)
        <div class="trend {{ $trendType === 'down' ? 'trend-down' : 'trend-up' }}">
            {{ $trend }}
        </div>
    @endif
</div>
