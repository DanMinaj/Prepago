<br />
<div class="cl"></div>
<h1>Simulate scheme</h1>

<div class="admin">

@if(Session::has('successMessage'))
<div class="alert alert-success alert-block" id="support-success">
<button type="button" class="close" data-dismiss="alert">&times;</button>
{{Session::get('successMessage')}}
</div>
@endif

@if(Session::has('errorMessage'))
<div class="alert alert-danger alert-block" id="support-success">
<button type="button" class="close" data-dismiss="alert">&times;</button>
{{Session::get('errorMessage')}}
</div>
@endif


	<form action="" method="POST">
	<table width="100%">
		<tr>
			<td width="80%" style="vertical-align:top;">
				<table width="100%" class="">
					@if($simulatedScheme)
					<tr>
						<td>
						<b>Last updated: </b>
						@if($simulatedScheme->simulator_updated_at != null)
							{{ $simulatedScheme->simulator_updated_at }} 
							({{ Carbon\Carbon::parse($simulatedScheme->simulator_updated_at)->diffForHumans() }})
						@else
							n/a
						@endif
						<br/><br/>
						</td>
					</tr>
					@endif
					<tr>
						<td><b>Currently Simulating: </b></td>
					</tr>
					<tr>
						<td>
							<input type="text" name="scheme_name" readonly="" @if($simulatedScheme) value="{{ $simulatedScheme->scheme_nickname }}" @else @endif>
						</td>
					</tr>
					<tr>
						<td>
							<input type="text" name="currency_sign" @if($simulatedScheme) value="{{ $simulatedScheme->currency_sign }}" @else @endif>
						</td>
					</tr>
					<tr>
						<td><b>Change Scheme to Simulate: </b></td>
					</tr>
					<tr>
						<td>
							<select name="scheme_to_simulate">
								@if($simulatedScheme)
									<option value="{{ $simulatedScheme->scheme_number }}">{{ $simulatedScheme->scheme_nickname }}</option>
								@endif
								@foreach(Scheme::active() as $k => $s)
									@if($simulatedScheme && $simulatedScheme->scheme_number == $s->scheme_number || $s->simulator > 0) @else 
										<option value="{{ $s->scheme_number }}">{{ $s->scheme_nickname }}</option>
									@endif
								@endforeach
							</select>
						</td>
					</tr>
				</table>
			</td>
			<td width="20%" style="vertical-align:top;">
				<table width="100%">
					<tr><td><br/></td></tr>
					<tr>
						<td width="100%">
							<button type="submit" class="btn btn-primary"> Simulate scheme </button>
						</td>
					</tr>
				</table>
			</td>
		</tr>	
	</table>
	</form>
	
</div>
