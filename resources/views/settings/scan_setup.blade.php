
</div>

<div><br/></div>
<h1>Insert meter(s) & scu(s) from scan output</h1>

<div class="admin2">

<div class="alert alert-info alert-block">
	<b>Note:</b> Please note that you are inserting this into the scheme 
	<?php $scheme = Scheme::find(Auth::user()->scheme_number); ?>
	@if($scheme)	
	<b>'{!! $scheme->company_name !!}'</b>
	@else
	'<b>Scheme not found</b>'
	@endif
</div>
	
<br/><br/>
@if ($message = Session::get('successMessage'))
<div class="alert alert-success alert-block">
	<button type="button" class="close" data-dismiss="alert">&times;</button>
	{!! $message !!}
</div>
@endif

@if ($message = Session::get('warningMessage'))
<div class="alert alert-warning alert-block">
	<button type="button" class="close" data-dismiss="alert">&times;</button>
	{!! $message !!}
</div>
@endif

@if ($message = Session::get('errorMessage'))
<div class="alert alert-danger alert-block">
	<button type="button" class="close" data-dismiss="alert">&times;</button>
	{!! $message !!}
</div>
@endif


<table width="100%" class="table table-bordered">
	
	<tr>
		<td colspan="2">	
			<h4> Meter Lookup</h4>
		</td>
	</tr>

	<tr>
		<td>
			<b> Meter last 8</b>
		</td>
		<td>
		{!! $meterLookup->last_eight !!}
		</td>
	</tr>
	
	
	<tr>
		<td>
			<b> Meter last 8</b>
		</td>
		<td>
		{!! $meterLookup->scu_last_eight !!}
		</td>
	</tr>
	
	
</table>	

<hr/>

@if(Session::has('total'))
<table width="100%" class="table table-bordered">
	
	<tr>
		<td colspan="2">	
			<h4> Results ({!! count(Session::get('total')) !!}) </h4>
		</td>
	</tr>
	<tr>
		<td>
			<b> Newly inserted </b>
		</td>
		<td>
		{!! count(Session::get('inserted')) !!}
		</td>	
	</tr>
	<tr>
		<td>
			<b> Already existed </b>
		</td>
		<td>
		{!! count(Session::get('existing')) !!}
		</td>	
	</tr>
	<tr>
		<td>
			<b> Discrepancies Fixed</b>
		</td>
		<td>
		{!! count(Session::get('errors')) !!}
		</td>	
	</tr>


<hr/>
@endif

@if(Session::has('existing') && count(Session::get('existing')) > 0) 
<table width="100%" class="table table-bordered">
	<tr>
		<td colspan="3"><b>mbus_address_translations already <b>existing</b></td>
	</tr>

	@foreach(Session::get('existing') as $m)
	<tr>
		<td> {!! $m['8digit'] !!} </td>
		<td> {!! $m['16digit'] !!} </td>
		@if($m->permanentMeter)
		<td>{!! $m->permanentMeter->house_name_number !!} {!! $m->permanentMeter->street1 !!}</td>
		@else
		<td> Customer needs to be found </td>
		@endif
	</tr>
	@endforeach
	
</table>
<hr/>
@endif

<form action="" method="POST">
	<textarea style="width:100%;height:200px;" name="scan" placeholder="Scan results">@if(Session::has('input')){!! Session::get('input') !!}@endif</textarea>
	<input style="width:101.5%" type="submit" class="btn btn-primary" value="Insert">
</form>
			
	

</div>