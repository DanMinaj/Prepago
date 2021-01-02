
</div>

<div><br/></div>
<h1>Announcement</h1>


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
		<a href="{{ URL::to('announcements') }}"><button class="btn btn-success"><i class="fa fa-plus"></i> Create announcement</button></a>
	</div>
	
	<hr/>
	
	<table width="100%" class="table table-bordered">
		
		<thead>
			<tr>
				<th width="10%"><b>Title</b></th>

				<th width="20%"><b>Display from</b></th>
				<th width="5%"><b>Total views</b></th>
				<th width="5%"><b>Comments</b></th>
				<th width="7%"><b><i class="fa fa-pencil"</b></th>
			</tr>
		</thead>
		@foreach($announcements as $k => $a) 
			
			<tr @if(Announcement::latest() && Announcement::latest()->id == $a->id) style='background:#abeaab;font-weight:bold;' @endif>
				<td>
					{{ $a->title }}
				</td>
				<td>
					{{ $a->show_at }} &horbar; {{ $a->stop_show_at }}
				</td>
				<td>
					<center><i class="fa fa-eye"></i> {{ $a->total_views }}</center>
				</td>
				<td>
					<center><i class="fa fa-comments"></i> {{ $a->comments->count() }}</center>
				</td>
				<td>
					<a href="{{ URL::to('announcements/view', ['id' => $a->id]) }}">
						<button class="btn btn-success" type="button"><i class="fa fa-eye"></i> View</button>
					</a>
				</td>
			</tr>
		
		@endforeach
		
	</table>
	
</div>


