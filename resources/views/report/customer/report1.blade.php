
@if(!$customer || !$customer->districtMeter || !$customer->permanentMeter)
	<h2>Customer not found / no meter / permanent meter associated with customer.</h2>
	<h4>Unable to generate report</h4>
@else
	
</div>

<div><br/></div>
<h1>Customer report Type 1: {!! $customer->username !!} - #{!! $customer->id !!}</h1>




<div class="admin2">
	
	@if(!isset($_GET['summary']))
	<table class="table table-bordered" style="font-size:16px;" width="100%">
		
		<tr>
			<td width="20%">
				<b>Range</b>
			</td>
			<td width="50%">
				28-02-2018 - 28-02-2019 (365 days)
			</td>
		</tr>
	
		<tr>
			<td width="20%">
				<b>Username</b>
			</td>
			<td width="50%">
				{!! $customer->username !!}
			</td>
		</tr>
	
		<tr>
			<td width="20%">
				<b>ID</b>
			</td>
			<td width="50%">
				<a href="/customer/{!! $customer->id !!}">{!! $customer->id !!}</a>
			</td>
		</tr>
		
		
		@if(isset($_GET['extra']))
		<tr>
			<td width="20%">
				<b>Meter number</b>
			</td>
			<td width="50%">
				{!! $customer->districtMeter->meter_number !!}
			</td>
		</tr>
		
		<tr>
			<td width="20%">
				<b>Current reading</b>
			</td>
			<td width="50%">
				{!! $customer->districtMeter->latest_reading !!} kWh
			</td>
		</tr>
		
			
		<tr>
			<td width="20%">
				<b>Abnormal days</b>
			</td>
			<td style='background:red;' width="50%">
				{!! $abnormal_days !!}
			</td>
		</tr>
			
		<tr>
		<td width="20%">
			<b>Unexpected start readings</b>
		</td>
		<td style='background:red;' width="50%">
			{!! $unexpected_start_readings !!}
		</td>
		</tr>
			
		@endif
	
	</table>
		
		
	<table class="table table-bordered" style="font-size:16px;" width="100%">
	
		<tr>
			<td colspan='4'>
				<b>a. Daily usage (kWh) & cost (&euro;)</b>
			</td>
		</tr>
		<tr>
			<th><b>Date</b></th>
			<th><b>Start reading</b></th>
			<th><b>End reading</b></th>
			<th><b>Cost of day</b></th>
			@if(isset($_GET['extra']))
				<th><b>Expected</b></th>
				<th><b>E_Start</b></th>
			@endif
		</tr>
		
		@foreach($usage365 as $u)
		<tr>
			<td>{!! $u->date !!}</td>
			<td>{!! $u->start_day_reading !!}</td>
			<td>{!! $u->end_day_reading !!}</td>
			<td>{!! $u->cost_of_day !!}</td>
			@if(isset($_GET['extra']))
				<td @if(!$u->normal) style='background:red;' @endif>
				{!! $u->expected_cod !!}
				</td>
				<td @if(!$u->normal) style='background:red;' @endif>
				{!! $u->e_start !!}
				</td>
			@endif
		</tr>
		@endforeach
		
	</table>

	<table class="table table-bordered" style="font-size:16px;" width="100%">

	<tr>
		<td width="50%"><b>b. Total usage (kWh)</b></td>
		<td width="50%">{!! $total_usage !!}
	</tr>
	
	</table>
	
	<table class="table table-bordered" style="font-size:16px;" width="100%">

	<tr>
		<td width="50%"><b>c. Total cost (&euro;)</b></td>
		<td width="50%">&euro;{!! number_format($total_cost,5) !!}
	</tr>
	
	</table>
	
	<table class="table table-bordered" style="font-size:16px;" width="100%">

	<tr>
		<td width="50%"><b>d. Avg daily usage (kWh)</b></td>
		<td width="50%">&euro;{!! number_format($avg_daily_usage, 5) !!}
	</tr>
	
	</table>
	
	<table class="table table-bordered" style="font-size:16px;" width="100%">

	<tr>
		<td width="50%"><b>e. Avg daily cost (&euro;)</b></td>
		<td width="50%">&euro;{!! number_format($avg_daily_cost,5) !!}
	</tr>
	
	</table>
	
	<table class="table table-bordered" style="font-size:16px;" width="100%">

	<tr>
		<td colspan='3' width="50%"><b>f. Peak day</b></td>
	</tr>
	@if($peak_day != null)
	<tr>
		<th><b>Date</b></td>
		<th><b>Usage (kWh)</b></td>
		<th><b>Cost (&euro;)</b></td>
	</tr>
	<tr>
		<td>{!! $peak_day->date !!}</td>
		<td>{!! $peak_day->total_usage !!}</td>
		<td>&euro;{!! $peak_day->cost_of_day !!}</td>
	</tr>
	@else
		<tr>
		<td colspan='3' width="50%"><b>	No peak day values for 28-02-2018 - 28-02-2019</b></td>
		</tr>	
	@endif
	
	
	</table>

<table class="table table-bordered" style="font-size:16px;" width="100%">
	
	<tr>
		<td colspan='3' width="50%"><b>g&h. Monthly usage avg</b></td>
	</tr>	
	@if(count($month_totals) > 0)
	<tr>
		<td width="33.3%"><b>Month</b></td>
		<td width="33.3%"><b>Avg Daily Usage (kWh)</b></td>
		<td width="33.3%"><b>Avg Daily Cost (&euro;)</b></td>
	</tr>
	@foreach($month_totals as $key => $m)
	<tr>
		<td>{!! $m['month'] !!}</td>
		<td>{!! number_format($m['usage']/$m['days'],0) !!}</td>
		<td>&euro;{!! number_format($m['cost']/$m['days'], 5) !!}</td>
	</tr>
	@endforeach
	@else
		<tr>
		<td colspan='3' width="50%"><b>	No month values for 28-02-2018 - 28-02-2019</b></td>
		</tr>	
	@endif


<table class="table table-bordered" style="font-size:16px;" width="100%">
	<tr>
		<td colspan='3' width="50%"><b>i. Payments made</b></td>
	</tr>
	<tr>
		<td width="50%"><b>Transaction ID</b></td>
		<td width="30%"><b>Date</b></td>
		<td width="20%"><b>Amount</b></td>
	</tr>
	@foreach($payments as $p)
	<tr>
		<td width="50%">{!! $p->ref_number !!}</td>
		<td width="30%">{!! $p->time_date  !!}</td>
		<td width="20%">&euro;{!! $p->amount !!}</td>
	</tr>
	@endforeach
	<tr>
		<td width="50%" colspan='2'><b>Total (&euro;)</b></td><td>&euro;{!! $payments->sum('amount') !!}</td>
	</tr>
</table>
@else
<h1> {!! $customer->username !!} </h1>
<table class='table table-bordered' width="100%">
	<tr><td width="50%"><h3><b>b. Total usage (kWh)</b></h3></td><td width="50%"><h3>{!! $total_usage !!}</h3></td></tr>
	<tr><td width="50%"><h3><b>c. Total cost (&euro;)</b></h3></td><td width="50%"><h3>&euro;{!! $total_cost !!}</h3></td></tr>
	<tr><td width="50%"><h3><b>d. Avg daily usage (kWh)</b></h3></td><td width="50%"><h3>{!! number_format($avg_daily_usage, 5) !!}</h3></td></tr>
	<tr><td width="50%"><h3><b>e. Avg daily cost (&euro;)</b></h3></td><td width="50%"><h3>&euro;{!! number_format($avg_daily_cost,5) !!}</h3></td></tr>
	<tr><td width="50%"><h3><b>f. Peak day</b></h3></td><td width="50%"></td></tr>
	<tr>
		<td colspan='2' width="100%">
		@if($peak_day != null)
		<table class='table table-bordered' width="100%">
			<tr><td><b><h4>{!! $peak_day->date !!}</h4></b></td></tr>
			<tr><td><b><h4>{!! $peak_day->total_usage !!} kWh</h4></b></td></tr>
			<tr><td><b><h4>&euro;{!! $peak_day->cost_of_day !!}</h4></b></td></tr>
		</table>
		@else
			Peak day not available
		@endif
		</td>
	</tr>
	<tr><td width="50%"><h3><b>g. Monthly avg usage</b></h3></td><td width="50%">
	</td></tr>
	<tr>
		<td colspan='2' width="100%">
		@if(!empty($month_totals))
		<table class='table table-bordered' width="100%">
			@foreach($month_totals as $key => $m)
			<tr>
				<td>{!! $m['month'] !!}</td>
				<td>{!! number_format($m['usage']/$m['days'],0) !!} kWh</td>
				<td>&euro;{!! number_format($m['cost']/$m['days'], 5) !!}</td>
			</tr>
			@endforeach
		</table>
		@else
			No month totals not available
		@endif
		</td>
	</tr>
</table>
@endif
</div>
@endif
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.2/Chart.bundle.js"></script>
