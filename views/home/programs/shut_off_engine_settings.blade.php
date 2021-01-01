
</div>

<div><br/></div>
<h1>Shut Off Engine Settings</h1>


<div class="admin2">

@if(Session::has('successMessage'))
<div class="alert alert-success">
		
		{{Session::get('successMessage')}}

</div>
@endif

@if(Session::has('errorMessage'))
<div class="alert alert-danger">
		
		{{Session::get('errorMessage')}}

</div>
@endif

@if($enabled)
	
<form action="{{ URL::to('settings/system_programs/shut_off_engine/save_programs') }}" method="POST">
<table width="100%">
	<tr>
		<td width="10%">
			<h4> New Shut Off Engine</h4>
		</td>
		<td width="10%">
			<h4> Old Shut Off Engine</h4>
		</td>
		<td width="10%">
		
		</td>
	</tr>
	<tr>
		<td>
			<input name="new_shut_off_engine" type="checkbox" @if($enabled->value == 'true') checked='true' @else @endif data-toggle="toggle" data-onstyle="primary">
		</td>
		<td>
			<input name="old_shut_off_engine" type="checkbox" @if($old_enabled) checked='true' @else @endif data-toggle="toggle" data-onstyle="primary">
		</td>
		<td>
			<button type="submit" class="btn btn-success">Save changes</button>
		</td>
	</tr>
</table>
</form>
<hr/>
@endif
				
			<table class="table table-bordered">
				
				
				<tr>
					<td width="30%"><b>Setting</b></td>
					<td width="30%"><b>Value</b></td>
					<td width="10%"><center><b><i class="fa fa-cogs"></i></b></center></td>
					<td width="10%"><center><b><i class="fa fa-cogs"></i></b></center></td>
				</tr>
				
				@foreach($settings as $setting)
				
					<tr>
						
						<form action="{{URL::to('settings/system_programs/shut_off_engine/save')}}" method="POST">
						<td>
							<input type="text" name="settings_name" value="{{$setting->name}}">
						</td>
						
						<td>
							@if(strlen($setting->value) > 30)
								<textarea style='height:150px;width:90%;' name="settings_value">{{$setting->value}}</textarea>
							@else
								<input type="text" name="settings_value" value="{{$setting->value}}">
							@endif
						</td>
						
						<td>
							<input type="hidden" name="setting_id" value="{{$setting->id}}" />
							<button type="submit" class="btn btn-success"><i class="fa fa-check"></i> Save</button>
							</form>
						</td>
						
						<td>
						
						
						<form action="{{URL::to('settings/system_programs/shut_off_engine/delete')}}" method="POST">
							<input type="hidden" name="setting_id" value="{{$setting->id}}" />
							<button type="submit" class="btn btn-danger"><i class="fa fa-times"></i> Delete</button>
						</form>
						
						</td>
						
					</tr>
				
				@endforeach
				
				<tr>
					
					<td>
					<form action="{{URL::to('settings/system_programs/shut_off_engine/add')}}" method="POST">
						<input type="text" name="setting_name" class="form-control" placeholder="Name">
					</td>
					
					<td>
						<input type="text" name="setting_value" class="form-control" placeholder="Value">
					</td>
					
					<td colspan='2'>
						<button type="submit" class="btn btn-success"><i class="fa fa-plus"></i> Add</button>
						</form>
					</td>
					
				</tr>
				
				</table>
				
</div>