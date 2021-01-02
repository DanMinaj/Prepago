<!DOCTYPE html>
<html lang="en-US">
<head>
    <meta charset="utf-8">
</head>
<body>

<div>
	Generated <?php echo date('F j Y H:i:s'); ?> <br/><br/>
	
    Hi Aidan, <br /><br />
    Here are the details from the automated Daily Checklist Queries: <br  /><br />
	
	@if ($shutOffCustomers && $shutOffCustomers->count())
        <h2>Shut Off Customers: </h2>
        <table style="width: 100%">
            <tr align-"right">
                <th>Customer</th>
                <th>Permanent Meter ID</th>
                <th>District Meter ID</th>
                <th>Meter Number</th>
                <th>Scheme Name</th>
                <th>Shut Off Reading</th>
				<th>Shut Off Reading - Latest Reading</th>
                <th>Shut Off Time</th>
            </tr>
            @foreach ($shutOffCustomers as $shutOffCustomer)
                <tr align="center">
                    <td><a href="https://prepagoplatform.com/customer/{{{ $shutOffCustomer->customer_username }}}">{{{ $shutOffCustomer->customer_username }}}</a></td>
                    <td>{{{ $shutOffCustomer->permanent_meter_ID }}}</td>
                    <td>{{{ $shutOffCustomer->district_meter_ID }}}</td>
                    <td>{{{ $shutOffCustomer->permanent_meter_number }}}</td>
                    <td>{{{ $shutOffCustomer->scheme_name }}}</td>
                    <td>{{{ $shutOffCustomer->shut_off_reading }}}</td>
					<td>{{{ abs($shutOffCustomer->shut_off_reading - $shutOffCustomer->sudo_reading) }}}</td>
                    <td>
						@if(strlen($shutOffCustomer->last_shut_off_time) > 3) 
							{{{ $shutOffCustomer->last_shut_off_time }}} ({{{ Carbon\Carbon::parse($shutOffCustomer->last_shut_off_time)->diffForHumans() }}})
						@else
							N/A
						@endif
					</td>
                </tr>
            @endforeach
        </table>
    @endif
	
	<br /><br />

	@if ($readingShutOffMeters && $readingShutOffMeters->count())
		<h2>Shut Off Meters That Are Still Reading: </h2>
        <table style="width: 100%">
            <tr align="left">
			    <th>Customer</th>
                <th>Permanent Meter ID</th>
                <th>District Meter ID</th>
                <th>Meter Number</th>
                <th>Scheme Name</th>
                <th>Shut Off Reading (a)</th>
                <th>End Day Reading (b)</th>
                <th>Usage (b-a)</th>
				<th>Shut Off Time</th>
            </tr>
            @foreach ($readingShutOffMeters as $readingShutOffMeter)
                <tr align="center">
					<td align="center"><a href="https://prepagoplatform.com/customer/{{ $readingShutOffMeter->customer_username }}">{{{ $readingShutOffMeter->customer_username }}}</a></td>
                    <td>{{{ $readingShutOffMeter->permanent_meter_ID }}}</td>
                    <td>{{{ $readingShutOffMeter->district_meter_ID }}}</td>
                    <td>{{{ $readingShutOffMeter->permanent_meter_number }}}</td>
                    <td>{{{ $readingShutOffMeter->scheme_name }}}</td>
                    <td>{{{ $readingShutOffMeter->shut_off_reading }}}</td>
                    <td>{{{ $readingShutOffMeter->end_day_reading }}}</td>
                    <td>{{{ $readingShutOffMeter->usage }}}</td>
					<td>
						@if(strlen($readingShutOffMeter->last_shut_off_time) > 3) 
							{{{ $readingShutOffMeter->last_shut_off_time }}} ({{{ Carbon\Carbon::parse($readingShutOffMeter->last_shut_off_time)->diffForHumans() }}})
						@else
							N/A
						@endif
					</td>
                </tr>
            @endforeach
        </table>
    @endif

    <br /><br />
	
    @if ($nonReadingMeters && count($nonReadingMeters) > 0)
		<h2>Non Reading Meters: </h2>
		<table style="width: 100%">
			<tr align="left">
				<th>Customer</th>
				<th>Permanent Meter ID</th>
				<th>District Meter ID</th>
				<th>Scheme Name</th>
				<th>Meter Number</th>
				<th>Last Reading</th>
				<th>Last Reading Time</th>
			</tr>
			@foreach ($nonReadingMeters as $nonReadingMeter)
				<tr align="left">
					<td><a href="https://prepagoplatform.com/customer/{{ $nonReadingMeter->customer_username }}">{{ $nonReadingMeter->customer_username }}</a></td>
					<td>{{{ $nonReadingMeter->permanent_meter_id }}}</td>
					<td>{{{ $nonReadingMeter->dhm_id }}}</td>
					<td>{{{ $nonReadingMeter->scheme_name }}}</td>
					<td>{{{ $nonReadingMeter->meter_number }}}</td>
					<td>
						@if($nonReadingMeter->lastReadingTime != 'Never')
							{{ $nonReadingMeter->lastReading }} kWh
						@else
							N/A
						@endif
					</td>
					<td>
					{{ $nonReadingMeter->lastReadingTime }} 
					@if(strlen($nonReadingMeter->lastReadingTime) > 3 && $nonReadingMeter->lastReadingTime != 'Never') 
						({{ Carbon\Carbon::parse($nonReadingMeter->lastReadingTime)->diffForHumans() }})
					@else
						
					@endif
					</td>
				</tr>
			@endforeach
		</table>
	@endif	

    <br /><br />

    @if ($remoteControlErrors && $remoteControlErrors->count())
		<h2>Remote Control Errors: </h2>
		<table style="width: 100%">
			<tr align="left">
				<th>Permanent Meter ID</th>
				<th>Date/Time</th>
				<th>Action</th>
				<th>Error</th>
			</tr>
			@foreach ($remoteControlErrors as $remoteControlError)
				<tr align="center">
					<td>{{{ $remoteControlError->permanent_meter_id }}}</td>
					<td>{{{ $remoteControlError->date_time }}}</td>
					<td>{{{ $remoteControlError->action }}}</td>
					<td>{{{ $remoteControlError->error }}}</td>
				</tr>
			@endforeach
		</table>
	@endif
    
    <br /><br />

    <br /><br />
    Regards,<br />
    Mariana
</div>

</body>
</html>
