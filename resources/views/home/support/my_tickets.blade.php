<br />
<div class="cl"></div>
<h1>Your Support Issues</h1>

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


			<table class="table table-bordered">
				
				
				<tr>
					<th width='20%'><b><center></center></b></th>
					<th width='20%'><b>Issue</b></th>
					<th width='10%'><b>Customer</b></th>
					<th width='15%'><b>Status</b></th>
					<th width='20%'><b>View</b></th>
				</tr>
				
				@foreach($un_viewed as $u)
					<tr>
					<?php
						 $d = new DateTime($u->created_at); 
					?>
						<td>{!! $d->format('F j H:i:s') !!}</td>
						<td>{!!$u->issue_title!!}</td>
						<td>{!!$u->customer!!}</td>
						<td {!! $u->statusCss(true) !!} ><center>None</center></td>
						<td>
						<center>
							<a href="{!! URL::to('support/view', ['id' => $u->id]) !!}"><button type='button' class='btn btn-primary'><i class='fa fa-check'></i> View</button></a>
							<a href="{!! URL::to('support/mark_solved', ['id' => $u->id]) !!}"><button type='button' class='btn btn-success'><i class='fa fa-check'></i> Mark as resolved</button></a>
						</center>
						</td>
					</tr>
				@endforeach
				
			</table>
	
	<table class="table table-bordered">
				
				
				<tr>				
					<th width='20%'><b><center></center></b></th>
					<th width='20%'><b>Issue</b></th>
					<th width='10%'><b>Customer</b></th>
					<th width='15%'><b>Status</b></th>
					<th width='20%'><b>View</b></th>
				</tr>
				
				@foreach($started as $u)
					<tr>
					<?php
						 $d = new DateTime($u->created_at); 
					?>
						<td>{!! $d->format('F j H:i:s') !!}</td>
						<td>{!!$u->issue_title!!}</td>
						<td>{!!$u->customer!!}</td>
						<td {!! $u->statusCss(true) !!} ><center>Started</center></td>
						<td>
						<center>
							<a href="{!! URL::to('support/view', ['id' => $u->id]) !!}"><button type='button' class='btn btn-primary'><i class='fa fa-check'></i> View</button></a>
							<a href="{!! URL::to('support/mark_solved', ['id' => $u->id]) !!}"><button type='button' class='btn btn-success'><i class='fa fa-check'></i> Mark as resolved</button></a>
						</center>
						</td>
					</tr>
				@endforeach
				
			</table>
			
				<table class="table table-bordered">
				
				<tr>
					<th width='20%'><b><center></center></b></th>
					<th width='20%'><b>Issue</b></th>
					<th width='10%'><b>Customer</b></th>
					<th width='15%'><b>Status</b></th>
					<th width='20%'><b>View</b></th>
				</tr>
				
				
				@foreach($resolved as $u)
					<tr>
					<?php
						 $d = new DateTime($u->created_at); 
					?>
						<td>{!! $d->format('F j H:i:s') !!}</td>
						<td>{!!$u->issue_title!!}</td>
						<td>{!!$u->customer!!}</td>
						<td {!! $u->statusCss(true) !!}><center>Resolved</center></td>
						<td>
						<center>
							<a href="{!! URL::to('support/view', ['id' => $u->id]) !!}"><button type='button' class='btn btn-primary'><i class='fa fa-check'></i> View</button></a>
						</center>
						</td>
					</tr>
				@endforeach
				
			</table>
</div>

<div class="admin2">
	
</div>
