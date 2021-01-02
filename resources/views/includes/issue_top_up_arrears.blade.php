<form class="well form-horizontal" id="issue_credit_form" method="POST" action="{{ URL::to('/issue_topup_arrears/add_amount') }}">
		<fieldset>
			<div class="control-group">
				<label class="control-label" for="input01">Amount</label>
				<div class="controls">
					<input type="text" name="amount" class="input-xlarge" id="issue_topup_arrears_amount" value="">
				</div>
			</div>
			<div class="control-group">
				<label class="control-label" for="input01">Reason</label>
				<div class="controls">
					<textarea type="text" name="reason" class="input-xlarge" id="arrears_daily_repayment_reason"></textarea>
				</div>
			</div>
			<div class="control-group">
				<label class="control-label" for="arrears_daily_repayment">Arrears Daily Repayment</label>
				<div class="controls">
					<input type="text" name="arrears_daily_repayment" class="input-xlarge" id="arrears_daily_repayment" value="0.5">
				</div>
			</div>

			<div class="form-actions">
				<button type="submit" class="btn btn-primary">Issue top-up arrears</button>
			</div>	
		</fieldset>
</form>