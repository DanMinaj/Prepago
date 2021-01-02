
</div>

<div><br/></div>
<h1>Status code information</h1>

<style>
u{
	border: 1px solid #999;
    background: #ccc;
    padding-right: 92%;
    padding-top: 1px;
    padding-bottom: 1px;
    padding-left: 1px;
}
</style>

<div class="admin2">

	<table width="100%" class="table table-bordered">
		
		<tr>
			<th width="20%">
				<b> Status code </b>
			</th>
			<th width="80%">
				<b> Info </b>
			</th>
		</tr>
		
		<tr>
			<td>
				<b>	@if($status == "0") <u> 0</u> @else 0 @endif </b>
			</td>
			<td>
				The scheme is currently inactive. No customer's are present in this scheme, & it's setup is yet to be finalised.
			</td>
		</tr>
		
		<tr>
			<td>
				<b>	@if($status == "1") <u> 1</u> @else 1 @endif </b>
			</td>
			<td>
				The scheme is online & functioning properly.
			</td>
		</tr>
		
		<tr>
			<td>
				<b>	@if($status == "2") <u> 2</u> @else 2 @endif </b>
			</td>
			<td>
				The scheme's SIM is offline.
			</td>
		</tr>
		
		<tr>
			<td>
				<b>	@if($status == "11") <u> 11</u> @else 11 @endif </b>
			</td>
			<td>
				The scheme's SIM is online / pingable, however the connection line to it & the schemes meters has dropped.
			</td>
		</tr>
		
		<tr>
			<td>
				<b>	@if($status == "21") <u> 21</u> @else 21 @endif </b>
			</td>
			<td>
				The scheme's SIM is offline & cannot be pinged, and the connection line to it & the schemes meters has dropped.
			</td>
		</tr>
		
		
	
	</table>

</div>

	