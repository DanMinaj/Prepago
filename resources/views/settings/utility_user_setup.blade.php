<br />
<div class="cl"></div>
<h1>Utility User Setup</h1>

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

<div class="admin2">
<style>
	td > b{
		font-size: 13px;
	}
	td > b::after{
		content: '*'
	}
</style>
	<br/><br/>
	<form  method="POST" autocomplete="off" action="">
		
	<div class="well">
		<b>Information: </b> Please make sure you double check the scheme of which you are creating the account for!
	</div>
	
		<table width="100%">
		
			<tr>
				
				<!-- Left hand side -->
				<td style="vertical-align:top" width="50%">
					
					<table width="100%">
						
						<tr>
							<td><h4>User Information</h4></td>
						</tr>
						
						<tr>
							<td><b>Scheme</b></td>
						</tr>
						<tr>
							<td>
								<select name="scheme">
									@foreach(Scheme::orderBy('id', 'DESC')->get() as $s)
										
										<option value="{!!$s->id!!}">
											{!! $s->scheme_nickname !!}
										</option>
									
									@endforeach
								</select>
							</td>
						</tr>
						<tr>
							<td><b>Username</b></td>
						</tr>
						<tr>
							<td><input type="text" value="{!! Input::old('username') !!}" placeholder="Username" name="username"></td>
						</tr>
					
						<tr>
							<td><b>Password</b></td>
						</tr>
						<tr>
							<td><input type="text" value="{!! Input::old('password') !!}" placeholder="Password" name="password"></td>
						</tr>
						
						<tr>
							<td><b>Employee name</b></td>
						</tr>
						<tr>
							<td><input type="text" value="{!! Input::old('employee_name') !!}" placeholder="e.g John Doe" name="employee_name"></td>
						</tr>
						
						<tr>
							<td><b>Employee email</b></td>
						</tr>
						<tr>
							<td><input type="email" value="{!! Input::old('email_address') !!}" placeholder="e.g test@prepago.ie" name="email_address"></td>
						</tr>
						
					</table>
				
				</td>
				
				
				<!-- Right hand side -->
				<td style="vertical-align:top" width="50%">
				
					<table width="100%">
					
						<tr>
							<td><h4>User Permissions</h4></td>
						</tr>
						
						<tr>
							<td>
								<b>Permissions group 
								<a href="#" data-toggle="modal" data-target="#permissions-help" ><i class="fa fa-question"></i> Info</a>
								</b>
							</td>
						</tr>
						<tr>
							<td>
								<select name="group_id">
									<option value="6">
										Group 3
									</option>
									<option value="4">
										Group 5
									</option>
									<option value="3">
										Group 4
									</option>
									<option value="2">
										Group 2
									</option>
									<option value="1">
										Group 1
									</option>
								</select>
							</td>
						</tr>
						<tr>
							<td width="3%"><b>Is Installer</b></td><td width="97%"><input style='height:20px;width:20px;' type="checkbox" name="isInstaller"></td>
						</tr>
						<tr>
							
						</tr>
						
					</table>
				
				</td>
			
			
			</tr>
		
		
		</table>
		
		
		<hr>
		
		<center><button type="submit" class="btn btn-primary">Submit</button></center>
		
	</form>

	@include('modals.permissions_help')
				
</div>
