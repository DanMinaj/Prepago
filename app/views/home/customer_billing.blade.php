
</div>

<div><br/></div>
<h1>Manage Billing: Customer {{ $customer->id }} - {{ $customer->username }}</h1>

<div style='background:white;padding:0.50%;border-radius:3px;border:1px solid #ccc;width:20%;text-align:center;'>
<b>Current Balance: </b> &euro;{{ $customer->balance }}
</div>

<div class="admin2">
	
	@include('includes.notifications')
 
   <ul class="nav nav-tabs" style="margin: 30px 0">
    
      <li class="active"><a href="#1" data-toggle="tab">Charges</a></li>
      <li><a href="#2" data-toggle="tab">Flags ({{ count($flags) }})</a></li>
      <li><a href="#3" data-toggle="tab">Logs</a></li>
	  
   </ul>
	
 <div class="tab-content">
	
	<!-- Tab 1 -->
	<div class="tab-pane active" id="1" style="text-align: left">	
	
	<form action="{{ URL::to('billing/' . $customer->id . '') }}">
		<table width="100%">
			<tr>
				<td>From</td>
				<td>To</td>
			</tr>
			<tr>
				<td style="vertical-align:top;" width="7%">
					<input type="text" placeholder="From" name="from" value="{{ $from }}"/>
				</td>
				<td style="vertical-align:top;" width="7%">
					<input type="text" placeholder="To" name="to" value="{{ $to }}"/>
				</td>
				<td style="vertical-align:top;" width="40%">
					<input type="submit" class="btn btn-primary" value="Submit">
				</td>
				<td width="30%">
					<a href="{{ URL::to('billing/' . $customer->id . '/download') }}"><button type="button" class="btn btn-info"><i class="fa fa-file-archive"></i> Download Customer Folder</button></a>
				</td>
			</tr>
		</table>
	</form>
	
	@foreach($logs as $k => $l)
		
			<table width="100%" class="table table-bordered">
				<tr>
					<th style='vertical-align:middle;color: white;background: #373737;' colspan='3'>
						<span style='font-weight:bold;font-size:15px;'>{{ $l->date }}</span>
						 <span style='float:right'>
							&euro;{{$l->charge_total}} | {{$l->total_usage}} kWh | {{(int)$l->start_day_reading}}&gt;{{(int)$l->end_day_reading}} kWh
						 </span>
						 
						<!--
						@if($l->charge_total > 0.00)
							<form style="display:inline-block;margin:0px;float:right;" action="{{ URL::to('billing/' . $customer->id . '/refund_all') }}" method="POST">
								<input type="hidden" value="{{ $l->date }}" name="log_date"/>
								<button type="submit" class="btn btn-success">Refund Day &euro;{{$l->charge_total}}</button>
							</form>	
						@endif
						-->
					</th>
				</tr>
				@if(!$l->entry_exists)
					<tr>
						<td>Cannot manage billing for this date: No entries.</td>
					</tr>
				@elseif(count($l->charges) <= 0)
					<tr>
						<td>Cannot manage billing for this date: No charges.</td>
					</tr>
				@endif
			</table>
			@if(!$l->entry_exists)<!--
				<table width="100%" class="table table-bordered">
					<tr>
						<td>No entries exist for this date.</td>
					</tr>
				</table>
				-->
			@else
				
			 <button class="btn btn-primary" style="width:100%" type="button" data-toggle="collapse" data-target="#charges-{{ $l->date }}" aria-expanded="false" aria-controls="charges-{{ $l->date }}">
				Show charges
			 </button>
			 
				<div class="collapse" id="charges-{{ $l->date }}">
				<br/>
				@foreach($l->charges as $key => $c)
				<table width="100%" class="table table-bordered">
					
					<!--<tr>
						<td>
							<center>#<b>{{ $key+1 }}</b></center>
						</td>
						<td colspan='2' style="vertical-align: middle;"><b>{{ $c->time }}</b></td>
					</tr>-->
					
					@if($c->type == 'normal')
					<!--
					<tr>
						<td><b>District Heating Usage ID:</b></td> <td colspan='2'>{{ $c->dhu->id }}</td>
					</tr>
					-->
					<tr>
						<td><b>Usage:</b></td> <td colspan='2'>{{ $c->usage }} kWh <span style='float:right'>{{ $c->time }}</span></td>
					</tr>
					<tr>
						<td><b>Billed:</b></td> <td colspan='2'>&euro;{{ $c->billed }}</td>
					</tr>
					<tr>
						<td><b>Sudo:</b></td> <td colspan='2'>{{ $c->sudo_reading }} kWh</td>
					</tr>
					<tr>
						<td><b>Latest:</b></td> <td colspan='2'>{{ $c->latest_reading }} kWh</td>
					</tr>
					@else
					<tr>
						<td colspan='3' style='color:#5bc0de'><b>Standing charge</b><span style='float:right;'><b>{{ $c->time }}</b></span></td>
					</tr>
					<tr>
						<td><b>District Heating Usage ID:</b></td> <td colspan='2'>{{ $c->dhu->id }}</td>
					</tr>
					@endif
				
					<tr>
						<td width="30%" style="vertical-align:middle;">
							<b>Balance change:</b>
						</td> 
						<td width="50%"style="vertical-align:middle;" colspan='1'>
							&euro;{{ $c->balance_before }} -> &euro;{{ $c->balance_after }}
						</td>
						
						<td>
							@if($c->status == 'applied')
								<form style="margin:0px;" action="{{ URL::to('billing/' . $customer->id . '/refund') }}" method="POST">
									<input type="hidden" value="{{ $l->date }}" name="date"/>
									<input type="hidden" value="{{ $key }}" name="charge_id"/>
									<button type="submit" class="btn btn-success"><i class="fa fa-undo"></i> Refund &euro;{{$c->billed}}</button>
								</form>
							@else
								<form style="margin:0px;" action="{{ URL::to('billing/' . $customer->id . '/charge') }}" method="POST">
									<input type="hidden" value="{{ $l->date }}" name="date"/>
									<input type="hidden" value="{{ $key }}" name="charge_id"/>
									<button type="submit" class="btn btn-danger"><i class="fa fa-caret-square-up"></i> Charge &euro;{{$c->billed}}</button>
								</form>
							@endif
						</td>
					</tr>
					
				</table>
				@endforeach
				</div>
				
			@endif
	
	
		<hr/>
	@endforeach
	
	</div>

	<!-- Tab 2 -->
	<div class="tab-pane" id="2" style="text-align: left">	
		
		@if($after = Session::get("after"))
			
			<?php $before = Session::get("before"); ?>
			<h4> The following changes were applied: </h4>
			<table width="100%">
				
				<tr>
					<th><b><i>Before</i></b></th>
					<th><b><i>After</i></b></th>
				</tr>
				<tr>
					
					<!-- Before -->
					<td width="50%">
						<table width="100%" class="table">
							<tr>
								<td width="10%"><b>district_heating_usage.id</b></td>
								<td width="30%">{{ $before['dhu_id'] }}</td>
							</tr>
							<tr>
								<td width="10%"><b>customers.balance</b></td>
								<td width="30%">{{ $before['balance'] }}</td>
							</tr>
							<tr>
								<td width="10%"><b>customers.used_today</b></td>
								<td width="30%">{{ $before['used_today'] }}</td>
							</tr>
							<tr>
								<td width="10%"><b>district_heating_meters.sudo_reading</b></td>
								<td width="30%">{{ $before['sudo_reading'] }}</td>
							</tr>
							<tr>
								<td width="10%"><b>district_heating_meters.latest_reading</b></td>
								<td width="30%">{{ $before['latest_reading'] }}</td>
							</tr>
							<tr>
								<td width="10%"><b>district_heating_usage.cost_of_day</b></td>
								<td width="30%">{{ $before['cost_of_day'] }}</td>
							</tr>
							<tr>
								<td width="10%"><b>district_heating_usage.unit_charge</b></td>
								<td width="30%">{{ $before['unit_charge'] }}</td>
							</tr>
							<tr>
								<td width="10%"><b>district_heating_usage.total_usage</b></td>
								<td width="30%">{{ $before['total_usage'] }}</td>
							</tr>
							<tr>
								<td width="10%"><b>district_heating_usage.end_day_reading</b></td>
								<td width="30%">{{ $before['end_day_reading'] }}</td>
							</tr>
						</table>
					</td>
					
					<!-- After -->
					<td width="50%">
						<table width="100%" class="table">
							<tr>
								<td width="10%"><b>district_heating_usage.id</b></td>
								<td width="30%">{{ $after['dhu_id'] }}</td>
							</tr>
							<tr>
								<td width="10%"><b>customers.balance</b></td>
								<td width="30%">{{ $after['balance'] }}</td>
							</tr>
							<tr>
								<td width="10%"><b>customers.used_today</b></td>
								<td width="30%">{{ $after['used_today'] }}</td>
							</tr>
							<tr>
								<td width="10%"><b>district_heating_meters.sudo_reading</b></td>
								<td width="30%">{{ $after['sudo_reading'] }}</td>
							</tr>
							<tr>
								<td width="10%"><b>district_heating_meters.latest_reading</b></td>
								<td width="30%">{{ $after['latest_reading'] }}</td>
							</tr>
							<tr>
								<td width="10%"><b>district_heating_usage.cost_of_day</b></td>
								<td width="30%">{{ $after['cost_of_day'] }}</td>
							</tr>
							<tr>
								<td width="10%"><b>district_heating_usage.unit_charge</b></td>
								<td width="30%">{{ $after['unit_charge'] }}</td>
							</tr>
							<tr>
								<td width="10%"><b>district_heating_usage.total_usage</b></td>
								<td width="30%">{{ $after['total_usage'] }}</td>
							</tr>
							<tr>
								<td width="10%"><b>district_heating_usage.end_day_reading</b></td>
								<td width="30%">{{ $after['end_day_reading'] }}</td>
							</tr>
						</table>
					</td>
					
					
				</tr>
			
			</table>
		@endif
		
		@foreach($flags as $f)
		
			
			<table width="100%" class="table table-bordered">
				
				<tr>
					<td width="10%"><b>Flagged at</b></td>
					<td width="30%">{{ $f->created_at }}</td>
				</tr>
				
				<tr>
					<td width="10%"><b>Usage to approve</b></td>
					<td width="30%">{{ $f->kwh_usage }} kWh</td>
				</tr>
			
				<tr>
					<td width="10%"><b>Bill to approve</b></td>
					<td width="30%">&euro;{{ $f->amount }}</td>
				</tr>
				
				<tr>
					<td width="10%"><b>Flag sudo_reading</b></td>
					<td width="30%">{{ $f->sudo_reading }}</td>
				</tr>
				
				<tr>
					<td width="10%"><b>Flag latest_reading</b></td>
					<td width="30%">{{ $f->latest_reading }}</td>
				</tr>
				
				@if($f->approved)
					<tr>
					<td width="10%"><b>Approved at </b></td>
					<td width="30%">{{ $f->updated_at }}</td>
				</tr>
				<tr>
					<td width="10%"><b>Applied to DHU </b></td>
					<td width="30%"><a target="_blank" href="{{ URL::to('edit_dhu/' . $f->applied_to) }}">#{{ $f->applied_to }}</a></td>
				</tr>
				@endif
				
				
				<style>
					.dtd {
						text-align: center !important;
						color: white;
					}
					.dtd.accepted{
						background: #62c462;
					}
					.dtd.declined{
						background: #ee5f5b;
					}
					.dtd.pending{
						background: #fbb450;
					}
				</style>
				
				<tr>
					@if($f->approved)
							
						<td colspan='2' class="dtd accepted">
							Approved
						</td>
					
					@elseif($f->declined)
					
						<td colspan='2' class="dtd declined">
							Declined
						</td>
						
					@else
						
						<td colspan='2' class="dtd pending">
							Declined
						</td>
						
					@endif
				</tr>
				
				
			</table>
		
		@endforeach
	
	</div>
	
	<!-- Tab 3 -->
	<div class="tab-pane active" id="3" style="text-align: left">	
	
	@foreach($db_logs as $log)
		
		<table width="100%" class="table table-bordered">
			
		<tr>
			<td> <b> Type </b> </td>
			<td> <i>{{ $log->type }}</i> </td>
		</tr>
		
		<tr>
			<td> <b> Operator </b> </td>
			<td> {{ User::find($log->operator_id)->username }} </td>
		</tr>
		
		<tr>
			<td> <b> Details </b> </td>
			<td> {{ $log->message }} </td>
		</tr>
		
		</table>
		
	@endforeach
	
	</div>
	
</div>

</div>

	