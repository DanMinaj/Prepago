<!DOCTYPE HTML>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>{!! $pdf_name !!}</title>
</head>
<body>
<style>
*{

}
body{
	font-family: Verdana, Geneva, sans-serif;
}
table{
	
}
h4{

}

td{
	text-align: left;
}
</style>

<table width="100%">
	
	<tr>
		<td colspan='2' width="100%">
			<h1>
				ICCID {!! $scheme->sim->ICCID !!}<br/>
				<font size='15px;font-weight:normal;'>SIM Status Report</font><br/>
				<font size='8px;font-weight:normal;'>Generated {!! date('Y-m-d H:i:s') !!}</font>
			</h1>
		</td>
	</tr>
	<tr>
		<td width="100%">
			<b>Name:</b> {!! $scheme->sim->Name !!} <br/>
			<b>IP Address:</b> {!! $scheme->sim->IP_Address !!} <br/>
			<b>ICCID:</b> {!! $scheme->sim->ICCID !!} <br/>
			<b>MSISDN:</b> {!! $scheme->sim->MSISDN !!}
		</td>
	</tr>
	
</table>

<hr/>
<h1> SIM Status Tracking </h1>
<table width="100%">
	
	<tr>
		<td width="100%"><b>Report range:</b> {!! $scheme->tracking->get()->last()->date !!} - {!! $scheme->tracking->get()->first()->date !!}</td>
	</tr>
	
	<tr>
		<td width="100%"><b>Avg. Online time:</b> {!! number_format($scheme->tracking->avg('uptime_percentage'), 2) !!}%</td>
	</tr>
	
	<tr>
		<td width="100%"><b>Avg. Ping failures a day:</b> {!! number_format($scheme->tracking->avg('offline_times'), 0) !!}</td>
	</tr>
	
	<tr>
		<td width="100%"><b>Last offline:</b> {!! $scheme->tracking->where('last_offline', '!=', '0000-00-00 00:00:00')->first()->last_offline !!}</td>
	</tr>
	
</table>
<hr/>


<h3>Daily Breakdown</h3>
	
	@foreach($scheme->tracking->get() as $t)
	<table style='border-collapse:collapse;' border='1' width="100%">
	
	<tr>
		<td width="50%"><b> Date: </b> </td>
		<td width="50%">{!! $t->date !!} @if($t->date == date('Y-m-d')) (Today*) @endif</td>
	</tr>
	<tr>
		<td width="50%"><b> <font color='red'>Ping failures</font> </b></td>
		<td width="50%">{!! $t->offline_times !!}</td>
	</tr>
	<tr>
		<td width="50%"><b> Online %</b></td>
		@if($t->offline_times > 0) 
		<td width="50%">{!! number_format($t->uptime_percentage, 2) !!}%</td>
		@else
		<td width="50%">N/A</td>
		@endif
	</tr>
	<tr>
		<td width="50%"><b> Last Ping failure</b></td>
		@if($t->offline_times > 0) 
		<td width="50%">{!! $t->last_offline !!}</td>
		@else
		<td width="50%">N/A</td>
		@endif
	</tr>
	</table>
	<br/>
	
	<br/>
	@endforeach
	
	



</body>
</html>