<!DOCTYPE html>
<html lang="en-US">
<head>
    <meta charset="utf-8">
</head>
<body>

<div>
    Hello {!! $user_name !!}, <br /><br />
    Here are the details from the automated meters readings: <br  /><br />

    @if ($readings && $readings->count())
        <table style="width: 100%">
            <tr>
                <th style="border-bottom: 1px solid black">Meter Number</th>
                <th style="border-bottom: 1px solid black">Scheme</th>
                <th style="border-bottom: 1px solid black">Customer</th>
                <th style="border-bottom: 1px solid black">Customer Email</th>
                <th style="border-bottom: 1px solid black">Reading</th>
                <th style="border-bottom: 1px solid black">Status</th>
            </tr>
            @foreach ($readings as $reading)
                <tr>
                    <td>{{ $reading['meter_number'] }}</td>
                    <td style="text-align: center">{{ $reading['scheme'] }}</td>
                    <td style="text-align: center">{{ $reading['customer_name'] }}</td>
                    <td style="text-align: center">{{ $reading['customer_email'] }}</td>
                    <td style="text-align: center">{{ $reading['reading'] }}</td>
                    <td style="text-align: center">{{ $reading['reading_status'] }}</td>
                </tr>
            @endforeach
        </table>
    @endif

    <br /><br />
    Best Regards,<br />
    Prepago Team
</div>

</body>
</html>