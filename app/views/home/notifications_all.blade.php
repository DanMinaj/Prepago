
</div>

<div><br/></div>
<h1>Notifications ({{ count($notifications) }})</h1>


<div class="admin2">
	
	@if(Session::has('successMessage'))
	<div class="alert alert-success">
	{{ Session::get('successMessage') }}
	</div>
	@endif
	
	@if(Session::has('errorMessage'))
	<div class="alert alert-danger">
	{{ Session::get('errorMessage') }}
	</div>
	@endif
	
	<div>
		<a href="{{ URL::to('notifications') }}"><button class="btn btn-success"><i class="fa fa-plus"></i>&nbsp;&nbsp;<i class="fa fa-bell"></i> Create notification</button></a>
	</div>
	
	<hr/>
	
	<table width="100%" class="table table-bordered">
		
		<thead>
			<tr>
				<th width="10%"><b>Title</b></th>
				<th width="25%"><b>Body</b></th>
				<th width="40%"><b>Statistics</b></th>
				<th width="25%"><b>Created at</b></th>
			</tr>
		</thead>
		@foreach($notifications as $k => $n) 
			
			<tr>
				<td>
					{{ $n->title }}
				</td>
				<td>
					{{ $n->body }}
				</td>
				<td>
				<?php $stats = $n->getStats(); ?>
					{{ $stats->views }}/{{ $stats->total }} Customers have viewed this notification
				</td>
				<td>
					{{ $n->created_at }} &horbar; ({{ Carbon\Carbon::parse($n->created_at)->diffForHumans() }})
				</td>
			</tr>
		
		@endforeach
		
	</table>
	
</div>


