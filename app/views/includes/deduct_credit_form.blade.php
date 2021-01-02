@if(isset($data))
	<form action="{{ URL::to('deduct_credit') }}" method="POST" class="well form-horizontal" id="deduct_credit_form"
	onSubmit="if(!confirm('You are about to deduct ' + document.getElementById('amount').value + ' from the customer\'s balance. Are you sure you would like to continue?')) {  return false; } "
	>
	   <input type="hidden" name="customer_id" value="{{ $data['id'] }}">
	   <fieldset>
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
		  <div class="form-actions">
			 <button type="submit" class="btn btn-primary">Deduct credit</button>
		  </div>
	   </fieldset>
	</form>
@else
	Customer data not found.
@endif