<table width="100%">

	<style>
		.barcode{
			transform: rotate(90deg) translateY(-100%);
			transform-origin:top left;
			width:4rem;
		}
		@media print {
		  .break { page-break-after: always; }
		}
	</style>
	<tr>
	
		<!-- Account information -->
		<td style="vertical-align:top;width:70%">
		<br/><br/>
		{!! $customer->first_name !!} {!! $customer->surname !!}<br/>
		{!! $address_1 !!}<br/>
		{!! $address_2 !!}<br/>
		{!! $address_3 !!}<br/>
		{!! $address_4 !!}<br/><br/>
		
		{!! $customer->barcode !!}<br/>
		<img class="barcode" src='http://prepago-admin.biz/Barcodes/9826004400000013877.png'>
		</td>
		
		<!-- Business information -->
		<td style="vertical-align:top;width:30%;">
			<table width="100%">
				<tr>
					<td style="text-align:right;margin-right:3%;">
						<img style="width:10rem" src="https://www.snugzone.biz/images/logo.png"/>
					</td>
				</tr>
				<tr>
					<td style="text-align:right;margin-right:3%;">
						1 Woodbine Avenue<br/>Blackrock<br/>Co. Dublin<br/>
					</td>
				</tr>
			</table>
		</td>
	</tr>
	
	<tr>
		<td>
			<b>Date:</b><br/>
			{!! date('d M Y') !!}
		</td>
	</tr>
	<tr>
		<td>
			<br/><b>Account commencement date:</b><br/>
			{!! (new DateTime($customer->commencement_date))->format('d M Y') !!}
		</td>
	</tr>
	<tr>
		<td><br/></td>
	</tr>
	<tr>
		<td>
			<b>From - To:</b><br/>
			{!! (new DateTime($from))->format('d M Y') !!} - {!! (new DateTime($to))->format('d M Y') !!} ({!! $no_of_days !!} days)
		</td>
	</tr>
	<tr>
		<td><br/></td>
	</tr>
</table>

<hr/>
<table width="100%" style="border: 2px solid black;padding: 3%;">

	<tr>
		<td colspan="3">
			<h3> Payments received ({!! $topups->count() !!})</h3>
		</td>
	</tr>

	
	<tr>
		<td colspan="3">
		 <b>Amount paid:</b> &euro;{!! number_format($topups->sum('amount'), 2) !!}
		</td>
	</tr>

	<tr>
		<th width="20%"><b> Date </b></th>
		<th width="30%"><b> Amount </b></th>
		<th width="50%"><b> Transaction ID</b></th>
	</tr>
	
	@foreach($topups->get() as $k => $t)
	<tr>
		<td>{!! (new DateTime($t->settlement_date ))->format('d M Y') !!} </td>
		<td>&euro;{!! number_format($t->amount, 2) !!}</td>
		<td>{!! $t->ref_number !!}</td>
	</tr>
	@endforeach
	
</table>
<div style='page-break-after:always'></div>
<hr/>
<table width="100%" style="border: 2px solid black;padding: 3%;">
	
	<tr>
		<td colspan="3">
		 <b>Value of SMS Messages:</b> &euro;{!! number_format($smss->sum('charge'), 2) !!}
		</td>
	</tr>
	<tr>
		<td colspan="3">
			<h3> SMS Messages </h3>
		</td>
	</tr>

	<tr>
		<th width="20%"><b> Date </b></th>
		<th width="20%"><b> Charge </b></th>
		<th width="60%"><b> Message </b></th>
	</tr>
	
	@foreach($smss->get() as $k => $s)
	<tr>
		<td>{!! (new DateTime($s->date_time))->format('d M Y') !!}</td>
		<td>&euro;{!! $s->charge !!}</td>
		<td>{!! $s->message !!}</td>
	</tr>
	@endforeach
	
</table>


<footer>

</footer>