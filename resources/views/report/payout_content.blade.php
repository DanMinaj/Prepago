<div style="clear:both"></div>

@if (\Auth::user()->isUserTest())
    <h3 style="float:left">
        {{ $scheme_name }} (Scheme Number {{ $scheme_number }})
    </h3>
    <h3 style="float: right;">
@else
    <h3>
@endif

    <form method="post" action="{{ $csvURL }}" id="csv-form-{{$scheme_number}}">
        <input type="hidden" value="13.5" id="vat-{{ $scheme_number }}" name="vat" />
        <a href="javascript:;" id="csv-url-{{ $scheme_number }}">Download CSV</a>
    </form>
</h3>

<div style="clear:both"></div>

@include('includes.notifications')
<table class="table payout-report" style="border: 1px solid #ddd;">
    <tbody>
    <tr>
        <th>Set Date</th>
        <td>{{{ $data['set_date'] }}}</td>
    </tr>
    <tr>
        <th>Number Of Days</th>
        <td>{{{ $data['number_of_days'] }}}</td>
    </tr>
    <tr>
        <th>Set VAT rate</th>
        <td>{{ Form::text('', '13.5', ['class' => 'form-control input-small', 'id' => 'vat-rate-' . $scheme_number ]) }} %</td>
    </tr>
    <tr>
        <th>Number of Payments</th>
        <td>{{{ $data['number_of_payments'] }}}</td>
    </tr>
    <tr>
        <th>Value Of Payments</th>
        <td>{{ $data['value_of_payments'] }}</td>
    </tr>
    <tr>
        <th>Number of SMS Messages</th>
        <td>{{ $data['number_of_sms'] }}</td>
    </tr>
    <tr>
        <th>Apps Installed</th>
        <td>{{ $data['apps_installed'] }}</td>
    </tr>
    <tr>
        <th>IOU Chargeable</th>
        <td>{{ $data['IOU_chargeable'] }}</td>
    </tr>
    <tr>
        <th>IOU Number</th>
        <td>{{ $data['IOU_number'] }}</td>
    </tr>
    <tr>
        <th>Number Of Meters</th>
        <td>{{ $data['number_of_meters'] }}</td>
    </tr>
    <tr>
        <th>Meters Total</th>
        <td>{{ $data['meter_total'] }}</td>
    </tr>
    <tr>
        <th>Number Of Meters (All)</th>
        <td>{{ $data['number_of_meters_all'] }}</td>
    </tr>
    <tr>
        <th>Meters Total (All)</th>
        <td>{{ $data['meter_total_all'] }}</td>
    </tr>
    <tr>
        <th>Show VAT Rate</th>
        <td id="show-vat-rate-{{ $scheme_number }}"></td>
    </tr>
	<tr>
        <th>Total Heat Sold</th>
        <td>{{ $data['scheme_total_usage'] }}</td>
    </tr>
	<tr>
        <th>Tariff 1 [ kWh unit ]</th>
        <td>{{ $data['tariff_1'] }}</td>
    </tr>
	
	<tr>
        <th>Tariff 2</th>
        <td>{{ $data['tariff_2'] }}</td>
    </tr>
    <tr>
        <th>Average Usage</th>
        <td>{{ number_format((float)$data['scheme_avg_usage'], 2, '.', '') }}</td>
    </tr>
    <tr>
        <th>Average Cost</th>
        <td>{{ number_format((float)$data['scheme_avg_cost'], 2, '.', '') }}</td>
    </tr>
	<tr>
        <th>Closed accounts</th>
        <td>{{ $data['closed_accounts'] }}</td>
    </tr>
	<tr>
        <th>Account statements issued</th>
        <td>{{ count($data['statements_issued']) }}</td>
    </tr>
    </tbody>
</table>
</div>
</div>


<script type="text/javascript">
    setTimeout( function(){
        setVatRate();
    }, 1000);

    $("#vat-rate-{{ $scheme_number }}").change(function()
    {
        setVatRate();
    });

    function setVatRate()
    {
        $("#show-vat-rate-{{ $scheme_number }}").text($("#vat-rate-{{ $scheme_number }}").val() + " %");
        $("#vat-{{ $scheme_number }}").val($("#vat-rate-{{ $scheme_number }}").val());
    }

    $("#csv-url-{{ $scheme_number }}").click(function()
    {
        $("#csv-form-{{ $scheme_number }}").submit();
        //$("#csv-url").attr('href').split("/").pop();
        //$("#csv-url").attr('href', $("#csv-url").attr('href') + "/" + $("#vat-rate").val());
    });
</script>