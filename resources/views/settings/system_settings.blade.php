<br />
<div class="cl"></div>
<h1>System Settings - All Settings</h1>

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
			<th width='10%'><b>Type</b></th>
			<th width='30%'><b>Name</b></th>
			<th width='30%'><b>Value</b></th>
			<th width='30%'><b>Edit</b></th>
		</tr>
		
		@foreach($settings as $s)
			<tr>
			
			<form action="{!!URL::to('settings/system_settings/save', $s->id)!!}" method="POST">
				<td>
					<input name="type" type="text" value="{!!$s->type!!}" placeholder="Setting Type">
				</td>
				<td>
					<textarea name="name" style="width:70%" placeholder="Setting Name">{!!$s->name!!}</textarea>
					<br/>{!!$s->desc!!}
				</td>
				<td>
					<textarea name="value" style="width:70%" placeholder="Setting Value">{!!$s->value!!}</textarea>
				</td>
				<td>
					
					<a href="{!!URL::to('settings/system_settings/remove', $s->id)!!}">
						<button type="button" class="btn btn-danger">Delete</button>
					</a>
					
					<button type="submit" class="btn btn-success">Save</button>
					
				
				</td>
			</form>
			
			</tr>
		@endforeach
		
		<tr>
			
			<form action="{!! URL::to('settings/system_settings/add') !!}" method="POST">
				
				<td>
					<input name="type" type="text" value="" placeholder="Setting Type">
				</td>
				<td>
					<textarea name="name" style="width:70%" placeholder="Setting Name"></textarea>
				</td>
				<td>
					<textarea name="value" style="width:70%" placeholder="Setting Value"></textarea>
				</td>
				<td>
					
					<button type="submit" class="btn btn-success">Add</button>
				
				</td>
			</form>
		
		</tr>
		
	</table>

</div>

<div class="admin2">
	
</div>
