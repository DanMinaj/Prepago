
</div>

<div><br/></div>
<h1>Paypoint Reports</h1>


<div class="admin2">


    <a href="{{ URL::to('system_reports') }}">System Reports</a> > Paypoint Reports
    

	<center>
	<h3><a href="">
	<button style='padding:1.5%;' type="button" data-toggle="modal" data-target="#sms_all"  class="sms-customer btn btn-warning"><i class="fa fa-comment"></i> SMS All</button>
	</a></h3>
	</center>

	<table class="table table-bordered">
        
		<tr>
			<td><b>Range</b></td><td>2018-07-18 - {{date('Y-m-d')}}</td>
		</tr>
		<tr>
			<td><b>Total No. Paypoint</b></td> <td>{{$total_paypoint_no}}</td>
		</tr>
		<tr>
			<td><b>Total &euro; Paypoint</b></td> <td>&euro;{{$total_paypoint_amt}}</td>
		</tr>
		
	</table>
	
	<table  id="paypoint-table"  class="table table-bordered" width="100%">
        
		<thead>
			<tr>
				<th><b>ID</b></th>
				<th><b>Scheme</b></th>
				<th><b>No. Paypoint</b></th>
				<th><b>&euro; Paypoint</b></th>
				<th><b>Send SMS</b></th>
				<th><b>SMS Feedback</b></th>
			</tr>
		</thead>
		
		<tbody>
		@foreach($most_recent_paypoint as $recent_paypoint_customer)
	
		<tr>
			<td width="10%">
			
			<a href="http://prepago-admin.biz/customer_tabview_controller/show/{{$recent_paypoint_customer['customer']->id}}" target="_blank">{{$recent_paypoint_customer['customer']->first_name}} {{$recent_paypoint_customer['customer']->surname}} ({{$recent_paypoint_customer['customer']->id}})</a></td>
			
			<td width="10%">
			@if(!empty($recent_paypoint_customer['customer']->scheme()))
				{{ $recent_paypoint_customer['customer']->scheme()->first()->company_name }}
			@endif
			</td>
			
			<td width="10%">
				{{ $recent_paypoint_customer['customer']->countPayments('paypoint', '2018-07-18') }}
			</td>
			
			<td width="10%">
				&euro;{{ $recent_paypoint_customer['customer']->sumPayments('paypoint', '2018-07-18') }}
			</td>
			
			<td width="10%">
				<button style='text-overflow: ellipsis; max-width: 130px; white-space: nowrap; overflow: hidden;' type="button" data-toggle="modal" data-target="#sms_customer" data-customer="{{$recent_paypoint_customer['customer']->id}}"  data-customer="{{$recent_paypoint_customer['customer']->scheme_number}}" class="sms-customer btn btn-primary"><i class="fa fa-comment"></i> SMS</button>
			</td>
			
			<td width="10%">
				<span class="customer-{{$recent_paypoint_customer['customer']->id}}">n/a</span>
			</td>
		</tr>
		
		@endforeach
		</tbody>
		
	</table>

</div>

<div id="sms_customer" class="modal fade" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
		
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal">&times;</button>        
			<h4 class="modal-title">SMS Customer </h4>
		</div>
		
		<div class="modal-body">
			
			<form action="">
				
				<table width="100%">
					<tr>
						<td>
							<input type="hidden" name="sms_customer" value="">
						</td>
					</tr>
					<tr>
						<td>
							<textarea name="sms_content" style="width: 513px;height: 80px;"></textarea>
						</td>
					</tr>
					
					<tr>
						<td>
						<center>
							<button type="button" style='padding:5%' id="sms_submit" class="btn btn-primary"><i class="fa fa-paper-plane"></i> Send</button>
						</center>
						</td>
					</tr>
					
				</table>
				
			</form>
			
		</div>
		  
		<div class="modal-footer">
			<button type="submit" class="btn btn-default" data-dismiss="modal">Close</button>
		</div>
		
    </div>

  </div>
</div>

<div id="sms_all" class="modal fade" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
		
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal">&times;</button>        
			<h4 class="modal-title">SMS All </h4>
		</div>
		
		<div class="modal-body">
			
			<center>
				<b>*</b> {{count($most_recent_paypoint)}} customers will receive this SMS. <b>*</b>
			</center>
			
			<form action="">
				
				<table width="100%">
					
					<tr>
						<td>
							<textarea name="sms_content_all" style="width: 513px;height: 80px;"></textarea>
						</td>
					</tr>
					
					<tr>
						<td>
						<center>
							<button type="button" style='padding:5%' id="sms_submit_all" class="btn btn-primary"><i class="fa fa-paper-plane"></i> Send</button>
						</center>
						</td>
					</tr>
					
				</table>
				
			</form>
			
		</div>
		  
		<div class="modal-footer">
			<button type="submit" class="btn btn-default" data-dismiss="modal">Close</button>
		</div>
		
    </div>

  </div>
</div>

<script>
	$(function(){
		
		var customer_id;
		var sms_msg;
		var customer_scheme;
		
		 $('#paypoint-table').DataTable();
		
		$('.sms-customer').on('click', function(){
			
			customer_id = $(this).attr("data-customer");
			customer_scheme = $(this).attr("data-scheme");
			
			$("input[name='sms_customer']").val(customer_id);
			
		});
		
		
		
		$('#sms_submit').on('click', function(){
			
			var sms_content = $("textarea[name='sms_content']").val();
			
			$('#sms_customer').modal('hide');
			
			$("textarea[name='sms_content']").val("");
			
			$.ajax({
				
				url: "http://prepago-admin.biz/prepago_admin/sms/user_specific_message/"+customer_id+"/"+customer_scheme+"/J92NSOPS9Sb9S/"+sms_content+"",
				type: "GET",
				
			}).done(function(data){
				
				$.notify("The following SMS was sent to Customer " + customer_id + ": " + '"' + sms_content + '"', "success");
			
			});
		});
		
		$('#sms_submit_all').on('click', function(){
			
			var sms_content = $("textarea[name='sms_content_all']").val();

			$('#sms_all').modal('hide');
			
			$("textarea[name='sms_content_all']").val("");
			
			@foreach($most_recent_paypoint as $customer)	
			
				$.ajax({
				
					url: "http://prepago-admin.biz/prepago_admin/sms/user_specific_message/{{$customer['customer']->id}}/{{$customer['customer']->scheme_number}}/J92NSOPS9Sb9S/"+sms_content+"",
					type: "GET",
				
				}).done(function(data){
					
					$('.customer-{{$customer['customer']->id}}').html('<center><i class="fa fa-comment"></i> SMS delivered</center>');
					console.log("Sent SMS to customer {{$customer['customer']->id}}");
				});
			
				
			@endforeach
			
			$.notify("The following SMS was sent to {{count($most_recent_paypoint)}} customers: " + '"' + sms_content + '"', "success");
			
			
		});
		
	});
</script>
