
@if(!$operator)
	</div>
	<div><br/></div>
	<h1>{!! Scheme::find(Auth::user()->scheme_number)->company_name !!} - Scheme Settings > Operator not found</h1>

	<div class="admin2">
	<div class="alert alert-danger alert-block">
	<button type="button" class="close" data-dismiss="alert">&times;</button>
	Operator not found in this scheme.
	</div>
	</div>
@else
</div>

<div><br/></div>
<h1>{!! Scheme::find(Auth::user()->scheme_number)->company_name !!} - Scheme Settings > Manage Operator {!!($operator->username)!!}</h1>


<div class="admin2">

@if ($message1 = Session::get('successMessage'))
<div class="alert alert-success alert-block">
	<button type="button" class="close" data-dismiss="alert">&times;</button>
	{!! $message1 !!}
</div>
@endif

@if ($message2 = Session::get('warningMessage'))
<div class="alert alert-warning alert-block">
	<button type="button" class="close" data-dismiss="alert">&times;</button>
	{!! $message2 !!}
</div>
@endif

@if ($message3 = Session::get('errorMessage'))
<div class="alert alert-danger alert-block">
	<button type="button" class="close" data-dismiss="alert">&times;</button>
	{!! $message3 !!}
</div>
@endif
	
	<a href="{!!URL::to('settings/scheme_settings')!!}">
	<button type="button" class="btn btn-primary">
		&lt;&lt; Go back
	</button>
	</a>
	
	<br/><br/>
	
	 <form onsubmit="return confirm('Are you sure you would like to change this operators password?');" action="" method="POST">
		<input type="hidden" name="operator_id" value="{!!$operator->id!!}">
		<table class="table table-bordered" width="100%">
			
			<tr>
				<td style='vertical-align:middle' width="50%"><b>Password</b></td>	
				<td style='vertical-align:middle' width="25%"><input type="text" name="new_password" placeholder="Desired new password"></td>
				<td style='vertical-align:middle' width="25%"><button type="submit" name="change_password" value="yes" class="btn btn-success">Change password</button></td>
			</tr>
			
		</table>
	</form>
	
	<form action="" method="POST">
		<input type="hidden" name="operator_id" value="{!!$operator->id!!}">
		<table class="table table-bordered" width="100%">
			
			
			<tr>
				<td style='vertical-align:middle' width="50%" colspan='1'><button type="submit" class="btn btn-success">Save changes</button></td>
				<td style='vertical-align:middle;text-align:center;' width="50%" colspan='1'>Last updated: {!!$operator->updated_at!!}</td>
				
			</tr>
			
			<tr>
				<td style='vertical-align:middle' width="50%"><b>Username</b></td>	<td style='vertical-align:middle' width="50%"><input type="text" name="username" placeholder="Username" value="{!! $operator->username !!}"></td>
			</tr>

			<tr>
				<td style='vertical-align:middle' width="50%"><b>Group ID</b></td>	<td style='vertical-align:middle' width="50%"><input type="text" name="group_id" placeholder="Group ID" value="{!! $operator->group_id !!}"></td>
			</tr>
			
			<tr>
				<td style='vertical-align:middle' width="50%"><b>Employee Name</b></td>	<td style='vertical-align:middle' width="50%"><input type="text" name="employee_name" placeholder="Employee Name" value="{!! $operator->employee_name !!}"></td>
			</tr>
			
			<tr>
				<td style='vertical-align:middle' width="50%"><b>Email</b></td>	<td style='vertical-align:middle' width="50%"><input type="text" name="email_address" placeholder="Email" value="{!! $operator->email_address !!}"></td>
			</tr>
			
			<tr>
				<td style='vertical-align:middle' width="50%"><b>Locked</b></td>	<td style='vertical-align:middle' width="50%"><input type="text" name="locked" placeholder="Locked" value="{!! $operator->locked !!}"></td>
			</tr>
			
			<tr>
				<td style='vertical-align:middle' width="50%"><b>Remember token</b></td>	<td style='vertical-align:middle' width="50%"><input type="text" name="remember_token" placeholder="Remember token" value="{!! $operator->remember_token !!}"></td>
			</tr>
			
			<tr>
				<td style='vertical-align:middle' width="50%"><b>Last online</b></td>	<td style='vertical-align:middle' width="50%"><input type="text" placeholder="Last online" disabled='' value="{!! $operator->is_online_time !!}"></td>
			</tr>

		</table>
	
	</form>
	
</div>
@endif