<br />
<div class="cl"></div>
<h1>Changeset #{!! $cs->id !!}</h1>

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
		
		<a href="{!! URL::to('changelog') !!}">
			<button style="margin-bottom:2%;" type="button" class="btn btn-primary">
				<i class="fa fa-angle-double-left"></i> Go back
			</button>
		</a>
		
		<div style="width:auto;margin-top:2%;margin-left:5%;" class="pull-right">
			@if(strlen($cs->email && $cs->track_progress) > 0)
			<a href="https://mail.google.com/mail/?view=cm&fs=1&to={!! $cs->email !!}&su=Changeset No.{!! $cs->id !!} - Manual Email&body=Hi ," target="_blank">
				<div style="padding:2px;cursor:pointer;" class="alert alert-info alert-block" id="">
					<center><i class="fa fa-envelope"></i> {!! (strlen($cs->email && $cs->track_progress) > 0) ? $cs->email : "None" !!}</center>
				</div>
			</a>	
			@else
			<a href="#">
				<div style="padding:2px;cursor:pointer;" class="alert alert-info alert-block" id="">
					<center><i class="fa fa-envelope"></i> No receivers</center>
				</div>
			</a>
			@endif
			
		</div>
		
		 <div style="margin:0px;height:15px;" change_id="{!! $cs->id !!}" class="percent_div progress {!! $cs->progressClass !!} active">
				<div id="" change_id="{!! $cs->id !!}" class="scheme_progress bar" style="width: {!! $cs->progress !!}%;">&nbsp;{!! $cs->progress !!}%</div>
		 </div>
		 
		 <br/>
		 
		 
						
		<div class="well">
		<div style="width:auto;margin-top:2%;margin-left:5%;" class="pull-right">
			<button data-toggle="modal" data-target="#changelog-edit" type="button" class="btn btn-primary"><i class="fa fa-cogs"></i> </button>
		</div>
			<i> {!! $cs->created_at  !!}  <i class="fa fa-clock"></i> ({!! Carbon\Carbon::parse($cs->created_at)->diffForHumans() !!}) </i>
			<h3> {!! $cs->title !!} - <font change_id="{!! $cs->id !!}" style="cursor:pointer;" class="finalize" color="green"><i class="fa fa-check-circle"></i> Mark complete</font></h3>
			
			{!! nl2br($cs->details) !!}
			
			<hr/>
			
			<div class="pull-left">
				<button change_id="{!! $cs->id !!}" change_amount="+10" type="button" class="increment btn btn-success"><i class="fa fa-plus"></i> 10% Progress <i class="fa fa-tasks"></i></button>
			</div>
			
			<div class="pull-right">
				<button change_id="{!! $cs->id !!}" change_amount="-10" type="button" class="decrement btn btn-danger"><i class="fa fa-minus"></i> 10% Progress <i class="fa fa-tasks"></i></button>
			</div>
			
			<br/>
			<br/>
			
		</div>
		
		@foreach($cs_comments as $comment) 
		
			<div class="well">
			
			<font size="2em">
			<b>{!! User::find($comment->user_id)->username !!}</b> at <i> {!! $comment->created_at  !!}  <i class="fa fa-clock"></i> ({!! Carbon\Carbon::parse($comment->created_at)->diffForHumans() !!}) </i>
			</font>
			
			<hr>
			
			{!! nl2br($comment->comment) !!}
		
			</div>
		
		@endforeach
	
		<div class="well">
		
			<form action="" method="POST">
				<h4> Add comment </h4>
				
				<input type="hidden" name="action" value="comment"/>
				<textarea name="comment" placeholder="Type comment here" style="width:98%"></textarea>
				
				<hr>
				
				<button class="btn btn-primary" type="submit">Comment</button>
				
			</form>
			
		</div>
	
<!-- Changelog edit modal -->
<form action="{!! URL::to('changelog/edit') !!}" method="POST">
<div id="changelog-edit" class="modal fade" role="dialog">

	  
	  <div class="modal-dialog">
	  
	  <!-- Modal content-->
	  <div class="modal-content">
		
	  <div class="modal-header">
		<button type="button" class="close" data-dismiss="modal">&times;</button>
		<h4 class="modal-title">Edit change set</h4>
	  </div>
	
	  <div class="modal-body">
		
			
			<table width="100%">
				
				<tr>
					<td><b> ID </b></td>
				</tr>
				<tr>
					<input type="hidden" name="id" value="{!! $cs->id !!}">
					<td><input disabled type="text" value="{!! $cs->id !!}"></td>
				</tr>
				
				<tr>
					<td><b> Title </b></td>
				</tr>
				<tr>
					<td><input type="text" value="{!! $cs->title !!}" name="title"></td>
				</tr>
				
				<tr>
					<td><b> Details </b></td>
				</tr>
				<tr>
					<td>
						<textarea style='width:90%;height:60px;' name="details">{!! $cs->details !!}</textarea>
					</td>
				</tr>
				
				<tr>
					<td>
					<table width="100%">
						<tr>
							<td width="100%" colspan="2">
								<b>Receive completion progress update via specified email</b><br/><br/>
							</td>
						</tr>
						<tr>
							<td width="5%" style="vertical-align:top"><input name="track_progress" @if($cs->track_progress) checked @endif  type="checkbox" data-toggle="toggle" data-onstyle="primary"></td>
							<td width="95%" style="vertical-align:top"><input name="email" value="{!! $cs->email !!}" type="email" placeholder="Email address"></td>
						</tr>
					</table>
					</td>
				</tr>
			
			
			</table>
			
			
	  </div>
	  
	  <div class="modal-footer">
		<!--<button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>-->
		<button type="submit" class="btn btn-primary" style="padding:2%">Submit</button>
		</form>
	  </div>
	  
	  
	</div>

	</div>
	</div>
			
</form>
<!-- / End Changelog edit modal -->
	
{!! HTML::script('resources/js/util/changelog.js?222') !!}

</div>
@section('extra_scripts')
{!! HTML::style('resources/js/bootstrap-toggle-master/css/bootstrap2-toggle.min.css') !!}
{!! HTML::script('resources/js/bootstrap-toggle-master/js/bootstrap2-toggle.min.js') !!}
@endsection
