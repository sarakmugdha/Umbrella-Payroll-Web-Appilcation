<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Invoice #{{ $invoiceDetails[0]->invoice_number }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            margin: 30px;
        }

        .header {
            width: 100%;
            text-align: center;
            margin-bottom: 30px;
        }

        /* .header svg {
            float: left;
        } */
        h2 {
            margin: 0;
            color: rgb(33, 13, 122);
        }

        .date {
            float: right;
            font-size: 14px;
        }

        .clearfix {
            clear: both;
        }

        .from-to {
            width: 100%;
            margin-top: 30px;
        }

        .from-to td {
            vertical-align: top;
            padding: 5px;
            width: 50%;
        }

        .info {
            margin-top: 20px;
        }

        table.invoice-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 30px;
        }

        table.invoice-table th,
        table.invoice-table td {
            border: 1px solid #cccccc;
            padding: 8px;
            text-align: center;
        }

        table.invoice-table th {
            background-color: #1b4c8b;
            color: white;
        }

        .total {
            text-align: right;
            margin-top: 20px;
            font-size: 16px;
        }

        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 12px;
        }

        .imgdiv {
            background-image: url("file://{{ public_path('/paypal.png') }}");
            background-size: cover;
            background-position: center;
            width: 60px;
            height: 60px;
        }
    </style>
</head>

<body>

    <!-- <div class="header" style="display: flex; justify-content: space-between; align-items: center; width: 100%; margin-bottom: 30px;">
    
    <div style="width: 60px; height: 60px; background-image: url('{{ $logo }}'); background-size: cover; background-position: center;">
    </div>

    <h2 style="margin: 0; text-align: center; flex: 1;">Invoice #{{ $invoiceDetails[0]->invoice_number ?? '' }}</h2>

    <div style="font-size: 14px; text-align: right;">
        {{ $date }}
    </div>
</div> -->
<table width="100%" style="margin-bottom: 30px;">
    <tr>
        <td style="width: 60px;">
            <img src="{{ $logo }}" width="60" height="60" style="object-fit: cover;">
        </td>
        <td style="padding-left:160px">
            <h2 style="margin: 0;">Invoice #{{ $invoiceDetails[0]->invoice_number ?? '' }}</h2>
        </td>
        <td style="text-align: right; font-size: 14px;">
            {{ $date }}
        </td>
    </tr>
</table>





    <div class="clearfix"></div>

    <table class="from-to">
        <tr>
            <td>
                <strong>To:</strong><br>
                {{ $customer->customer_name }}<br>
                {{ $customer->email }}<br>
                {{ $customer->address }}<br>
                {{ $customer->state }}, {{ $customer->pincode }}
            </td>
            <td>
                <strong>From:</strong><br>
                {{ $company->company_name }}<br>
                {{ $company->email }}<br>
                {{ $company->address }}<br>
                {{ $company->city }}, {{ $company->state }}, {{ $company->pincode }}
            </td>
        </tr>
    </table>

    <div class="info">
        <p><strong>Assignment Number:</strong> {{ $invoiceDetails[0]->assignment_id }}</p>
        <p><strong>Contractor Name:</strong> {{ $people->name }}</p>
    </div>

    <table class="invoice-table">
        <thead>
            <tr>
                <th>Description</th>
                <th>Hours Worked</th>
                <th>Hourly Pay</th>
                <th>Total Pay</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($invoiceDetails as $item)
                <tr>
                    <td>{{ $item->description }}</td>
                    <td>{{ $item->hours_worked }}</td>
                    <td>{{ number_format($item->hourly_pay, 2) }}</td>
                    <td>{{ number_format($item->total_pay, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <p class="total"><strong>Total: </strong>{{ $total }}</p>

    <div class="footer">
        © 2025 Paycaddy PVT.LTD. All rights reserved
    </div>

</body>

</html>