<!DOCTYPE html>
<html xmlns="https://www.w3.org/1999/xhtml">

<head>
    <meta name="viewport" content="width=1100" />
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <link href='https://fonts.googleapis.com/css?family=Droid+Sans' rel='stylesheet' type='text/css'/>

    <script src="https://code.jquery.com/jquery-1.9.1.js"></script>
    <script src="https://code.jquery.com/ui/1.10.3/jquery-ui.js"></script>
    <title>Welcome To The Prepago Utility Dashboard</title>
    {!! HTML::style('resources/css/bootstrap-responsive.min.css') !!}
    {!! HTML::style('resources/css/bootstrap.min.css') !!}
    {!! HTML::style('resources/css/style1.css') !!}
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css" />
    {!! HTML::style('resources/css/example-fluid-layout.css') !!}
	{!! HTML::style('resources/fontawesome/css/all.css') !!}
</head>

<body>
<div class="wrapper" >
    <div class="admin">
        {!! $page !!}
    </div>
</div>

</body>
</html>