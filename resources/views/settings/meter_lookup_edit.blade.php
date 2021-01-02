
</div>

<div><br/></div>
<h1>Meter lookup - Edit meter</h1>


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
	
	<a href="{{URL::to('settings/meter_lookup')}}">
	<button type="button" class="btn btn-primary">
		&lt;&lt; Go back
	</button>
	</a>
	
	<br/><br/>
	
	@if($meter == null) 
		<div class="alert alert-warning alert-block">
			Meter Lookup ID {{ $id }} not found!
		</div>
	@else
	<form action="" method="POST">
		
		<table class="table table-bordered" width="100%">
			
			<tr>
				<td style='vertical-align:middle' width="50%" colspan='2'><button type="submit" class="btn btn-success">Save changes</button></td>
			</tr>
			
			<tr>
				<td style='vertical-align:middle' width="50%"><b><u>Meter last 8</u></b></td>	
				<td style='vertical-align:middle' width="50%"><input type="text" name="last_eight" placeholder="Meter last 8" value="{{ $meter->last_eight }}"></td>
			</tr>
			
			
			<tr>
				<td style='vertical-align:middle' width="50%"><b>Meter make</b></td>	
				<td style='vertical-align:middle' width="50%"><input type="text" name="meter_make" placeholder="Meter make" value="{{ $meter->meter_make }}"></td>
			</tr>
			
				
			<tr>
				<td style='vertical-align:middle' width="50%"><b>Meter model</b></td>	
				<td style='vertical-align:middle' width="50%"><input type="text" name="meter_model" placeholder="Meter model" value="{{ $meter->meter_model }}"></td>
			</tr>
			
				
			<tr>
				<td style='vertical-align:middle' width="50%"><b>Meter HIU</b></td>	
				<td style='vertical-align:middle' width="50%"><input type="text" name="meter_HIU" placeholder="Meter HIU" value="{{ $meter->meter_HIU }}"></td>
			</tr>
			
			<tr>
				<td style='vertical-align:middle' width="50%"><b><u>SCU last 8</u></b></td>	
				<td style='vertical-align:middle' width="50%"><input type="text" name="scu_last_eight" placeholder="SCU last 8" value="{{ $meter->scu_last_eight }}"></td>
			</tr>
			
			<tr>
				<td style='vertical-align:middle' width="50%"><b>SCU make</b></td>	
				<td style='vertical-align:middle' width="50%"><input type="text" name="scu_make" placeholder="SCU make" value="{{ $meter->scu_make }}"></td>
			</tr>
			
			<tr>
				<td style='vertical-align:middle' width="50%"><b>SCU model</b></td>	
				<td style='vertical-align:middle' width="50%"><input type="text" name="scu_model" placeholder="SCU model" value="{{ $meter->scu_model }}"></td>
			</tr>
			
			
		</table>
	
	</form>
	@endif
	
</div>
