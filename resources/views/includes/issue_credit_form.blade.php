@if(isset($data))
	<form action="{!! URL::to('issue_credit') !!}" method="POST" class="well form-horizontal" id="issue_credit_form"
	onSubmit="if(!confirm('You are about to issue ' + document.getElementById('amount').value + ' to the customer\'s balance. Are you sure you would like to continue?')) {  return false; } "
	>
	   <input type="hidden" name="customer_id" value="{!! $data['id'] !!}">
	   <fieldset>
			<div class="row-fluid">
			
			
				<div class="span7">

				  <div class="control-group">
					 <label class="control-label" for="input01">Amount</label>
					 <div class="controls">
						<input id="amount" type="text" class="input-xlarge" name="amount">
					 </div>
				  </div>
				  <div class="control-group">
					 <label class="control-label" for="input01">Reason</label>
					 <div class="controls">
						<textarea type="text" class="input-xlarge" name="reason"></textarea>
					 </div>
				  </div>
					<div class="control-group">
					<label class="control-label" for="input01">REF # (optional)</label>
					 <div class="controls">
						<input type="text" class="input-xlarge" name="ref_number">
					 </div>
					</div>
				  <div class="control-group">
					 <label class="control-label" for="input01">Time (optional)</label>
					 <div class="controls">
						<textarea type="text" class="input-xlarge" name="time"></textarea>
					 </div>
				  </div>
				  <div class="form-actions">
					 <button type="submit" class="btn btn-primary">Issue credit</button>
				  </div>
				</div>
				
				<div class="span4 offset1">
				<div class='row-fluid'>
				<div>
					<h4> Choose preset reason </h4>
					<label><input type="radio" name="reason_preset"> <span>Customer not credited after Paypal top-up</span> </label>
					<label><input type="radio" name="reason_preset"> <span>Customer was overcharged</span> </label>
				</div>
				</div>
				<div class='row-fluid'>
				<div>
					<h4> Choose preset amount </h4>
					<label><input type="radio" name="amount_preset"> {!! $currency !!}<span>10.00</span> </label>
					<label><input type="radio" name="amount_preset"> {!! $currency !!}<span>25.00</span> </label>
					<label><input type="radio" name="amount_preset"> {!! $currency !!}<span>50.00</span> </label>
					<label><input type="radio" name="amount_preset">	{!! $currency !!}<span>100.00</span> </label>
				</div>
				</div>
				</div>
			
			
			</div>
	   </fieldset>
	</form>
@else
	Customer data not found.
@endif
<script>
	$("input[name='reason_preset']").on('click', function(){
		var preset_reason = $(this).next().html();
		$("textarea[name='reason']").val(preset_reason);
	});
	$("input[name='amount_preset']").on('click', function(){
		var preset_amount = $(this).next().html();
		$("input[name='amount']").val(preset_amount);
	});
</script>