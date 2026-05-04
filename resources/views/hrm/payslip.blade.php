<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payslip - {{ $payroll->employee->user->name }}</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 12px;
            color: #333;
            line-height: 1.5;
        }
        .container {
            width: 100%;
            margin: 0 auto;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #4a90e2;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            color: #4a90e2;
            font-size: 24px;
            text-transform: uppercase;
        }
        .header p {
            margin: 5px 0 0;
            color: #777;
        }
        .info-section {
            margin-bottom: 20px;
            width: 100%;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
        }
        .info-table td {
            padding: 5px;
            vertical-align: top;
        }
        .info-label {
            font-weight: bold;
            width: 150px;
        }
        .details-section {
            margin-bottom: 30px;
        }
        .details-table {
            width: 100%;
            border-collapse: collapse;
        }
        .details-table th {
            background-color: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
            padding: 10px;
            text-align: left;
            font-weight: bold;
        }
        .details-table td {
            padding: 10px;
            border-bottom: 1px solid #dee2e6;
        }
        .amount {
            text-align: right;
        }
        .total-row {
            font-weight: bold;
            background-color: #f8f9fa;
        }
        .footer {
            margin-top: 50px;
            text-align: center;
            color: #777;
            font-size: 10px;
        }
        .net-salary-box {
            margin-top: 20px;
            padding: 15px;
            background-color: #4a90e2;
            color: white;
            text-align: right;
            border-radius: 4px;
        }
        .net-salary-box h2 {
            margin: 0;
            font-size: 18px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{{ config('app.name') }}</h1>
            <p>PAYSLIP - {{ $payroll->payrollPeriod->name }}</p>
        </div>

        <div class="info-section">
            <table class="info-table">
                <tr>
                    <td class="info-label">Employee Name</td>
                    <td>: {{ $payroll->employee->user->name }}</td>
                    <td class="info-label">Employee Code</td>
                    <td>: {{ $payroll->employee->emp_code }}</td>
                </tr>
                <tr>
                    <td class="info-label">Department</td>
                    <td>: {{ $payroll->employee->department->name ?? '-' }}</td>
                    <td class="info-label">Designation</td>
                    <td>: {{ $payroll->employee->designation->name ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="info-label">Payment Date</td>
                    <td>: {{ $payroll->payment_date ? $payroll->payment_date->format('d M Y') : '-' }}</td>
                    <td class="info-label">Status</td>
                    <td>: {{ strtoupper($payroll->status) }}</td>
                </tr>
            </table>
        </div>

        <div class="details-section">
            <table class="details-table">
                <thead>
                    <tr>
                        <th>Earnings</th>
                        <th class="amount">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($payroll->items->where('type', 'earning') as $item)
                    <tr>
                        <td>{{ $item->name }}</td>
                        <td class="amount">{{ number_format($item->amount, 2) }}</td>
                    </tr>
                    @endforeach
                    <tr class="total-row">
                        <td>Total Earnings</td>
                        <td class="amount">{{ number_format($payroll->total_earnings, 2) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="details-section">
            <table class="details-table">
                <thead>
                    <tr>
                        <th>Deductions</th>
                        <th class="amount">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($payroll->items->where('type', 'deduction') as $item)
                    <tr>
                        <td>{{ $item->name }}</td>
                        <td class="amount">{{ number_format($item->amount, 2) }}</td>
                    </tr>
                    @endforeach
                    <tr class="total-row">
                        <td>Total Deductions</td>
                        <td class="amount">{{ number_format($payroll->total_deductions, 2) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="net-salary-box">
            <p>NET SALARY</p>
            <h2>{{ config('app.currency', 'IDR') }} {{ number_format($payroll->net_salary, 2) }}</h2>
        </div>

        <div class="footer">
            <p>This is a computer-generated document and does not require a signature.</p>
            <p>Generated on {{ now()->format('d M Y H:i:s') }}</p>
        </div>
    </div>
</body>
</html>
