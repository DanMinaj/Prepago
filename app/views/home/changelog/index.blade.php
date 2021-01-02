	
@section('extra_scripts')

	{{ HTML::script('resources/js/datatable/datatables.min.js') }}
	{{ HTML::style('resources/js/datatable/datatables.min.css') }}
	
@stop


<br />
<div class="cl"></div>
<h1>Changelog</h1>

<div class="admin">

@if(Session::has('successMessage'))
<div class="alert alert-success alert-block" id="support-success">
<button type="button" class="close" data-dismiss="alert">&times;</button>
{{Session::get('successMessage')}}
</div>
@endif

@if(Session::has('errorMessage'))
<div class="alert alert-danger alert-block" id="support-success">
<button type="button" class="close" data-dismiss="alert">&times;</button>
{{Session::get('errorMessage')}}
</div>
@endif
<script>

$(document).ready(function() {
   $('#table2').dataTable({
		
		 "order": [[ 0, "desc" ]]
		
	});
} );
</script>
		

</div>
<style>
	.slim{
	    height: 25px;
		font-size: 10px;
		padding: 0px 2% 0px 2%;
	}
	.prevent_overflow{
		max-width:200px;
		white-space: nowrap;
		overflow: hidden;
		text-overflow: ellipsis;
	}
	.changeset{
		background: #fff;
		border: 1px solid #ccc;
		border-radius: 4px;
		margin: 0%;
		padding: 0px 5px 0px 5px;
	}
	.changeset::after{
		content: " >>"
	}
</style>
<div class="admin2">
		
		<button data-toggle="modal" data-target="#changelog" style="padding:1%;margin-bottom:2%;" type="button" class="btn btn-primary">
			Create a new submission
		</button>
		
		<table id="table1" class="table table-bordered">
			
			<thead>
				<tr>
					<th width='5%'><b>ID</b></th>
					<th width='20%'><b>Title</b></th>
					<th width='10%'><b>Details</b></th>
					<th width='25%'><b>Progress %</b></th>
					<th width='25%'><b>Actions</b></th>
				</tr>
			</thead>
			
			<tbody>
			
			@foreach($pending as $cs)
			
				<tr>
					
					<td>{{ $cs->id }}</td>
					
					<td>
					
						<div class="prevent_overflow">
							<a class="changeset" href="{{ URL::to('changelog/view', ['id' => $cs->id]) }}">{{ $cs->title }}</a>
						</div>
						
					</td>
					
					<td>
						
						<div class="prevent_overflow">
						{{ $cs->details }}
						</div>
						
					</td>
					
					<td>
					
						<div style="margin:0px;" change_id="{{ $cs->id }}" class="percent_div progress {{ $cs->progressClass }} active">
						  <div id="" change_id="{{ $cs->id }}" class="scheme_progress bar" style="width: {{ $cs->progress }}%;">&nbsp;{{ $cs->progress }}%</div>
						</div>
									
					</td>
					
					<td>
							
						<button type="button" change_id="{{ $cs->id }}" class="btn btn-success slim mark-completed"><i class="fa fa-check"></i> Mark completed</button>
						<button type="button" change_id="{{ $cs->id }}" class="btn btn-warning slim send-reminder"><i class="fa fa-bell"></i> Send reminder</button>
						<button type="button" disabled class="btn btn-primary slim"><i class="fa fa-comment-alt"></i>&nbsp;{{ $cs->comments()->count() }}</button>	
						
					</td>
				
				</tr>
				
			@endforeach
			
			</tbody>
			
			
		</table>
		
		
	
		<table id="table2" class="table table-bordered">
			
			<thead>
				<tr>
					<th width='5%'><b>ID</b></th>
					<th width='20%'><b>Title</b></th>
					<th width='10%'><b>Details</b></th>
					<th width='25%'><b>Progress %</b></th>
					<th width='25%'><b>Actions</b></th>
				</tr>
			</thead>
			
			<tbody>
			
			@foreach($completed as $cs)
			
				<tr>
					
					<td>{{ $cs->id }}</td>
					
					<td>
					
						<div class="prevent_overflow">
						<a class="changeset" href="{{ URL::to('changelog/view', ['id' => $cs->id]) }}">{{ $cs->title }}</a>
						</div>
						
					</td>
					
					<td>
						
						<div class="prevent_overflow">
						{{ $cs->details }}
						</div>
						
					</td>
					
					<td>
					
						<div style="margin:0px;"  change_id="{{ $cs->id }}" class="percent_div progress {{ $cs->progressClass }} active">
						  <div id="" change_id="{{ $cs->id }}" class="scheme_progress bar" style="width: {{ $cs->progress }}%;">&nbsp;{{ $cs->progress }}%</div>
						</div>
									
					</td>
					
					<td>
							
						<button type="button" change_id="{{ $cs->id }}" class="btn btn-warning slim mark-uncompleted"><i class="fa fa-times"></i> Mark un-completed</button>
						
					</td>
				
				</tr>
				
			@endforeach
			
			</tbody>
			
			
		</table>
		
		
	
	

@include('modals.changelog')	
{{ HTML::script('resources/js/util/changelog.js?0193') }}

</div>
