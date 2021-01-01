
</div>

<div><br/></div>
<h1>Announcement View {{ $announcement->id }}: {{ $announcement->title }}</h1>


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
	
	
	<a href="{{ URL::to('announcements/all') }}">
	<button class="btn btn-primary"> <i class="fa fa-long-arrow-alt-left"></i> Back </button>
	</a>
	
	<hr/>
	
	<div class="row-fluid">
		<div class="span1">
			<i class="fa fa-eye"></i> {{ $announcement->total_views }}
		</div>
		<div class="span1">
			<i class="fa fa-comments"></i> {{ $announcement->comments->count() }}
		</div>
		<div class="span10 pull-right" style="text-align: right;">
			{{ $announcement->show_at }} &horbar; {{ $announcement->stop_show_at }}
		</div>
	</div>
	
	<div class="row-fluid">
		<div class="span7">
			<h3>{{ $announcement->title }}</h3>
		</div>
		<div class="span5 pull-right" style="text-align: right;">
			<a href="{{ URL::to('announcements/edit', ['id' => $announcement->id]) }}">
			<button class="btn btn-success" type="button"><i class="fa fa-pencil-alt"></i> Edit</button>
			</a>
		</div>
	</div>
	
	<div class="row-fluid">
		<div class="span12">
			<b>Teaser: {{ $announcement->teaser }}</b>
		</div>
	</div>
	
	<div class="row-fluid">
		<div class="span12">
			<b>{{ $announcement->created_at }} ({{ Carbon\Carbon::parse($announcement->created_at)->diffForHumans() }})</b>
		</div>
	</div>
	
	<div class="row-fluid">
		<div class="span12">
			{{ $announcement->body }}
		</div>
	</div>
	
	<h3> Comments </h3>
	@if($announcement->comments->count() <= 0)
		<center> There are no comments currently. </center>
	@endif
	@foreach($announcement->comments as $c)
	<div class="row-fluid">
		<div class="span12">
			
			<div class="row-fluid">
				<div class="span6">
					<b><a href="/customer/{{ $c->customer_id }}"> {{ Customer::find($c->customer_id)->username }} </a></b>
				</div>
				<div class="span6">
				{{ $c->created_at }} &horbar; {{ Carbon\Carbon::parse($c->created_at)->diffForHumans() }}
				</div>
			</div>
			
			<div class="row-fluid">
				<div class="span12">
					{{ $c->comment }}
				</div>
			</div>
	
		</div>
	</div>
	<hr/>
	@endforeach
	
</div>


