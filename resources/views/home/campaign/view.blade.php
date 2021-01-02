
</div>

<div><br/></div>
<h1>Campaign View {!! $campaign->id !!}: {!! $campaign->title !!}</h1>


<div class="admin2">

	@if(Session::has('successMessage'))
	<div class="alert alert-success">
	{!! Session::get('successMessage') !!}
	</div>
	@endif
	
	@if(Session::has('errorMessage'))
	<div class="alert alert-danger">
	{!! Session::get('errorMessage') !!}
	</div>
	@endif
	
	
	<a href="{!! URL::to('campaigns') !!}">
	<button class="btn btn-primary"> <i class="fa fa-long-arrow-alt-left"></i> Back </button>
	</a>
	
	<hr/>
	
	<div class="row-fluid">
		@if($campaign->announcement)
		<div class="span1">
			<i class="fa fa-eye"></i> {!! $campaign->announcement->total_views !!}
		</div>
		@endif
		<div class="span10 pull-right" style="text-align: right;">
			{!! $campaign->show_from !!} &horbar; {!! $campaign->show_to !!}
		</div>
	</div>
	
	<div class="row-fluid">
		<div class="span7">
			<h3>{!! $campaign->title !!}</h3>
		</div>
		<div class="span5 pull-right" style="text-align: right;">
			@if($campaign->announcement)
				<a href="{!! URL::to('announcements/edit', ['id' => $campaign->announcement->id]) !!}">
					<button class="btn btn-success" type="button"><i class="fa fa-pencil-alt"></i> Edit Announcement (Live)</button>
				</a>
			@else
				<a href="{!! URL::to('campaigns/edit', ['id' => $campaign->id]) !!}">
					<button class="btn btn-success" type="button"><i class="fa fa-pencil-alt"></i> Edit Campaign</button>
				</a>
			@endif
		</div>
	</div>
	
	<div class="row-fluid">
		<div class="span12">
			<b>Teaser: {!! $campaign->teaser !!}</b>
		</div>
	</div>
	
	<div class="row-fluid">
		<div class="span12">
			<b>{!! $campaign->created_at !!} ({!! Carbon\Carbon::parse($campaign->created_at)->diffForHumans() !!})</b>
		</div>
	</div>
	
	<div class="row-fluid">
		<div class="span12">
			{!! $campaign->body !!}
		</div>
	</div>
	

	
</div>


