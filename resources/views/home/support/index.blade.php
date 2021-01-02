<br />
<div class="cl"></div>
<h1>Support</h1>

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
<script>

$(document).ready(function() {
    $('#datatable').DataTable({
		
		 "order": [[ 0, "desc" ]]
		
	});
} );
</script>
			<table id="table1" class="table table-bordered">
				
				<thead>
					<tr>				
						<th width='5%'><b><center>#</center></b></th>
						<th width='15%'><b>Created</b></th>
						<th width='25%'><b>Issue</b></th>
						<th width='10%'><b>Operator</b></th>
						<th width='10%'><b>Customer</b></th>
						<th width='20%'><b>Manage</b></th>
					</tr>
				</thead>
				
				<tbody>
				@foreach($un_viewed as $u)
					<tr>
						<td><center>{!!$u->id!!}</center></td>
						<td>{!! Carbon\Carbon::parse($u->created_at)->diffForHumans() !!}</td>
						<td>{!!$u->issue_title!!}</td>
						<td>{!!$u->operator!!}</td>
						<td>{!!$u->customer!!}</td>
						<td {!! $u->statusCss(true) !!}>
						<center>
							<a href="{!! URL::to('support/view', ['id' => $u->id]) !!}"><button type='button' class='btn btn-warning'><i class='fa fa-wrench'></i> Start</button></a>
							<a href="{!! URL::to('support/mark_solved', ['id' => $u->id]) !!}"><button type='button' class='btn btn-success'><i class='fa fa-check'></i> Solved</button></a>
						</center>
						</td>
					</tr>
				@endforeach
				</tbody>
				
			</table>
	
	<table class="table table-bordered">
				
				<thead>
					<tr>				
						<th width='5%'><b><center>#</center></b></th>
						<th width='15%'><b>Created</b></th>
						<th width='25%'><b>Issue</b></th>
						<th width='10%'><b>Operator</b></th>
						<th width='10%'><b>Customer</b></th>
						<th width='20%'><b>Manage</b></th>
					</tr>
				</thead>
				
				<tbody>
				@foreach($started as $u)
					<tr>
						<td><center>{!!$u->id!!}</center></td>
						<td>{!! Carbon\Carbon::parse($u->created_at)->diffForHumans() !!}</td>
						<td>{!!$u->issue_title!!}</td>
						<td>{!!$u->operator!!}</td>
						<td>{!!$u->customer!!}</td>
						<td {!! $u->statusCss(true) !!}>
						<center>
							<a href="{!! URL::to('support/view', ['id' => $u->id]) !!}"><button type='button' class='btn btn-primary'><i class='fa fa-eye'></i> View</button></a>
							<a href="{!! URL::to('support/mark_solved', ['id' => $u->id]) !!}"><button type='button' class='btn btn-success'><i class='fa fa-check'></i> Solved
						</center>
						</td>
					</tr>
				@endforeach
				</tbody>
				
			</table>
		<hr>	
				<table id="datatable" class="table table-bordered">
				
				<thead>
					<tr>
						<th width='5%'><b><center>#</center></b></th>
						<th width='10%'><b>Created</b></th>
						<th width='20%'><b>Issue</b></th>
						<th width='10%'><b>Operator</b></th>
						<th width='10%'><b>Customer</b></th>
						<th width='15%'><b>Status</b></th>
					</tr>
				</thead>
				
				<tbody>
				@foreach($resolved as $u)
					<tr>
						<td><center>{!! $u->id !!}</center></td>
						<td>{!! $u->created_at !!}</td>
						<td><a href="{!! URL::to('support/view', ['id' => $u->id]) !!}">{!!$u->issue_title!!}</a></td>
						<td>{!!$u->operator!!}</td>
						<td>{!!$u->customer!!}</td>
						<td {!! $u->statusCss(true) !!} colspan='2'><center style='color:white;'>Solved</center></td>
					</tr>
				@endforeach
				</tbody>
				
			</table>
</div>

<div class="admin2">
	
</div>
