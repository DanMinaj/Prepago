@if (count($topups))
    <table class="table table-bordered sortthistable">
        <thead>
        <tr>
            <th width="25%" class="header">REF #</th>
            <th width="25%" class="header">Date/Time</th>
            <th width="10%" class="header">Amount</th>
            <th width="10%" class="header">VIA</th>
            <th width="20%" class="header">Balance change</th>
           @if(Auth::user()->isUserTest())
		   <th width="20%" class="header">Modify</th>
		   @endif
        </tr>
        </thead>
        <tbody>
        @foreach ($topups as $key => $topup)
            <tr>
                <td>{!! $topup['ref_number'] !!}</td>
                <td>{{ $topup['time_date'] }}</td>
                <td>{!! $currencySign !!} {{ $topup['amount'] }}</td>
                <td> {!! $topup['acceptor_name_location'] !!} </td>
				<td>
					@if($topup['balance_before'] == NULL || $topup['balance_after'] == NULL)
						<center>-</center>
					@else
						<center> {!! $topup['balance_before'] !!} -&gt; {!! $topup['balance_after'] !!} </center>
					@endif
				</td>
				<td>
					@if($topup['acceptor_name_location'] == 'stripe' && Auth::user()->isUserTest())
					<button type="button" amount="{!! $topup['amount'] !!}" ref_number="{!! $topup['ref_number'] !!}" class="btn btn-primary refund_btn" ype="button" >Refund</button>
					@else
					N/A
					@endif
				</td>
            </tr>
        @endforeach
        </tbody>
    </table>
@else
    <p>No Entries</p>
@endif
<div id="refund-payment" class="modal hide fade" >
   <div class="modal-header">
	  <h3 id="myModalLabel">Refund Stripe Payment</h3>
   </div>
	<div class="modal-body">
	<form action="/customer_tabview_controller/refund_payment/{!! $data['id'] !!}" method="POST">
	<input type="hidden" class="ref_number" name="ref_number">
	<input type="hidden" name="customer_id" value="{!! $data['id'] !!}">
	<table width="100%">
		<tr>
			<td width="50%"><b style="font-size: 1.5rem;">Partial refund</b></td>
			<td width="50%"><input style="    width: 30px;
    height: 30px;" name="partial_refund" class="partial_refund" type="checkbox">
	</td>
		</tr>
		<tr>
			<td width="50%"><b class="refund_amount_lbl" style="font-size: 1.5rem;display:none;">Refund amount</b></td>
			<td width="50%"><input style="display:none;" type="text" style="font-size:1.5rem" class="refund_amount" name="refund_amount" placeholder="&euro;0.00"></td>
		</tr>	
		<tr>
			<td width="50%"><b class="refund_reason_lbl" style="font-size: 1.5rem;">Reason</b></td>
			<td width="50%"><input type="text" style="" class="refund_reason" name="refund_reason" placeholder="e.g Customer requested"></td>
		</tr>			
	</table>
	</div>
<div class="modal-footer">
<button class="btn" data-dismiss="modal" aria-hidden="true">Cancel</button>
<button class="btn btn-primary" type="submit">Refund</button>
</form>
</div>
</div>
<script>
	$(function(){
			
		$('.refund_btn').on('click', function(){
			
			var ref = $(this).attr('ref_number');
			var amount = $(this).attr('amount');
			
			$('.refund_amount').val(amount);
			$('.ref_number').val(ref);
			
			 $('#refund-payment').modal('show');
		
		});
		
		$('.partial_refund').on('click', function(){
			var check = $('.partial_refund').is(':checked');
			if(check) {
				$('.refund_amount_lbl').show()
				$('.refund_amount').show()
			}
			else {
				$('.refund_amount_lbl').hide()
				$('.refund_amount').hide()
			}
		});
			
	});
</script>