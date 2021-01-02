</div>

<div class="cl"></div>
<h1>Reinstate Customer {!! $customer->id !!}</h1>
<div class="cl"></div>

<a href="{!! URL::to('reinstate_account') !!}">
	<button class="btn btn-primary">
		&lt; Go Back
	</button>
</a>

@include('includes.notifications')

@if($customer->replaced)
	<hr/>
	<div class="alert alert-warning">
		
		<font color="red"><b>Warning:</b></font> 
		<font color="black">This apartment '{!! $customer->username !!}' already has an 
		<a href="/customer/{!! $customer->replacement->id !!}">existing customer (customer {!! $customer->replacement->id !!}) that commenced on {!! $customer->replacement->commencement_date  !!}</a>. 
		<br/>Reinstating this customer will create a "duplicate customer" scenario.
		Please close the <a href="/customer/{!! $customer->replacement->id !!}">existing customer, (customer {!! $customer->replacement->id !!})</a>
		before continuing!
		</font>
	</div>
	</font>
	<hr/>
@endif

<table width="100%">
	<tr>
	
		<!-- Left -->
		<td style="vertical-align: top" width="50%">
			
			<table width="100%" class="table table-bordered">
				
				<tr><td><b>Customer ID: </b></td><td> {!! $customer->id !!} </td></tr>
				<tr><td><b>Full name: </b></td><td> <?php try { echo Crypt::decrypt($customer->first_name) . Crypt::decrypt($customer->surname); } catch(Exception $e) {} ?></td></tr>
				<tr><td><b>Username: </b></td><td> {!! $customer->username !!} </td></tr>
				<tr><td><b>Balance: </b></td><td> &euro;{!! $customer->balance !!} </td></tr>
				<tr><td><b>Topups: </b></td><td> {!! PaymentStorage::where('customer_id', $customer->id)->count() !!} &horbar; (&euro;{!! number_format(PaymentStorage::where('customer_id', $customer->id)->sum('amount'), 2) !!})</td></tr>
				<tr><td><b>Commencement date: </b></td><td> {!! $customer->commencement_date !!} &horbar; {!! Carbon\Carbon::parse($customer->commencement_date)->diffForHumans() !!} </td></tr>
				<tr><td><b>Deleted at: </b></td><td> {!! $customer->deleted_at !!} &horbar; {!! Carbon\Carbon::parse($customer->deleted_at)->diffForHumans() !!} </td></tr>
				
			</table>
			
		</td>
		
		<!-- Right -->
		<td style="vertical-align: top;text-align:right;" width="50%">
			<br/>
			<center>
				<form action="" method="POST">
					<button class="btn btn-primary" style="font-size:1rem;padding: 3%;">
						<i class="fa fa-unlock"></i> Reinstate
					</button>
				</form>	
			</center>
		</td>
		
		
	</tr>
</table>