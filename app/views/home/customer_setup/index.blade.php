</div>
<div class="cl"></div>

<h1>Customer Set Up</h1>

<div class="admin2">

@if ($message1 = Session::get('successMessage'))
<div class="alert alert-success alert-block">
	<button type="button" class="close" data-dismiss="alert">&times;</button>
	{{ $message1 }}
</div>
@endif

@if ($message2 = Session::get('warningMessage'))
<div class="alert alert-warning alert-block">
	<button type="button" class="close" data-dismiss="alert">&times;</button>
	{{ $message2 }}
</div>
@endif

@if ($message3 = Session::get('errorMessage'))
<div class="alert alert-danger alert-block">
	<button type="button" class="close" data-dismiss="alert">&times;</button>
	{{ $message3 }}
</div>
@endif


  

</div>