@if(Auth::user()->isUserTest())
<br />
<div class="cl"></div>
<h1>Viewing Bug #{!!$bug->id!!}</h1>
<div class="admin">
		
@if(Session::has('successMessage'))
<div class="alert alert-success alert-block" id="support-success">
<button type="button" class="close" data-dismiss="alert">&times;</button>
{!!Session::get('successMessage')!!}
</div>
@endif

@if(Session::has('errorMessage'))
<div class="alert alert-danger alert-block" id="support-success">
<button type="button" class="close" data-dismiss="alert">&times;</button>
{!!Session::get('errorMessage')!!}
</div>
@endif

<style>
.operator_avatar{
	background:black;
	border-radius:2px;
	border:1px solid black;
	width:100px;
	height:100px;
}
.my_reply{
	background: #006dcc;
	color: white;
	border-radius: 5px;
}
.reply{
	text-align: left;
    padding-top: 5px;
    padding-left: 13px;
    border-left: 1px solid #ccc;
}
</style>

	
		<table width="100%">
				
				<tr>
					<td>
						<a href="{!! URL::to('bug/reports') !!}">
							<button type='button' class='btn btn-primary'>
							<i class='fa fa-arrow-left'></i> Back
							</button>
						</a><br/><br/>
					 </td>
				</tr>
				
				<tr>
					<td width="25%">
						
						<h5>{!! $bug->created_at !!} &horbar; {!! \Carbon\Carbon::parse($bug->created_at)->diffForHumans() !!} </h5>
						
							
							@if($bug->customer)
								<h4>Customer: <a href="/customer/{!!$bug->customer->id!!}"> {!! $bug->customer->username !!} </a> </h4>
								<h4>Scheme: {!! $bug->customer->scheme->scheme_nickname !!} </h4>
							@else
								<h4>Customer: 
								 <a target="_blank" href="/customer/{!! ReportABug::decipherUsername($bug->apt_number . $bug->apt_building) !!}">
								 {!! ReportABug::decipherUsername($bug->apt_number . $bug->apt_building) !!} *guest
								 </a>
								 </h4>
								 @if($bug->scheme) 
								 <h4>Scheme: {!! $bug->scheme->scheme_nickname !!} </h4>
								@endif
							@endif
							@if($bug->customer)
								@if($bug->customer->districtMeter)
									@if($bug->customer->lastCommand && $bug->customer->lastCommand->away_mode_initiated == 1)
									<span style='font-size:0.7rem;'>- <b>Using Away Mode</b> -</span>
									<br/>
									@endif
									<span style='font-size:0.7rem;'>Balance: &euro;{!! number_format($bug->customer->balance, 2) !!} </span><br/>
									<span style='font-size:0.7rem;'>Meter Temp: {!! $bug->customer->districtMeter->last_flow_temp !!}&deg;C &horbar; {!! Carbon\Carbon::parse($bug->customer->districtMeter->last_temp_time)->diffForHumans() !!}</span>
									@if($bug->customer->lastCommand)
										<br/><span style='font-size:0.7rem;'>Last valve command:  {!! ($bug->customer->lastCommand->turn_service_on == 1) ? 'Open' : 'Close' !!} &horbar; {!! Carbon\Carbon::parse($bug->customer->lastCommand->time_date)->diffForHumans() !!} @if($bug->customer->lastCommand->away_mode_initiated == 1) (away-mode) @endif</span>
									@endif
								@endif
							@endif
							
					
						
					</td>
					@if($bug->customer)
					
					<input type="hidden" id="text_field" value="reply"/>
					<input type="hidden" id="email_address_field" value="{!! $bug->customer->email_address !!}"/>
					<input type="hidden" id="username_field" value="{!! $bug->customer->username !!}"/>
					<input type="hidden" id="id_field" value="{!! $bug->customer->id !!}"/>
					<input type="hidden" id="balance_field" value="{!! number_format($bug->customer->balance, 2) !!}"/>
		
					<td width="55%">
						<table width="100%">
							
								@if($bug->customer->lastEngagement)	
								<tr>
								<td>
									<b>Last login:</b> {!! $bug->customer->lastEngagement->updated_at !!} &horbar; {!! Carbon\Carbon::parse($bug->customer->lastEngagement->updated_at)->diffForHumans() !!} &horbar; <i style='color:#666'>{!! $bug->customer->lastEngagement->platform !!} {!! $bug->customer->lastEngagement->make !!}</i>
								</td>
								</tr>
								@endif
								@if($bug->customer->lastTop)
								<tr>
								<td>
									<b>Last topped up:</b> {!! $bug->customer->lastTop->time_date !!} &horbar; {!! Carbon\Carbon::parse($bug->customer->lastTop->time_date)->diffForHumans() !!}
								</td>
								</tr>
								@endif
								@if($bug->customer->districtMeter && strpos($bug->customer->districtMeter->last_shut_off_time, '0000') === false)
								<tr>
								<td>
									<b>Last shut off:</b> {!! $bug->customer->districtMeter->last_shut_off_time !!} &horbar; {!! Carbon\Carbon::parse($bug->customer->districtMeter->last_shut_off_time)->diffForHumans() !!}
								</td>
								</tr>
								@endif
						</table>
					</td>
					@endif
					<td width="20%">
						<h2>
							@if(!$bug->resolved)
							<a style="float:right;" href="{!! URL::to('bug/reports/view', ['id' => $bug->id, 'solved' => 1, 'platform' => 'snugzone']) !!}">
								<button type='button' class='btn btn-success'>
									<i class='fa fa-check'></i> Mark solved
								</button>
							</a>
							@else
							<a style="float:right;" href="{!! URL::to('bug/reports/view', ['id' => $bug->id, 'solved' => 0, 'platform' => 'snugzone']) !!}">
								<button type='button' class='btn btn-warning'>
									<i class='fa fa-lock-open'></i> Re-open
								</button>
							</a>
							@endif
						</h2> 
					</td>
				</tr>
				</table>
				
				
				<hr/>
				@if(strlen($bug->follow_up_at) > 3)
				<table width="100%">
				<tr>
					<td>
						<font style='font-size:1.5em;'>
						<div class="alert alert-success"> Customer provided feedback on this ticket {!! Carbon\Carbon::parse($bug->follow_up_at)->diffForHumans() !!} - {!! $bug->follow_up_at !!} </div>
						@if($bug->follow_up_res) 
							They said that their issue was <b>solved</b>.
						@else
							The customer said that their issue was <b>not</b> solved.
						@endif
						
						@if(strlen($bug->follow_up_reply) > 1) 
							<br/><br/>
							The customer also provided further comments: <br/><br/>
							@if($bug->customer)
								<b>{!! $bug->customer->username  !!}:</b> - {!! $bug->follow_up_at !!}<br/>
							@endif
							"{!! $bug->follow_up_reply !!}"
						@endif
						</font>
					</td>
				</tr>
						
				</table>
				<hr/>
				@endif
				<table width="100%">
				<tr>
					<td>
						<font style='font-size:1.5em;'>
						{!! $bug->description !!}
						</font>
						
						@if($bug->customer)
								
							@if(!empty($bug->customer->platform))
							<br/><br/>
							&horbar; {!! $bug->customer->platform->platform !!} Device
							@endif
							
						@endif
					</td>
				</tr>
						
				</table>
		
			
			@if($bug->created_at >= '2020-03-04 22:00:00')
			<hr/>
				
				@if(count($sms_responses) == 0)
					<center> This has received no response. </center>
				@else
				<h4> SMS Responses ({!! count($sms_responses) !!})</h4>
				@foreach($sms_responses as $l => $r) 
					<div style="background: #e1f8ff;margin-bottom: 1%;padding:1%;border-radius:5px;" class="row">
						<div class="span12">
							{!! $r->message !!}<br/><br/>
							&horbar; <i>{!! $r->date_time !!} ({!! Carbon\Carbon::parse($r->date_time)->diffForHumans() !!})</i><br/>
						</div>
					</div>
				@endforeach
				@endif
			@endif
			
			<hr/>
			
			@if($bug->customer)
			<form action="{!! URL::to('bug/reports/reply', ['id' => $bug->id]) !!}" method='POST'>
		
			<table width='100%'>
				<tr>
					<td colspan='2'><h3>SMS Reply</h3></td>
				</tr>
				<tr>
					<div class="row">
						 <div class="span8">
						 
							
							<div class="row">
								<div class="span12">
								<br/><br/>
								<input type="checkbox" checked name='charge' id='premium' value='premium' style='height: 21px; width: 21px;'>
								<label for="premium" style='font-size:0.9rem;display:inline-block;'>Charge Premium (&euro;.0.50)</label>
								</div>
							</div>
							
							<div class="row">
								<div class="span12">
								<input type="checkbox" name='charge' id='normal' value='normal' style='height: 21px; width: 21px;'>
								<label for="normal" style='font-size:0.9rem;display:inline-block;'>Charge (&euro;0.12)</label>
								</div>
							</div>
							
						 </div>
						 <div class="span4">
						 	<div class="row">
								<div class="span12">
									  <button type="button" name="sms_details" id="sms_details" class="btn btn-primary " />1. Fill details preset</button>
										<br/><br/>
								</div>
							</div>
							<div class="row">
								<div class="span12">
									<input type="checkbox" checked="true" name="reset_pw" id="reset_pw" style='height: 21px; width: 21px;'>
									<label for="normal" style='font-size:0.9rem;display:inline-block;'>Reset password</label>
								</div>
							</div>
							<div class="row">
								<div class="span12">
									<input type="checkbox" checked="true" name="sms_web" id="sms_web_link" style='height: 21px; width: 21px;'>
									<label for="normal" style='font-size:0.9rem;display:inline-block;'>Include Website link</label>
								</div>
							</div>
						 </div>
					</div>
				</tr>
				<tr>
					<td width="100%">
						<a href='/settings/sms_presets'><i class='fa fa-pencil-alt'></i>&nbsp;Edit Presets</a><br/><br/>
						<select class='categories' name='categories'>
							<option value='0'> -- Select Preset Category -- </option>
							@foreach(SMSMessagePreset::categories() as $k => $categories)
								<option>{!! $categories->category !!}</option>
							@endforeach
						</select>
						&nbsp;
						<select style='width:70%;display:none;' class='presets' name='presets'>
							
						</select>
					</td>
				</tr>
				
				
				
				<tr>
					<td>
						<textarea id="reply" class='reply' required style='width:100%;' rows='5' placeholder='Your reply will automatically be sent as an SMS.' name="reply"></textarea>
					</td>
				</tr>
				<tr>
					<td>
						<button style='width:100%;padding-top:2%;padding-bottom:2%;' type='submit' class='btn btn-primary'>Submit</button>
					</td>
				</tr>
			
			</table>
			</form>
			@endif
	
		
		<br/><br/>
		

</div>

<div class="admin2">
	
</div>

@if($bug->customer)
{!!HTML::script('resources/js/util/fill_details_preset.js?21')!!}
<script>
	$(function(){
		
		var loaded_presets = [];
		
		$('.categories').on('change', function(){
			
			$('.reply').val('');
				
			var category = $(this).val();
			
			if(category.indexOf('Select Preset') != -1) {
				$('.presets').hide();
			} else {
				$('.presets').show();
			}
			
			$.ajax({
				
				url: '/bug/reports/get_presets', 
				type: 'POST',
				data: { category: category, customer_id: {!! $bug->customer->id !!}  },
				success: function(data){
					loaded_presets = data.presets;
					generatePresets();
				}
			});
			
		});
		
		$('.presets').on('change', function(){
			
			var val = $(this).val();
			if(val.indexOf('Select Preset') != -1) return;
			
			val = "Hi " + '{!! $bug->customer->first_name !!},' + "\n\n" + val;
			val = val + "\n\nThis SMS is NOREPLY - Responses to this SMS will not be delivered.\n\n";
			val = val + "Kind Regards\nSnugZone";
			$('.reply').val(val);
			
			
		});
		
		function generatePresets()
		{
			var presets = $('.presets');
			presets.html('');
			var append = '';
			presets.append($('<option>', { 
					value: ' -- Select Preset Response -- ',
					text : ' -- Select Preset Response -- ',
			}));
/// jquerify the DOM object 'o' so we can use the html method
			$.each(loaded_presets, function(k, v){
				var o = new Option(v.body, v.body);
				presets.append($('<option>', { 
					value: v.body,
					text : v.body,
				}));
			});
		
		}
		
	});
</script>
@endif



@else
	<br/><br/>
		<h2>Access Denied.</h2>
	<br/><br/>
@endif