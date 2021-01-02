
</div>

<div><br/></div>
<h1>Payment settings</h1>

<div class="admin2">

<br/><br/>
@if ($message = Session::get('successMessage'))
<div class="alert alert-success alert-block">
	<button type="button" class="close" data-dismiss="alert">&times;</button>
	{{ $message }}
</div>
@endif

@if ($message = Session::get('warningMessage'))
<div class="alert alert-warning alert-block">
	<button type="button" class="close" data-dismiss="alert">&times;</button>
	{{ $message }}
</div>
@endif

@if ($message = Session::get('errorMessage'))
<div class="alert alert-danger alert-block">
	<button type="button" class="close" data-dismiss="alert">&times;</button>
	{{ $message }}
</div>
@endif

	<ul class="nav nav-tabs" style="margin: 30px 0">
      <li class="active"><a href="#customers" data-toggle="tab">Customers</a></li>
      <li><a href="#sources" data-toggle="tab">Sources</a></li>
      <li><a href="#logs" data-toggle="tab">Logs</a></li>
      <li style='border-left:1px dotted #ccc;border-radius:0px;'><a href="#payments" data-toggle="tab">Payments</a></li>
      <li style='border-left:1px dotted #ccc;border-radius:0px;'><a href="#failed_payments" data-toggle="tab">Failed payments</a></li>
   </ul>



	<div class="tab-content">
     
	 <div class="tab-pane active" id="customers" style="">
	 <table width="100%" class="table table-bordered">
		<tr>
			<th width="10%"><b> Customer</b></th>
			<th width="15%"><b>	Token </b></th>
			<th width="25%"><b>	Last topup </b></th>
			<th width="50%"><b>	Created </b></th>
		</tr>
		@foreach($stripeCustomers as $c)
			<tr>
				<td><a href="/customer/{{ $c->customer_id }}" target="_blank">({{ $c->customer_id }}) {{ $c->customer->username }}</a></td>
				<td><a href="https://dashboard.stripe.com/customers/{{ $c->token }}" target="_blank">{{ $c->token }}</a></td>
				<td>
					@if($c->lastTopup)
					   &euro;{{ number_format($c->lastTopup->amount, 2) }} &horbar; ({{ Carbon\Carbon::parse($c->lastTopup->created_at)->diffForHumans() }})
					@else
						n/a 
					@endif
				</td>
				<td>{{ $c->created_at }} &horbar; ({{ Carbon\Carbon::parse($c->created_at)->diffForHumans() }})</td>
			</tr>
		@endforeach
	 </table>
	 </div>
	 
	 <div class="tab-pane" id="sources" style="">
	 <table width="100%" class="table table-bordered">
		<tr>
			<th width="10%"><b> Customer</b></th>
			<th width="15%"><b>	Token </b></th>
			<th width="25%"><b>	Type </b></th>
			<th width="50%"><b>	Created </b></th>
		</tr>
		@foreach($stripeSources as $s)
			<tr>
				<td><a href="/customer/{{ $s->customer_id }}" target="_blank">({{ $s->customer_id }}) {{ $s->customer->username }}</a></td>
				<td><a href="https://dashboard.stripe.com/cards/{{ $s->source_type_token }}" target="_blank">{{ $s->source_type_token }}</a></td>
				<td>
					{{ $s->type_br }}
				</td>
				<td>{{ $s->created_at }} &horbar; ({{ Carbon\Carbon::parse($s->created_at)->diffForHumans() }})</td>
			</tr>
		@endforeach
	 </table>
	 </div>
	 
	 <div class="tab-pane" id="logs" style="">
	 
	 <h4> Regular Logs </h4>
	  <table width="100%" class="table table-bordered">
		<tr>
			<th width="10%"><b> Type</b></th>
			<th width="70%"><b>	Log </b></th>
			<th width="10%"><b>	Created at </b></th>
		</tr>
		@foreach($stripeLogs as $l)
			<tr>
				<td> {{ $l->type }} </td>
				<td> {{ $l->logFormatted }} </td>
				<td> {{ $l->created_at }} &horbar; ({{ Carbon\Carbon::parse($l->created_at)->diffForHumans() }})</td>
			</tr>
		@endforeach
	 </table>
	 <hr/>
	  <h4> Error Logs </h4>
	  <table width="100%" class="table table-bordered">
		<tr>
			<th width="10%"><b> Type</b></th>
			<th width="70%"><b>	Log </b></th>
			<th width="10%"><b>	Created at </b></th>
		</tr>
		@foreach($stripeErrorLogs as $l)
			<tr>
				<td> {{ $l->type }} </td>
				<td> {{ $l->logFormatted }} </td>
				<td> {{ $l->created_at }} &horbar; ({{ Carbon\Carbon::parse($l->created_at)->diffForHumans() }})</td>
			</tr>
		@endforeach
	 </table>
			
	 </table>
	 </div>
	 
	 <div class="tab-pane" id="payments" style="">
	  <table width="100%" class="table table-bordered">
		<tr>
			<th width="10%"><b> Customer</b></th>
			<th width="15%"><b>	Token </b></th>
			<th width="25%"><b>	Amount </b></th>
			<th width="10%"><b>	Notified customer </b></th>
			<th width="50%"><b>	Created </b></th>
		</tr>
		@foreach($stripePayments as $p)
			<tr>
				<td><a href="/customer/{{ $p->customer_id }}" target="_blank">
				({{ $p->customer_id }}) {{ $p->customer->username }}
				</a></td>
				<td><a href="https://dashboard.stripe.com/payments/{{ $p->source_type_token }}" target="_blank">
				{{ $p->token }}
				</a></td>
				<td>
					&euro;{{ number_format($p->amount, 2) }}
				</td>
				<td>
					@if($p->notified_customer)
						Yes
					@else
						No
					@endif
				</td>
				<td>{{ $p->created_at }} &horbar; ({{ Carbon\Carbon::parse($p->created_at)->diffForHumans() }})</td>
			</tr>
		@endforeach
	 </table>
	 </div>
	 
	 <div class="tab-pane" id="failed_payments" style="">
	 <table width="100%" class="table table-bordered">
	 <tr>
			<th width="10%"><b> Customer</b></th>
			<th width="15%"><b>	Token </b></th>
			<th width="25%"><b>	Amount </b></th>
			<th width="10%"><b>	Notified customer </b></th>
			<th width="50%"><b>	Created </b></th>
		</tr>
		@foreach($stripeFailedPayments as $p)
			<tr>
				<td><a href="/customer/{{ $p->customer_id }}" target="_blank">
				({{ $p->customer_id }}) 
					@if($p->customer) {{ $p->customer->username }} @else n/a @endif
				</a></td>
				<td><a href="https://dashboard.stripe.com/payments/{{ $p->source_type_token }}" target="_blank">
				{{ $p->token }}
				</a></td>
				<td>
					&euro;{{ number_format($p->amount, 2) }}
				</td>
				<td>
					@if($p->notified_customer)
						Yes
					@else
						No
					@endif
				</td>
				<td>{{ $p->created_at }} &horbar; ({{ Carbon\Carbon::parse($p->created_at)->diffForHumans() }})</td>
			</tr>
		@endforeach
	 </table>
	 </div>
	 
    </div>

</div>