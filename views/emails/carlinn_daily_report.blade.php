<!DOCTYPE html>
<html lang="en-US">
<head>
    <meta charset="utf-8">
</head>
<body>

<div>
    Hi Kathy Cranny, <br /><br />

    @if ($warningResults && $warningResults->count())
        <strong>WARNING: Customer's device needs hard reset</strong><br />
        <table style="width: 50%">
            <tr>
                <td style="border-bottom: 1px solid #000">Customers Usernames</td>
            </tr>
            @foreach ($warningResults as $warning)
                <tr>
                    <td>{{{ $warning->customers->username }}}</td>
                </tr>
            @endforeach
        </table>

        <br /><br />
    @endif

    @if ($errorResults && $errorResults->count())
        <strong>ERROR: Comes when warnings are not fixed</strong><br />
        <table style="width: 50%">
            <tr>
                <td style="border-bottom: 1px solid #000">Customers Usernames</td>
            </tr>
            @foreach ($errorResults as $error)
                <tr>
                    <td>{{{ $error->customers->username }}}</td>
                </tr>
            @endforeach
        </table>

        <br /><br />
    @endif

    @if ($criticalResults && $criticalResults->count())
        <strong>CRITICAL: Needs immediate hard reset as we are loosing payments as customers are not billed</strong><br />
        <table style="width: 50%">
            <tr>
                <td style="border-bottom: 1px solid #000">Customers Usernames</td>
            </tr>
            @foreach ($criticalResults as $critical)
                <tr>
                    <td>{{{ $critical->customers->username }}}</td>
                </tr>
            @endforeach
        </table>

        <br /><br />
    @endif

    Best Regards,<br />
    Prepago Team
</div>

</body>
</html>