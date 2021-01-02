
</div>

<div><br/></div>
<h1>Billing Engine Flags ({!! count($flags) !!})</h1>


<div class="admin2">
	
	@include('includes.notifications')
	
	@if(count($flags) == 0)
		There are no flags.
	@else
	@foreach($flags as $f)
		
		@if($f->customer)
			
		<table width="100%" class="table table-bordered">
			
			<tr>
				
				<td width="10%">
					<b>Customer </b>
				</td>
				
				<td width="90%">
					<a href="{!! URL::to('customer/' . $f->customer->id) !!}">{!! $f->customer->username !!} ({!! $f->customer->id !!})</a>
				</td>
				
			</tr>
			
			<tr>
				
				<td width="10%">
					<b>Current balance </b>
				</td>
				
				<td width="90%">
					&euro;{!! $f->customer->balance !!}
				</td>
				
			</tr>
			
			<tr>
				<td width="10%"><b>Flagged at</b></td>
				<td width="90%">{!! $f->updated_at !!}</td>
			</tr>
			
			<tr>
				<td width="10%"><b>Usage to approve</b></td>
				<td width="90%">{!! $f->kwh_usage !!} kWh &horbar; &euro;{!! $f->amount !!}</td>
			</tr>
				
			<tr>
				<td width="10%"><b>Flag sudo_reading</b></td>
				<td width="90%">{!! $f->sudo_reading !!}</td>
			</tr>
				
			<tr>
				<td width="10%"><b>Flag latest_reading</b></td>
				<td width="90%">{!! $f->latest_reading !!}</td>
			</tr>
			
			<tr>
				<td width="10%"><b>Avg. Usage for Prior 7 days</b></td>
				<td width="90%">{!! number_format($f->avgUsage(7), 0) !!}kWh</td>
			</tr>
			
			<tr>
				<td width="100%" colspan="2">
					<div class="alert alert-warning"> 
						<b>Recommended action 
						<span style='float:right;'>
						<input class="customSpread customSpread_{!! $f->id!!}" flag_id='{!! $f->id !!}' style='text-align:center;width:80px;padding-left:20px;' value='{!! $f->missingDays !!}' type='number'>
						<button class='btn btn-primary undo' flag_id='{!! $f->id !!}' style='margin-bottom:5%;' class='btn btn-primary'><i class='fa fa-redo'></i></button>
						</span>
						</b> 
						<br/>
						<div class='customSpreadArea_{!! $f->id!!}'>
						There were <span class='customSpreadTxt_{!! $f->id !!}'>{!! $f->missingDays !!}</span> day(s) where the usage was 0. 
						<br/>Would you like to spread this charge over those days?
						</div>
						<center>
						<form onsubmit="return confirm('You are about to approve this flag & spread it\'s charges over ' + $('input[name=spread_days_196]').val() + ' days. Are you sure?');" style="margin:0px !important;" action="{!!  URL::to('billing/' . $f->customer->id . '/auto_spread') !!}" method="POST">
							<input type="hidden" name="flag_id" value="{!! $f->id !!}"/>
							<input type="hidden" name="spread_days_original_{!! $f->id !!}" value="{!! $f->missingDays !!}"/>
							<input type="hidden" name="spread_days_{!! $f->id !!}" value="{!! $f->missingDays !!}"/>
							<button style='font-size:0.7rem' type="submit" class="btn btn-warning">Spread over <span class='customSpreadTxt_{!! $f->id !!}'>{!! $f->missingDays !!}</span> days</button>
						</form>
						</center>
					</div>
				</td>
			</tr>
			
			
			<tr>
				<td width="50%">
					<center>
					<form action="{!!  URL::to('billing/' . $f->customer->id . '/approve_flag') !!}" method="POST">
						<input type="hidden" name="flag_id" value="{!! $f->id !!}"/>
						<button type="submit" class="btn btn-success">Approve</button>
					</form>
					</center>
				</td>
				<td width="50%">
					<center>
					<form action="{!!  URL::to('billing/' . $f->customer->id . '/decline_flag') !!}" method="POST">
						<input type="hidden" name="flag_id" value="{!! $f->id !!}"/>
						<button type="submit" class="btn btn-danger">Decline</button>
					</form>
					</center>
				</td>
			</tr>
			
				
		</table>
		
		@endif
		
	@endforeach
	@endif
		
</div>

<script>
	$(function(){
		
		$('.customSpread').on('change', function(){
			var flag_id = $(this).attr('flag_id');
			var input_div = $('input[name=spread_days_' + flag_id + ']');
			var display_div = $('.customSpreadTxt_' + flag_id);
			var area_div = $('.customSpreadArea_' + flag_id);
			var input_original_div = $('input[name=spread_days_original_' + flag_id + ']');
			
			area_div.hide();
			display_div.text($(this).val());
			input_div.val($(this).val());
			if(parseInt($(this).val()) == parseInt(input_original_div.val())) {
				$('.undo').trigger('click');
			}
		});
		
		$('.undo').on('click', function(){
			
			var flag_id = $(this).attr('flag_id');
			var input_div = $('input[name=spread_days_' + flag_id + ']');
			var spread_div = $('.customSpread_' + flag_id);
			var display_div = $('.customSpreadTxt_' + flag_id);
			var area_div = $('.customSpreadArea_' + flag_id);
			var input_original_div = $('input[name=spread_days_original_' + flag_id + ']');
			
			
			area_div.show();
			spread_div.val(input_original_div.val());
			display_div.text(input_original_div.val());
			input_div.val(input_original_div.val());
			
		});
		
	});
</script>

	