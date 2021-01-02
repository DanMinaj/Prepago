<html xmlns="https://www.w3.org/1999/xhtml">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <meta name="viewport" content="width=1100" />
  <title>Login</title>
  <link media="all" type="text/css" rel="stylesheet" href="https://prepagoplatform.com/resources/css/style1.css">

  <link media="all" type="text/css" rel="stylesheet" href="https://prepagoplatform.com/resources/css/stylesheet.css">

  <link href='https://fonts.googleapis.com/css?family=Droid+Sans' rel='stylesheet' type='text/css'>
</head>
<div class="wrapper">
  <div class="admin">
    <div class="admin_box">

    </div>
    <div class="cl"></div>
    <h1>Login</h1>
    <div class="cl"></div>
    <div class="custome_left" id="login-blade">
      
       <form method="POST" action="https://prepagoplatform.com/login/login_action" accept-charset="UTF-8">
	   
	   
	  <input type="hidden" name="_token" value="<?php echo csrf_token(); ?>">


        @if($message = Session::get('signinerror'))
        <label style="text-transform:none;color: red">{!! $message !!}</label>
        @endif

        {!! Form::label('username', 'Username') !!}
        {!! Form::text('username') !!}
        {!! $errors->first('username', '<label style="text-transform:none;color: red">:message</label>') !!}

        {!! Form::label('password', 'Password') !!}
        {!! Form::password('password') !!}
        {!! $errors->first('password', '<label style="text-transform:none;color: red">:message</label>') !!}

        {!! Form::submit('') !!}
        {!! Form::close() !!}

      </form>

    </div>
	
	
	<div class="custome_right" id="login-blade">
			
		
		
	
	</div>
	
  </div>
</div>
<body>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
</body>
</html>
