<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quotation {{ $quotation->quotation_number }}</title>
    <style>
        body {
            font-family: 'Helvetica', sans-serif;
            color: #333;
            line-height: 1.6;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 40px;
            border: 1px solid #eee;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 40px;
        }

        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #4f46e5;
        }

        .info {
            text-align: right;
        }

        .info h1 {
            margin: 0;
            color: #1f2937;
        }

        .details {
            display: flex;
            justify-content: space-between;
            margin-bottom: 40px;
        }

        .details div {
            width: 45%;
        }

        .details h3 {
            border-bottom: 2px solid #f3f4f6;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 40px;
        }

        th {
            background: #f9fafb;
            text-align: left;
            padding: 12px;
            border-bottom: 1px solid #e5e7eb;
        }

        td {
            padding: 12px;
            border-bottom: 1px solid #f3f4f6;
        }

        .totals {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            width: 250px;
            padding: 4px 0;
        }

        .grand-total {
            font-size: 20px;
            font-weight: bold;
            border-top: 2px solid #e5e7eb;
            margin-top: 8px;
            padding-top: 8px;
        }

        .footer {
            margin-top: 60px;
            font-size: 12px;
            color: #9ca3af;
            border-top: 1px solid #eee;
            padding-top: 20px;
        }

        @media print {
            .no-print {
                display: none;
            }

            .container {
                border: none;
                padding: 0;
            }
        }
    </style>
</head>

<body>
    <div class="no-print" style="text-align: center; padding: 20px;">
        <button onclick="window.print()"
            style="padding: 10px 20px; background: #4f46e5; color: white; border: none; border-radius: 6px; cursor: pointer;">Print
            PDF</button>
    </div>

    <div class="container">
        <div class="header">
            <div class="logo">MINI ERP</div>
            <div class="info">
                <h1>QUOTATION</h1>
                <p>{{ $quotation->quotation_number }}</p>
                <p>Date: {{ $quotation->created_at->format('M d, Y') }}</p>
                <p>Valid Until: {{ $quotation->valid_until->format('M d, Y') }}</p>
            </div>
        </div>

        <div class="details">
            <div>
                <h3>To:</h3>
                <p><strong>{{ $quotation->customer->name }}</strong></p>
                <p>{{ $quotation->customer->email }}</p>
                <p>{{ $quotation->customer->phone ?? 'No phone' }}</p>
            </div>
            <div style="text-align: right;">
                <h3>From:</h3>
                <p><strong>Your Company Name</strong></p>
                <p>your@company.com</p>
                <p>+62-123-456-789</p>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Description</th>
                    <th>Qty</th>
                    <th>Price</th>
                    <th style="text-align: right;">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($quotation->items as $item)
                    <tr>
                        <td>{{ $item->description }}</td>
                        <td>{{ number_format($item->quantity, 0) }}</td>
                        <td>Rp {{ number_format($item->unit_price, 2) }}</td>
                        <td style="text-align: right;">Rp {{ number_format($item->subtotal, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="totals">
            <div class="total-row">
                <span>Subtotal:</span>
                <span>Rp {{ number_format($quotation->subtotal, 2) }}</span>
            </div>
            <div class="total-row">
                <span>Tax:</span>
                <span>Rp {{ number_format($quotation->tax_amount, 2) }}</span>
            </div>
            @if($quotation->discount_amount > 0)
                <div class="total-row">
                    <span>Discount:</span>
                    <span>- Rp {{ number_format($quotation->discount_amount, 2) }}</span>
                </div>
            @endif
            <div class="total-row grand-total">
                <span>Total:</span>
                <span>Rp {{ number_format($quotation->total_amount, 2) }}</span>
            </div>
        </div>

        <div class="footer">
            <p><strong>Terms & Conditions:</strong></p>
            <p>{{ $quotation->terms ?? 'Payment due within 30 days. This quotation is valid until the specified date.' }}
            </p>
        </div>
    </div>
</body>

</html>