                            
</div>

<div><br/></div>
<h1>Meter Info</h1>


<a href="{!! URL::to('installed_meters') !!}" class="btn btn-primary">&laquo; Back</a>
<br /><br />

<ul class="nav nav-tabs">
    <li class="active"><a href="#m_data" data-toggle="tab">Meter Data</a></li>
    <li><a href="#m_readings" data-toggle="tab">Meter Readings</a></li>
</ul>
<div class="tab-content">
    <div class="tab-pane active" id="m_data">

	    <div class="custome_left">
			<label>Username</label>
	        <input type="text" class="cus-in" value="{!! $meter['username'] !!}" disabled="disabled" />
	        <label>Meter Type</label>
	        <input type="text" class="cus-in" value="{!! $meter['meter_type'] !!}" disabled="disabled" />
	        <label>Meter Number</label>
	        <input type="text" class="cus-in" value="{!! $meter['meter_number'] !!}" disabled="disabled" />
	        <label>Install Date</label>
	        <input type="text" class="cus-in" value="{!! $meter['install_date'] !!}" disabled="disabled" />
	        <label>SCU Number</label>
	        <input type="text" class="cus-in" value="{!! $meter['scu_number'] !!}" disabled="disabled" />
	        <label>SCU Port</label>
	        <input type="text" class="cus-in" value="{!! $meter['scu_port'] !!}" disabled="disabled" />
	        @if($meter['heat_port'] != -1)
	        <label>Heat Port</label>
	        <input type="text" class="cus-in" value="{!! $meter['heat_port'] !!}" disabled="disabled" />
	        @endif
	        <label>In Use</label>
	        <input type="text" class="cus-in" value="<?php echo ($meter['in_use'] == 0)? 'No': 'Yes'; ?>" disabled="disabled" />
	        @if($meter['is_boiler_room_meter'] == 1)
	        <label>Is Boiler Room Meter</label>
	        <input type="text" class="cus-in" value="Yes" disabled="disabled" />
	        @endif
		</div>

		<div class="custome_left" style="margin-left: 2em;">

			<label>House Name or Number</label>
	        <input type="text" class="cus-in" value="{!! $meter['house_name_number'] !!}" disabled="disabled" />
	        <label>Street</label>
	        <input type="text" class="cus-in" value="{!! $meter['street1'] !!}" disabled="disabled" />
	        <label>Town</label>
	        <input type="text" class="cus-in" value="{!! $meter['town'] !!}" disabled="disabled" />
	        <label>County</label>
	        <input type="text" class="cus-in" value="{!! $meter['county'] !!}" disabled="disabled" />
	        <label>Country</label>
	        <input type="text" class="cus-in" value="{!! $meter['country'] !!}" disabled="disabled" />

	    </div>

		<div style="clear: both;"></div>

		<div class="custome_left" style="margin-top: 2em;">
			<label>Meter Make</label>
	        <input type="text" class="cus-in" value="{!! $meter['meter_make'] !!}" disabled="disabled" />
	        <label>Meter Model</label>
	        <input type="text" class="cus-in" value="{!! $meter['meter_model'] !!}" disabled="disabled" />
	        <label>Meter Manufacturer</label>
	        <input type="text" class="cus-in" value="{!! $meter['meter_manufacturer'] !!}" disabled="disabled" />
	        <label>Meter Baud Rate</label>
	        <input type="text" class="cus-in" value="{!! $meter['meter_baud_rate'] !!}" disabled="disabled" />
	        <label>Meter Readings Per Day</label>
	        <input type="text" class="cus-in" value="{!! $meter['readings_per_day'] !!}" disabled="disabled" />
		</div>

		<div class="custome_left" style="margin-left: 2em; margin-top: 2em;">
			<label>HIU Make</label>
	        <input type="text" class="cus-in" value="{!! $meter['HIU_make'] !!}" disabled="disabled" />
	        <label>HIU Model</label>
	        <input type="text" class="cus-in" value="{!! $meter['HIU_model'] !!}" disabled="disabled" />
	        <label>HIU Manufacturer</label>
	        <input type="text" class="cus-in" value="{!! $meter['HIU_manufacturer'] !!}" disabled="disabled" />
		</div>

		<div class="custome_left" style="margin-left: 2em; margin-top: 2em;">
			<label>Valve Make</label>
	        <input type="text" class="cus-in" value="{!! $meter['valve_make'] !!}" disabled="disabled" />
	        <label>Valve Model</label>
	        <input type="text" class="cus-in" value="{!! $meter['valve_model'] !!}" disabled="disabled" />
	        <label>Valve Manufacturer</label>
	        <input type="text" class="cus-in" value="{!! $meter['valve_manufacturer'] !!}" disabled="disabled" />
		</div>


    </div>
    
    <div class="tab-pane" id="m_readings">

        <form method="post" action="<?php echo URL::to('installed_meters/meter_data_search') ?>"class="form-inline" style="float:right">
	        <label>From</label>
	        <input id="from" name="from" type="text">
	        <label>To</label>
	        <input id="to" name="to" type="text">
	        <input name="meter_id" type="hidden" value="{!! $meter['ID'] !!}">
	        <input type="submit" value="search" class="btn-success"/>
	    </form>

	    <script type="text/javascript">
		    $(document).ready(function()
		    {
		        $("#to").datepicker({ dateFormat: 'dd-mm-yy' });
		        $("#from").datepicker({ dateFormat: 'dd-mm-yy' });
		    });

		</script>
		
		<table class="table table-bordered" style="width: 250px;">
			<tr><td style="font-weight: bold;">Date</td><td style="font-weight: bold;">Reading</td></tr>

			@foreach($readings as $reading)
				
				<tr><td>{!! date('d-m-Y', strtotime($reading['time_date'])) !!}</td><td>{!! $reading['reading1'] !!} {!! $abb !!}</td></tr>

			@endforeach
		</table>	

    </div>


</div>

<div class="cl">&nbsp;</div>
</div>
</div>
</body>
</html>
