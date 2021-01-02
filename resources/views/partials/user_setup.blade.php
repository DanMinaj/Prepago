{!! Form::label('employee_name', 'Employee Name:') !!}
{!! Form::text('employee_name') !!}

{!! Form::label('username', 'Username:') !!}
{!! Form::text('username') !!}

{!! Form::label('password', 'Password:') !!}
{!! Form::password('password') !!}

{!! Form::label('group', 'Group:') !!}
{!! Form::select('group', groups()) !!}

{!! Form::label('isInstaller', 'isInstaller:') !!}
{!! Form::select('isInstaller', ['0' => 'no', '1' => 'yes']) !!}