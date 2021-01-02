

</div>
<div class="cl"></div>
<h1>Closing {!! $customer->username  !!}
   
</h1>
<div class="admin2">
	
	@if (Session::has('successMsg') && Session::get('successMsg'))
		<div class="alert alert-success">{!! Session::get('successMsg') !!}</div>
	@endif
    @include('includes.notifications')
	
	<style>
		
		table#spaced {
			
			border-collapse: separate; 
			border-spacing: 30px;
			
		}
		
	
	</style>
	
	<!--<div class="alert alert-danger">The customers information will be wiped from the database.</div>-->
	
	<a href="{!! URL::to('close_account_alt') !!}"> &lt;&lt; Go back </a>
	
	<hr/>
	
	<a href="{!! URL::to('close_account_alt/confirm/download', ['id' => $customer->id]) !!}"> <button type="button" class="btn btn-primary"><i class="fa fa-download"></i> Download as CSV</button> </a>
	
	<table width="100%">
		
		<tr>	
			
			<td valign="top" width="50%">
				
				
				<table id="spaced" width="100%">
						
						
						<tr>
							<td> <h4> Customer information </h4> </td>
						</tr>
						
						<tr>
							<td> <b> ID </b> </td> <td> <a href="{!! URL::to('customer_tabview_controller/show', ['customer_id' => $customer->id]) !!}"> {!! $customer->id !!} </a> </td>
						</tr>
						
						<tr>
							<td> <b> Barcode </b> </td> <td> {!! $customer->barcode !!} </td>
						</tr>
						
						<tr>
							<td> <b> Full name </b> </td> <td> {!! $customer->first_name . ' ' . $customer->surname !!} </td>
						</tr>
						
						<tr>
							<td> <b> Username </b> </td> <td> {!! $customer->username !!} </td>
						</tr>
						
						<tr>
							<td> <b> Email address </b> </td> <td> {!! $customer->email_address !!} </td>
						</tr>
						
						<tr>
							<td> <b> Mobile number </b> </td> <td> {!! $customer->mobile_number !!} </td>
						</tr>
						
				</table>
			
			</td>
			
			@if($customer->districtMeter && $customer->permanentMeter)
				
			<td valign="top" width="50%">
				
				<table id="spaced" width="100%">
					
					<tr>
						<td> <h4> Meter Data </h4> </td>
					</tr>
					
					<tr>
						<td> <b> Meter number </b> </td> <td> {!! $customer->districtMeter->meter_number !!} </td>
					</tr>
				
					<tr>
						<td> <b> Latest reading </b> </td> <td> {!! $customer->districtMeter->latest_reading !!} kWh ( {!! $customer->districtMeter->latest_reading_time !!} ) </td>
					</tr>
					
					<tr>
						<td> <b> Sudo reading </b> </td> <td> {!! $customer->districtMeter->sudo_reading !!} kWh ( {!! $customer->districtMeter->sudo_reading_time !!} ) </td>
					</tr>
					
					<tr>
						<td> <b> Last return temp. </b> </td> <td> {!! $customer->districtMeter->last_return_temp !!}&deg; </td>
					</tr>
					
					<tr>
						<td> <h4> Permanent Meter Data </h4> </td>
					</tr>
					
					<tr>
						<td> <b> In use. </b> </td> <td> {!! $customer->permanentMeter->in_use !!} </td>
					</tr>
				
				</table>
				
			</td>
			
			@else
				
			<td valign="top" width="50%">
				
				<table id="spaced" width="100%">
					
					<tr>
						<td> <h4> Meter information </h4> </td>
					</tr>
					
					<tr>
						<td> This customer has no meter assosciated with them. </td>
					</tr>
				
				</table>
				
			</td>
				
			@endif
			
		</tr>
		
		
	</table>
	
	<table id="spaced" width="100%">
		
		<tr>
			
			<form onsubmit="return confirm('Are you sure you would like to close this customers account?');" action="" method="POST">
			
				<td> <button style="padding:2%;" type="submit" class="btn btn-warning btn-lg btn-block">Close account</button> </td>
			
			</form>
			
		</tr>
		
	</table>
	
</div>
