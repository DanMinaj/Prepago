<!DOCTYPE html>
<html lang="en-US">
	<head>
		<meta charset="utf-8"/>
	</head>
	
	<body>
	<style>
		.prepago{
			color: rgb(153, 51, 153);
			font-weight: bold;
		}
		.ie{
			colour:rgba(255, 72, 41);
			font-weight: bold;
		}
	</style>
		<div>
		
			Dear administrator,
			<br/><br/>
			
			A new app bug report has been created by <b>{{ $bug->apt_number }}{{ $bug->apt_building }}</b> and allocated ref #{{ $bug->id }}.
			<br/><br/>
			<a style='background: #4d90fe; padding: 14px; display: block; margin-top: 2%; border-radius: 3px; color: white; font-size: 1rem; border: 1px solid #ccc;' 
			href="https://prepagoplatform.com/bug/reports/view/{{$bug->id}}">Click here to view.</a>	
			
			<p style='background: #ffffff; padding: 20px; /* border-radius: 2px; */ color: #000000; border-top: 1px solid #636363;'>
				<h3>Description:</h3>
				<i>
					{{ $bug->description }}
				</i>
				<br/>
				@if($bug->customer)
					<h3>Customer's Info:</h3>
					<a href="https://prepagoplatform.com/customer/{{ $bug->customer->id }}">{{ $bug->customer->username }}</a> <br/>
					<b>Balance:</b> &euro;{{ $bug->customer->balance }}<br/>
					@if($bug->customer->districtMeter)<b>Temp:</b> {{ $bug->customer->districtMeter->last_flow_temp }}&deg; 
						@if(strlen($bug->customer->districtMeter->last_temp_time) > 3) 
							({{ Carbon\Carbon::parse($bug->customer->districtMeter->last_temp_time)->diffForHumans() }}) 
						@endif<br/>
					@endif
					@if($bug->customer->lastCommand)<span><b>Last valve command:</b> {{ ($bug->customer->lastCommand->turn_service_on == 1) ? 'Open' : 'Close' }} - {{ Carbon\Carbon::parse($bug->customer->lastCommand->time_date)->diffForHumans() }} @if($bug->customer->lastCommand->away_mode_initiated == 1) (away-mode) @endif</span><br/>
					@endif
				@else
					<i>{{ $bug->apt_number }} {{ $bug->apt_building }}</i>
				@endif
			</p>
			
			<hr>
			
			<br/>
			
			<b>Prepago Platform Support</b>
			<br/>
			<span class="prepago">Prepago</span><span class="ie">.ie</span>
			<br/><br/>
			1 Woodbine Avenue, Blackrock<br/>
			Co.Dublin, Ireland

			<br/><br/>
			<b>Mobile</b> +353 (0) 87 253 4708
			<br/><br/>
			www.Prepago.ie
            <br/><br/>
			<b style='color:rgb(153, 0, 255)'>Prepay; done right.</b>
			
		</div>
		
	</body>
</html>
