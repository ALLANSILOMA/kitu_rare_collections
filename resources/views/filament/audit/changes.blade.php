@php
    $state      = $getState() ?? [];
    $old        = $state['old'] ?? [];
    $new        = $state['new'] ?? [];
    $properties = $state['properties'] ?? [];
    $keys       = array_values(array_unique(array_merge(array_keys($old), array_keys($new))));

    $fmt = function ($value) {
        if (is_null($value)) {
            return '—';
        }
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        return is_scalar($value)
            ? (string) $value
            : json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    };

    $cell = 'padding:6px 10px;border:1px solid rgba(128,128,128,.3);vertical-align:top;white-space:pre-wrap;word-break:break-word;';
@endphp

<div style="overflow-x:auto;">
    @if (empty($keys))
        <p style="opacity:.7;font-size:.875rem;">No field changes were recorded for this event.</p>
    @else
        <table style="width:100%;border-collapse:collapse;font-size:.875rem;">
            <thead>
            <tr>
                <th style="{{ $cell }}text-align:left;width:22%;">Field</th>
                <th style="{{ $cell }}text-align:left;">Before</th>
                <th style="{{ $cell }}text-align:left;">After</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($keys as $key)
                <tr>
                    <td style="{{ $cell }}font-weight:600;">{{ $key }}</td>
                    <td style="{{ $cell }}background:rgba(239,68,68,.12);">{{ $fmt($old[$key] ?? null) }}</td>
                    <td style="{{ $cell }}background:rgba(34,197,94,.12);">{{ $fmt($new[$key] ?? null) }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    @endif

    @if (! empty($properties))
        <p style="margin-top:16px;margin-bottom:6px;font-weight:600;font-size:.875rem;">Details</p>
        <table style="width:100%;border-collapse:collapse;font-size:.875rem;">
            <tbody>
            @foreach ($properties as $key => $value)
                <tr>
                    <td style="{{ $cell }}font-weight:600;width:22%;">{{ $key }}</td>
                    <td style="{{ $cell }}">{{ $fmt($value) }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    @endif
</div>
