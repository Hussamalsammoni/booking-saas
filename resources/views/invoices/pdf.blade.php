<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        body {
            font-family: 'amiri', sans-serif;
            direction: rtl;
            text-align: right;
            padding: 10px;
            color: #333;
        }

        .header {
            border-bottom: 2px solid #4f46e5;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .shop-name {
            font-size: 22px;
            font-weight: bold;
            color: #4f46e5;
        }

        .invoice-title {
            font-size: 16px;
            color: #555;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        th {
            background-color: #f3f4f6;
            text-align: right;
            padding: 8px;
            font-size: 13px;
        }

        td {
            padding: 8px;
            border-bottom: 1px solid #eee;
            font-size: 13px;
        }

        .total-row {
            font-weight: bold;
            background-color: #f9fafb;
        }

        .status-paid {
            color: #065f46;
            background-color: #d1fae5;
            padding: 3px 8px;
        }

        .status-unpaid {
            color: #92400e;
            background-color: #fef3c7;
            padding: 3px 8px;
        }

        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 12px;
            color: #777;
        }
    </style>
</head>
<body>

    <div class="header">
        <div class="shop-name">{{ $shopName }}</div>
        <div class="invoice-title">فاتورة رقم: {{ $invoice->invoice_number }}</div>
    </div>

    <table>
        <tr>
            <td style="width: 50%;"><strong>الزبون:</strong> {{ $invoice->booking->customer_name }}</td>
            <td><strong>رقم الهاتف:</strong> {{ $invoice->booking->customer_phone }}</td>
        </tr>
        <tr>
            <td><strong>التاريخ:</strong> {{ $invoice->created_at->format('Y-m-d') }}</td>
            <td>
                <strong>الحالة:</strong>
                <span class="{{ $invoice->status === 'paid' ? 'status-paid' : 'status-unpaid' }}">
                    {{ $invoice->status === 'paid' ? 'مدفوعة' : 'غير مدفوعة' }}
                </span>
            </td>
        </tr>
    </table>

    <table style="margin-top: 20px;">
        <thead>
            <tr>
                <th>الخدمة</th>
                <th>الموظف</th>
                <th>السعر</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $invoice->booking->service->name }}</td>
                <td>{{ $invoice->booking->staff->user->name }}</td>
                <td>${{ number_format($invoice->amount, 2) }}</td>
            </tr>
            <tr class="total-row">
                <td colspan="2">الإجمالي</td>
                <td>${{ number_format($invoice->amount, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        شكراً لتعاملكم معنا
    </div>

</body>
</html>