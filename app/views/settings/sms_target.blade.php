
</div>

<div><br/></div>
<h1>SMS Tagret</h1>


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

	
	 
   <ul class="nav nav-tabs" style="margin: 30px 0">
       <li class="active"><a href="#old_app_users" data-toggle="tab">Old App Users ({{ count($old_app_customers) }})</a></li>
       <li><a href="#paypal_users" data-toggle="tab">Paypal Customers ({{ (count($paypal_customers)) }})</a></li>
   </ul>
   
   <div class="tab-content">
   
		<div class="tab-pane active" id="old_app_users" style="text-align: left">	
		
		
			<form action="" method="POST" onsubmit="return confirm('You are about to send the SMS: \n\n\'' + ($('#old_app_customers_msg').val()) + '\'\n\n to {{ count($old_app_customers) }} customers. Are you sure?');">
			
			<table width="100%">
				<tr>
					<td style="vertical-align:top;" width="80%">
						<input type="hidden" name="type" value="old_app_customers">
						<textarea name="message" style="width:95%;" id="old_app_customers_msg" placeholder="SMS Message"></textarea>
					</td>
					<td style="vertical-align:top;" width="20%">
						<button class="btn btn-primary" type="submit">Send SMS</button>
					</td>
				</tr>
			</table>
			
			<table width="100%" class="table table-bordered">
				<tr>
					<th> <b> ID </b> </th>
					<th> <b> Customer </b> </th>
					<th> <b> Last login </b> </th>
					@if($sent_to = Session::get('sent_to'))
					<th> <b> Sent SMS </b> </th>
					@endif
				</tr>
				@foreach($old_app_customers as $k => $c)
					<tr>
						<td> {{ $c->id }} </td>
						<td>
							<a href="/customer/{{ $c->id }}">{{ $c->username }}</a>
						</td>
						<td>
							{{ $c->last_login }}
						</td>
						@if($sent_to = Session::get('sent_to'))
						<td> 
						<center>
							@if(in_array($c->id, $sent_to))
								<i style="color:green;" class="fa fa-check"></i>
							@else
								<i style="color:red;" class="fa fa-times"></i>
							@endif
						</center>
						</td>
						@endif
					</tr>
				@endforeach
			</table>
			
			</form>
			
		</div>
		
		<div class="tab-pane" id="paypal_users" style="text-align: left">	
		
		
		
			<form action="" method="POST" onsubmit="return confirm('You are about to send the SMS: \n\n\'' + ($('#paypal_customers_msg').val()) + '\'\n\n to {{ count($paypal_customers) }} customers. Are you sure?');">
			
			<table width="100%">
				<tr>
					<td style="vertical-align:top;" width="80%">
						<input type="hidden" name="type" value="paypal_customers">
						<textarea name="message" id="paypal_customers_msg" style="width:95%;" placeholder="SMS Message"></textarea>
					</td>
					<td style="vertical-align:top;" width="20%">
						<button class="btn btn-primary" type="submit" >Send SMS</button>
					</td>
				</tr>
			</table>
			
			<table width="100%" class="table table-bordered">		
				<tr>
					<th> <b> ID </b> </th>
					<th> <b> Customer </b> </th>
					<th> <b> Last paypal topup </b> </th>
					@if($sent_to = Session::get('sent_to'))
					<th> <b> Sent SMS </b> </th>
					@endif
				</tr>
				@foreach($paypal_customers as $k => $c)
					<tr>
						<td> {{ $c->id }} </td>
						<td>
							<a href="/customer/{{ $c->id }}">{{ $c->username }}</a>
						</td>
						<td>
							{{ $c->last_topup }}
						</td>
						@if($sent_to = Session::get('sent_to'))
						<td> 
						<center>
							@if(in_array($c->id, $sent_to))
								<i style="color:green;" class="fa fa-check"></i>
							@else
								<i style="color:red;" class="fa fa-times"></i>
							@endif
						</center>
						</td>
						@endif
					</tr>
				@endforeach
			</table>
			
			</form>
			
		</div>
	
	</div>
  
</div>
