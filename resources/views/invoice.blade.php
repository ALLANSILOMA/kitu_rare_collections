@php
    $logoPath = public_path('apple-icon-180x180.png'); // Or whichever large icon you prefer
    $logoData = base64_encode(file_get_contents($logoPath));
    $logoSrc = 'data:image/png;base64,' . $logoData;
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KituRare Collections | Invoice #{{ $record->id }}</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; color: #333; line-height: 1.6; margin: 0; padding: 20px; }
        .header { width: 100%; border-bottom: 2px solid #f4f4f4; padding-bottom: 20px; margin-bottom: 30px; }
        .company-name { font-size: 28px; font-weight: bold; color: #4B0082; text-transform: uppercase; letter-spacing: 2px; margin: 0; }
        .company-tagline { font-size: 10px; color: #888; text-transform: uppercase; letter-spacing: 2px; margin: 0; }

        .invoice-details { width: 100%; margin-bottom: 40px; border-collapse: collapse; }
        .details-col { width: 50%; vertical-align: top; }
        .label { font-size: 10px; color: #888; text-transform: uppercase; font-weight: bold; margin-bottom: 5px; }
        .value { font-size: 13px; margin-bottom: 15px; }

        .invoice-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .invoice-table th { text-align: left; font-size: 11px; color: #888; text-transform: uppercase; padding: 10px; border-bottom: 2px solid #333; }
        .invoice-table td { padding: 15px 10px; border-bottom: 1px solid #eee; font-size: 13px; }

        .total-section { margin-top: 30px; text-align: right; }
        .total-box { display: inline-block; background: #f9f9f9; padding: 15px; border-radius: 4px; border: 1px solid #eee; }
        .total-label { font-size: 11px; text-transform: uppercase; color: #888; }
        .total-amount { font-size: 20px; font-weight: bold; color: #333; display: block; }

        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 10px; color: #aaa; border-top: 1px solid #eee; padding-top: 10px; }
    </style>
</head>
<body>

<div class="header">
    <table style="width: 100%;">
        <tr>
            <td style="width: 80px;">
                <img src="{{ $logoSrc }}" style="width: 70px; height: 70px;">
            </td>
            <td>
                <h1 class="company-name">KituRare Collections</h1>
                <p class="company-tagline">Home of chic & rare finds, elevating women one bag at a time!</p>
            </td>
        </tr>
    </table>
</div>

<table class="invoice-details">
    <tr>
        <td class="details-col">
            <div class="label">Billed To</div>
            <div class="value">
                <strong>{{ $record->first_name }} {{ $record->last_name }}</strong><br>
                {{ $record->email }}<br>
                {{ $record->phone }}
            </div>

            <div class="label" style="margin-top: 10px;">Shipping To</div>
            <div class="value">
                {{ $record->shipping_address }}<br>
                {{ $record->city }}<br>
                @if($record->pickup_agent_details)
                    <strong>Agent:</strong> {{ $record->pickup_agent_details }}
                @endif
            </div>
        </td>
        <td class="details-col" style="text-align: right;">
            <div class="label">Invoice Number</div>
            <div class="value">#KRC-{{ $record->id }}</div>

            <div class="label">Order Date</div>
            <div class="value">{{ $record->created_at->format('M d, Y') }}</div>

            <div class="label">Payment Status</div>
            <div class="value" style="text-transform: uppercase; font-weight: bold; color: {{ $record->payment_status === 'completed' ? 'green' : 'orange' }}">
                {{ $record->payment_status }}
            </div>

            <div class="label">Delivery Information - Pickup Location</div>
            <div class="value">{{ $record->shipping_method_name }}</div>
        </td>
    </tr>
</table>

<table class="invoice-table">
    <thead>
    <tr>
        <th style="width: 50%;">Item Description</th>
        <th style="text-align: right;">Price</th>
        <th style="text-align: right;">Shipping Fee</th>
        <th style="text-align: right;">Total</th>
    </tr>
    </thead>
    <tbody>
    <tr>
        <td>
            <div style="font-weight: bold; font-size: 15px;">{{ $record->product->name ?? 'Luxury Handbag' }}</div>
            @if($record->variation_label)
                <div style="font-size: 11px; color: #666; text-transform: uppercase; margin-top: 4px; letter-spacing: 1px;">
                    Option: {{ $record->variation_label }}
                </div>
            @endif
        </td>
        <td style="text-align: right;">
            KES {{ number_format($record->total_amount - $record->shipping_cost, 2) }}
        </td>
        <td style="text-align: right;">
            KES {{ number_format($record->shipping_cost, 2) }}
        </td>
        <td style="text-align: right; font-weight: bold;">
            KES {{ number_format($record->total_amount, 2) }}
        </td>
    </tr>
    </tbody>
</table>

<div class="total-section">
    <div class="total-box">
        <span class="total-label">Grand Total Paid via {{ $record->payment_method }}</span>
        <span class="total-amount">KES {{ number_format($record->total_amount, 2) }}</span>
    </div>
</div>

<div class="footer">
    Thank you for choosing KituRare Collections. For inquiries, call us on 0116 020420
</div>

</body>
</html>
