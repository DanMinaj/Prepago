
</div>

<div><br/></div>
<h1>Tariff setup</h1>

<div class="admin2">

@if($tariff)
	<div class="alert alert-success alert-block">
	<b>Note:</b> You are editing tariffs for 
	@if($scheme)	
	<b>'{!! $scheme->company_name !!}'.</b> &horbar; This will automatically edit the FAQ for this scheme.
	@else
	'<b>Scheme not found</b>'
	@endif
</div>
@else
	<div class="alert alert-info alert-block">
	<b>Note:</b> You are setting up tariffs for 
	@if($scheme)	
	<b>'{!! $scheme->company_name !!}'.</b> &horbar; This will automatically edit the FAQ for this scheme.
	@else
	'<b>Scheme not found</b>'
	@endif
</div>
@endif
	

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


<form action="" method="POST">
	<table class="table table-bordered" width="100%">
		<tr>
			<td style="vertical-align: middle;">
				<b> @if($tariff) {!! $tariff->tariff_1_name !!} @else kWh usage tariff @endif </b>
			</td>
			<td  style="vertical-align: middle;">
				<input type="hidden" name="scheme_number" value="{!! $scheme->scheme_number !!}"/>
				<input type="text" name="tariff_1"  @if($tariff) value="{!! $tariff->tariff_1 !!}" @else value="0" @endif >
			</td>
		</tr>
		<tr>
			<td style="vertical-align: middle;">
				<b> @if($tariff) {!! $tariff->tariff_2_name !!} @else daily usage tariff/standing charge @endif </b>
			</td>
			<td  style="vertical-align: middle;">
				<input type="text" name="tariff_2"  @if($tariff) value="{!! $tariff->tariff_2 !!}" @else value="0" @endif >
			</td>
		</tr>
		<tr>
			<td style="vertical-align: middle;">
				<b> @if($tariff) {!! $tariff->tariff_3_name !!} @else EV kWh usage tariff @endif </b>
			</td>
			<td  style="vertical-align: middle;">
				<input type="text" name="tariff_3"  @if($tariff) value="{!! $tariff->tariff_3 !!}" @else value="0" @endif >
			</td>
		</tr>
		<tr>
			<td style="vertical-align: middle;text-align: center;" colspan="2">
				<button type="submit" class="btn btn-success">Submit</button>
			</td>
		</tr>
	</table>
</form>

</div>