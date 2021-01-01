
</div>

<div><br/></div>
<h1>Campaigns</h1>


<div class="admin2">
	
	@if(Session::has('success'))
	<div class="alert alert-success">
	{{ Session::get('success') }}
	</div>
	@endif
	
	@if(Session::has('error'))
	<div class="alert alert-danger">
	{{ Session::get('error') }}
	</div>
	@endif
	
	<table width="100%">
		<tr>
			<td> 
				<a href="/campaigns/create">
					<button type="button" class="btn btn-success"><i class="fa fa-plus"></i> Create campaign</button>
				</a>
			</td>
		</tr>
	</table>
	
	<hr/>
	
	
	@foreach($campaigns as $k => $c) 
	
	<table width="100%" class="table table-bordered">
		<tr>
			<th width="20%"><b>Title</b></th>
			<th width="30%"><b>Schedule</b></th>
			<th width="15%"><b>Notifications sent</b></th>
			<th width="35%"><b>Manage</b></th>
		</tr>
		<tr>
			<td>{{ $c->title }}</td>
			<td>{{ Carbon\Carbon::parse($c->show_from)->format('Y-m-d') }} -> {{ Carbon\Carbon::parse($c->show_to)->format('Y-m-d') }}</td>
			<td>{{ $c->notifs_sent }}</td>
			<td>
			<a href="{{ URL::to('campaigns/view', ['id' => $c->id]) }}">
				<button class="btn btn-success" type="button"><i class="fa fa-eye"></i> View Campaign</button>
			</a>
			@if($c->announcement)
			<a href="{{ URL::to('announcements/view', ['id' => $c->announcement->id]) }}">
				<button class="btn btn-primary" type="button"><i class="fa fa-eye"></i> View Announcement</button>
			</a>
			@else
			<a href="#">
				<button class="btn btn" disabled type="button"><i class="fa fa-eye"></i> Not live yet</button>
			</a>
			@endif
			</td>
		</tr>
	</table>
	
	@endforeach
	
</div>