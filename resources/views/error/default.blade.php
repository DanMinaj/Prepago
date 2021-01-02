
</div>

<div><br/></div>
<h1>An error occured!</h1>


<div class="admin2">


	<font style="font-size:15px;color:green">Details of error</font>
    @if ($message = Session::get('successMessage'))
		
		{!! $message !!}
		
	@endif
	
</div>