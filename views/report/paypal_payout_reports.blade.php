</div>

<div><br/></div>
<h1>Paypal Payout reports</h1>
<div style="float: right">

    <form method="get" action="<?php echo URL::to('system_reports/paypal_payout_reports') ?>"class="form-inline" style="float:left">
        
		
		<label>From</label>
        <input autocomplete="off" id="from" name="from" @if((Input::get('from')))value="{{Input::get('from')}}"@else value="{{$default_from}}" @endif type="text">
        <label>To</label>
        <input autocomplete="off" id="to" name="to" @if((Input::get('to')))value="{{Input::get('to')}}"@else value="{{$default_to}}" @endif type="text">
        <input type="submit" value="search" class="btn-success"/>
		
		
    </form>

</div>

<div class="admin2">


    <a href="{{ URL::to('system_reports') }}">System Reports</a> > Paypal Payout reports
    <h3><a href="">
	<form method="get" action="{{ $csvURL }}" id="csv-form">
		<input type="hidden" name="from" value="{{$from_dt->format('Y-m-d H:i:s')}}">
		<input type="hidden" name="to" value="{{$to_dt->format('Y-m-d H:i:s')}}">
        <a href="javascript:;" id="csv-url">Download CSV</a>
    </form>
	</a></h3>
	
	<table class="table table-bordered">
        
		
		<tr>
			<td width="40%"><b>Most popular day</b></td><td>{{$mostPopularDay}}</td>
		</tr>
		<tr>
			<td width="40%"><b>Range</b></td><td>{{$from_dt->format('d-m-Y')}} - {{$to_dt->format('d-m-Y')}}</td>
		</tr>
		<tr>
			<td width="40%"><b>No. of months</b></td><td>{{$no_months}}</td>
		</tr>
		<tr>
			<td width="40%"><b>Total no. of topups</b></td><td>{{$total_topups}}</td>
		</tr>
		<tr>
			<td width="40%"><b>Total &euro; of topups</b></td><td>&euro;{{number_format($total_amount, 2)}}</td>
		</tr>
		
		
		
	</table>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Day</th>
                <th>No. Topups</th>
                <th>&euro; Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($day_of_weeks as $key=>$day)
                <tr>
					<td>{{$key}}</td>
					<td>{{$day['total_no']}}</td>
					<td>&euro;{{number_format($day['amount'], 2)}}</td>
				</tr>
            @endforeach
        </tbody>
    </table>

</div>

<script type="text/javascript">
$(function(){
	
	var date_settings = {
            dateFormat: 'dd-mm-yy',
		};
	
	$( "#from" ).datepicker(date_settings);
	$( "#to" ).datepicker(date_settings);
	
	
	$("#csv-url").click(function(){
        $("#csv-form").submit();
    });
	
});
</script>