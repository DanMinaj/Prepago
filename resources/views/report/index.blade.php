
</div>

<div><br/></div>
<h1>Reports</h1>


<div class="admin2">

    <div class="cl"></div>

    <ul>
        @if (hasAccess('supply.report.units'))
            <li class=""><a href="{{ URL::to('system_reports/supply_report_units') }}">Supply Report-Units</a></li>
        @endif

        @if (hasAccess('topup.reports'))
            <li class=""><a href="{{ URL::to('system_reports/topup_reports') }}">Top-Up Reports</a></li>
        @endif

        @if (hasAccess('barcode.reports'))
            <li class=""><a href="{{ URL::to('system_reports/barcode_reports') }}">Barcode Reports</a></li>
        @endif

        <!--<li class=""><a href="{{ URL::to('system_reports/messaging_reports') }}">Messaging Reports</a></li>
        <li class=""><a href="{{ URL::to('system_reports/customer_supply_status') }}">Customer Supply Status</a></li>-->

        @if (hasAccess('sms.messages.sent'))
            <li class=""><a href="{{ URL::to('system_reports/sms_messages') }}">SMS Messages Sent</a></li>
        @endif

        @if (hasAccess('list.all.customers'))
            <li class=""><a href="{{ URL::to('system_reports/list_all_customers') }}">List Of All Customers</a></li>
        @endif

        @if (hasAccess('deleted.customer.report'))
            <li class=""><a href="{{ URL::to('system_reports/deleted_customers') }}">Deleted Customers Report</a></li>
        @endif

        @if (hasAccess('inactive.landlords.report'))
            <li class=""><a href="{{ URL::to('system_reports/inactive_landlords') }}">Inactive Landlords Report</a></li>
        @endif

        @if (hasAccess('deposit.report'))
            <li class=""><a href="{{ URL::to('customer_supply_status/deposit_reports') }}">Deposit Reports</a></li>
        @endif

        @if (hasAccess('credit.issue.report'))
            <li class=""><a href="{{ URL::to('system_reports/credit_issue_reports') }}">Credit Issue Reports</a></li>
        @endif

        @if (hasAccess('weather.report'))
            <li class=""><a href="{{ URL::to('system_reports/weather_reports') }}">Weather Reports</a></li>
        @endif

        @if (hasAccess('bill.reports'))
            <li class=""><a href="{{ URL::to('system_reports/bill_reports') }}">Bill Reports</a></li>
        @endif

        @if (hasAccess('payout.reports'))
            <li class=""><a href="{{ URL::to('system_reports/payout_reports') }}">Payout Reports</a></li>
        @endif

        @if (hasAccess('not.read.meters.reports'))
            <li class=""><a href="{{ URL::to('system_reports/not_read_meters') }}">Not Read Meters Reports</a></li>
        @endif
    </ul>


</div>