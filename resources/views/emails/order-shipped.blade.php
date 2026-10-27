<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: 'Inter', Arial, sans-serif; background: #FBFBFB; color: #1A1A1A; margin: 0; padding: 0; }
        .wrap { max-width: 560px; margin: 40px auto; background: #fff; border-radius: 16px; border: 1px solid #e5e7eb; overflow: hidden; }
        .header { background: #1A1A1A; padding: 32px 40px; text-align: center; }
        .header span { color: #fff; font-size: 13px; font-weight: 700; letter-spacing: 0.3em; text-transform: uppercase; }
        .body { padding: 40px; }
        .badge { display: inline-block; background: #000; color: #fff; padding: 8px 20px; border-radius: 999px; font-size: 11px; font-weight: 700; letter-spacing: 0.2em; text-transform: uppercase; }
        .status-row { background: #f9fafb; border-radius: 12px; padding: 20px 24px; margin: 24px 0; display: flex; justify-content: space-between; }
        .label { font-size: 10px; text-transform: uppercase; letter-spacing: 0.15em; color: #9ca3af; font-weight: 700; margin-bottom: 4px; }
        .value { font-size: 14px; font-weight: 700; color: #111; }
        .tracker { display: flex; align-items: center; justify-content: center; gap: 0; margin: 32px 0; }
        .step { text-align: center; flex: 1; }
        .dot { width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 6px; font-size: 12px; }
        .dot-done { background: #22c55e; color: white; }
        .dot-active { background: #1A1A1A; color: white; }
        .dot-pending { background: #e5e7eb; color: #9ca3af; }
        .line { flex: 1; height: 1px; background: #e5e7eb; margin-bottom: 22px; }
        .step-label { font-size: 9px; text-transform: uppercase; letter-spacing: 0.1em; font-weight: 700; }
        .step-label-done { color: #374151; }
        .step-label-active { color: #1A1A1A; }
        .step-label-pending { color: #d1d5db; }
        .cta { text-align: center; margin: 32px 0 8px; }
        .btn { background: #000; color: #fff; padding: 14px 32px; border-radius: 12px; font-size: 10px; font-weight: 700; letter-spacing: 0.2em; text-transform: uppercase; text-decoration: none; display: inline-block; }
        .footer { text-align: center; padding: 24px 40px; font-size: 11px; color: #9ca3af; border-top: 1px solid #f3f4f6; }
    </style>
</head>
<body>
<div class="wrap">

    <div class="header">
        <span>KituRare Collections</span>
    </div>

    <div class="body">
        <p style="font-size:13px; color:#6b7280; margin-bottom:8px;">Hi <strong style="color:#111;">{{ $order->first_name }}</strong>,</p>
        <h1 style="font-size:22px; font-weight:700; margin:0 0 4px;">Your order is on its way! 🎉</h1>
        <p style="font-size:13px; color:#6b7280; margin-top:8px;">
            Great news — your KituRare order has been dispatched and is heading to you now.
        </p>

        <div class="status-row">
            <div>
                <div class="label">Order Reference</div>
                <div class="value">{{ $order->order_reference }}</div>
            </div>
            <div>
                <div class="label">Total Paid</div>
                <div class="value">KES {{ number_format($order->total_amount) }}</div>
            </div>
            <div>
                <div class="label">Shipping Via</div>
                <div class="value">{{ $order->shipping_method_name }}</div>
            </div>
        </div>

        {{-- Status tracker --}}
        <div class="tracker">
            <div class="step">
                <div class="dot dot-done">✓</div>
                <div class="step-label step-label-done">Order Placed</div>
            </div>
            <div class="line"></div>
            <div class="step">
                <div class="dot dot-done">✓</div>
                <div class="step-label step-label-done">Payment Verified</div>
            </div>
            <div class="line"></div>
            <div class="step">
                <div class="dot dot-active">→</div>
                <div class="step-label step-label-active">Shipped</div>
            </div>
            <div class="line"></div>
            <div class="step">
                <div class="dot dot-pending">○</div>
                <div class="step-label step-label-pending">Delivered</div>
            </div>
        </div>

        <p style="font-size:13px; color:#6b7280; text-align:center;">
            Delivery to <strong style="color:#111;">{{ $order->city }}</strong>
            via <strong style="color:#111;">{{ $order->shipping_method_name }}</strong>.
            We'll be in touch if there are any updates.
        </p>

        <div class="cta">
            <a href="https://wa.me/+254116020420?text=Hi,%20my%20order%20ref%20is%20{{ urlencode($order->order_reference) }}" class="btn">
                Track via WhatsApp
            </a>
        </div>
    </div>

    <div class="footer">
        Questions? Reply to this email or WhatsApp us at +254 116 020 420<br>
        © {{ date('Y') }} KituRare Collections. All rights reserved.
    </div>

</div>
</body>
</html>
