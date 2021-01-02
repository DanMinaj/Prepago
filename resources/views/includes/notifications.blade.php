@if (count($errors->all()) > 0)
<div class="alert alert-danger alert-block">
	<button type="button" class="close" data-dismiss="alert">&times;</button>
	@foreach ($errors->all() as $error)
        <div>{!! $error !!}</div>
    @endforeach
</div>
@endif

@if ($message = Session::get('successMessage'))
<div class="alert alert-success alert-block">
	<button type="button" class="close" data-dismiss="alert">&times;</button>
	{!! $message !!}
</div>
@endif

@if ($message = Session::get('errorMessage'))
<div class="alert alert-danger alert-block">
	<button type="button" class="close" data-dismiss="alert">&times;</button>
	{!! $message !!}
</div>
@endif

@if ($message = Session::get('warning'))
<div class="alert alert-warning alert-block">
	<button type="button" class="close" data-dismiss="alert">&times;</button>
	{!! $message !!}
</div>
@endif

@if ($message = Session::get('info'))
<div class="alert alert-info alert-block">
	<button type="button" class="close" data-dismiss="alert">&times;</button>
	{!! $message !!}
</div>
@endif

@if(isset($data))
	@if(isset($data->permanentMeter) && isset($data->districtMeter))
		<!-- Warn if customer has duplicate district_heating_meters entrys -->
		@if(DistrictHeatingMeter::where('permanent_meter_ID', $data->permanentMeter->ID)->count() > 1 && \Auth::user()->isUserTest())
		<div class="alert alert-danger alert-block">
		  <b>Attention: </b> This customer has duplicate entries in district_heating_meters. Please fix this to prevent issues!
		</div>
		@endif

		<!-- Warn if customer has duplicate permanent_meter_data entrys -->
		@if(PermanentMeterData::where('username', $data['username'])->count() > 1 && \Auth::user()->isUserTest())
		<div class="alert alert-danger alert-block">
		  <b>Attention: </b> This customer has duplicate entries in permanent_meter_data. Please fix this to prevent issues!
		</div>
		@endif

		<?php $dupe_customers = PermanentMeterData::join('customers', 'permanent_meter_data.username', '=', 'customers.username')
		->whereRaw("( (scu_number = '" . $data['scu_number'] . "' OR m_bus_relay_id = '" . $data['scu_number'] . "') AND scu_number != '00000000' AND m_bus_relay_id != '00000000' )")
		->whereRaw("(customers.deleted_at IS NULL AND LOWER(customers.username) != '" . strtolower($data['username']) . "')")->get(); 
		?>
		
		<!-- Warn if customer has duplicate permanent_meter_data SCU -->
		@if(count($dupe_customers) > 0 && \Auth::user()->isUserTest())
		<div class="alert alert-danger alert-block">
		
			
		  <b>Attention: </b> This customer ({!! $data['username']!!}) SCU ({!! $data['scu_number'] !!}) is being used by <b>{!! count($dupe_customers) !!}</b> other customer(s)! Please fix this to prevent issues!:
		  <hr/>
		  
		  @foreach($dupe_customers as $k => $v) 
			<a href="/customer/{!! $v->username !!}">{!! $v->username !!} #{!! $v->id !!}</a><br/>
		  @endforeach
		  
		</div>
		@endif


		<!-- Warn if customer's  district_heating_meters.meter_number != permanent_meter_data.meter_number -->
		@if($data->permanentMeter->meter_number_clean != $data->districtMeter->meter_number_clean)
		<div class="alert alert-danger alert-block">
		  <b>Attention: </b> This customer district_heating_meters.meter_number is not equal to permanent_meter_data.meter_number. Please fix this to prevent issues!
		</div>
		@endif

		<!-- Warn if customer has another customer sharing its meter_number in district_heating_meters -->
		@if(DistrictHeatingMeter::where('meter_number', 'like', '%' . $data->permanentMeter. '%')->count() > 1 && \Auth::user()->isUserTest())
		<div class="alert alert-danger alert-block">
		  <b>Attention: </b> This customer has duplicate entries in permanent_meter_data. Please fix this to prevent issues!
		</div>
		@endif
		
	@endif
@endif


<div class="alert alert-success alert-block" style="display:none;" id="support-success">
	<button type="button" class="close" data-dismiss="alert">&times;</button>
	<span id="support-reply"></span>
</div>

<div class="alert alert-danger alert-block" style="display:none;" id="support-error">
	<button type="button" class="close" data-dismiss="alert">&times;</button>
	<span id="support-error-reply"></span>
</div>
