</div>

<div><br/></div>
<h1>Meter lookup</h1>

</div>
<div class="cl"></div>
<div class="admin2">
		
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

<form action="" method="POST">		
<button type="submit" class="btn btn-success">Save changes</button><br/><br/>

		@foreach($meter_lookup as $ml)

	
		<table class="table table-bordered" width="100%">
			<tr>
				<th width="20%"><b>Meter</b></th>
				<th width="10%"><b>Meter Last-8</b></th>
				<th width="10%"><b>SCU Last-8</b></th>
				<th width="10%"><b>Manage</b></th>
			</tr>
			<tr>
				<td><input type="text"  value="{!! 	$ml->meter_make !!} {!! $ml->meter_model !!}" disabled="true" placeholder="Meter name"></td>
				<td><input type="text" name="{!! $ml->id !!}|last_eight" value="{!! $ml->last_eight !!}" placeholder="Meter Last-8"></td>
				<td><input type="text" name="{!! $ml->id !!}|scu_last_eight" value="{!! $ml->scu_last_eight !!}" placeholder="SCU Last-8"></td>
				<td><a class="btn btn-success" href="{!! URL::to('settings/meter_lookup', ['id' => $ml->id]) !!}">Edit</td>
			</tr>
		</table>
		
		@endforeach
		
</form>

</div>