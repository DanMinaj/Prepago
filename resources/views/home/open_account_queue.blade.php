</div>
<div class="cl"></div>
<h1>Open Account Queue
</h1>
<div class="admin2">
	
	@if (Session::has('successMsg') && Session::get('successMsg'))
		<div class="alert alert-success">{!! Session::get('successMsg') !!}</div>
	@endif
    @include('includes.notifications')
	
	
	<hr/>
	<h2>Currently in queue ({!! count($in_queue) !!})</h2>
	<hr/>
   <table class="table table-bordered tablesorter sortthistable">
		
		<thead>
			<th width="20%">Full Name</th>
			<th width="15%">Username</th>
			<th width="20%">Email Address</th>
			<th width="20%">Mobile Number</th>	
			<th width="25%">&nbsp;</th>
		</thead>
		
		<tbody>
			@foreach($in_queue as $k => $v)
				<tr>
					<td> {!! $v->first_name !!} {!! $v->surname !!} </td>
					<td> {!! $v->username !!} </td>
					<td> {!! $v->email_address !!} </td>
					<td> {!! $v->mobile_number !!} </td>
					<td>
						<a href="/open_account/queue/action?action=run&q={!! $v->id !!}"><button class="btn btn-success">Force run</button></a>
						<a href="/open_account/queue/action?action=cancel&q={!! $v->id !!}"><button class="btn btn-danger">Cancel</button></a>
					</td>
				</tr>
			@endforeach
		</tbody>
        
    </table>
	
	<hr/>
	<h2>Completed ({!! count($finished_queue) !!})</h2>
	<hr/>
	<table class="table table-bordered tablesorter sortthistable">
		
		<thead>
			<th width="20%">Full Name</th>
			<th width="15%">Username</th>
			<th width="20%">Email Address</th>
			<th width="20%">Mobile Number</th>	
			<th width="25%">&nbsp;</th>
		</thead>
		
		<tbody>
			@foreach($finished_queue as $k => $v)
				<tr>
					<td> {!! $v->first_name !!} {!! $v->surname !!} </td>
					<td> {!! $v->username !!} </td>
					<td> {!! $v->email_address !!} </td>
					<td> {!! $v->mobile_number !!} </td>
					<td>
						<a href="/open_account/queue/action?action=undo&q={!! $v->id !!}"
						onclick="return confirm('Are you sure? You will be deleting the customer created from this queue!');"><button class="btn btn-warning">Undo</button></a>
					</td>
				</tr>
			@endforeach
		</tbody>
        
    </table>
	
	<hr/>
	<h2>Failed ({!! count($failed_queue) !!})</h2>
	<hr/>
	<table class="table table-bordered tablesorter sortthistable">
		
		<thead>
			<th width="20%">Full Name</th>
			<th width="15%">Username</th>
			<th width="20%">Email Address</th>
			<th width="20%">Mobile Number</th>	
			<th width="25%">&nbsp;</th>
		</thead>
		
		<tbody>
			@foreach($failed_queue as $k => $v)
				<tr>
					<td> {!! $v->first_name !!} {!! $v->surname !!} </td>
					<td> {!! $v->username !!} </td>
					<td> {!! $v->email_address !!} </td>
					<td> {!! $v->mobile_number !!} </td>
					<td>
						<a href="/open_account/queue/action?action=redo&q={!! $v->id !!}"><button class="btn btn-primary">re-Queue</button></a>
					</td>
				</tr>
			@endforeach
		</tbody>
        
    </table>


</div>
<script>
		$(document).ready(function() {
			$(function() {
				$(".sortthistable").tablesorter({
					
					headers: {
					  1: { sorter: "digit", empty : "top" }, // sort empty cells to the top
					  2: { sorter: "digit", string: "max" }, // non-numeric content is treated as a MAX value
					  3: { sorter: "digit", string: "min" }  // non-numeric content is treated as a MIN value
					},
					sortList: [[1, 1]]
					
				});
			});
		});
</script>