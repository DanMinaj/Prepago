<form class="well form-horizontal" id="issue_credit_form">
	@if ($type == 'issue_admin_iou')
		
	
		<a href="/issue_admin_iou/issue/{!! $data['id'] !!}">
        <button type="button" 
		class="btn btn-lg btn-primary btn-block" 
		style="width: 300px; margin: 0 auto;">{!! $iouAvailable  ? 'IOU Available' : 'IOU Unavailable' !!}</button>
		</a>
    @else
		<fieldset>
			<div class="control-group">
				<label class="control-label" for="input01">Amount</label>
				<div class="controls">
					{!! Form::text('amount', null, ['id' => $type . '_amount', 'class' => 'input-xlarge']) !!}
				</div>
			</div>
			<div class="control-group">
				<label class="control-label" for="input01">Reason</label>
				<div class="controls">
					<textarea type="text" class="input-xlarge" id="{!! $type !!}_reason"></textarea>
				</div>
			</div>

			@if ($type == 'issue_topup_arrears')
				<div class="control-group">
					<label class="control-label" for="arrears_daily_repayment">Arrears Daily Repayment</label>
					<div class="controls">
						{!! Form::text('arrears_daily_repayment', 0.5, ['id' => 'arrears_daily_repayment', 'class' => 'input-xlarge']) !!}
					</div>
				</div>
			@endif
			
			<?php
			
				
			?>

			<div class="form-actions">
				<a href="#topupModal-{!! $type !!}" onclick="topup('/{!! $type !!}/add_amount', '{!! $type !!}')" class="btn btn-primary" >Issue credit</a>
			</div>
		</fieldset>
	@endif	
</form>

<div id="topupModal-{!! $type !!}" class="modal hide fade" >
    <div class="modal-header">
        <h3 id="myModalLabel">{!! $modalTitle !!}</h3>
    </div>
    <div class="modal-body">
        <form class="form-horizontal" id="admin_pass_form" action="javascript:;" onsubmit="return topup('{!! $addAmountURL !!}', '{!! $type !!}');">
            <div class="form-group" role="form">
                <label for="inputEmail1" class="control-label">Admin Password: </label>
                <div>
                    <input type="password" class="form-control" id="password-{!!$type!!}" placeholder="Password">
                    <input type="hidden" class="form-control" id="base" value="<?php echo URL::to('/'); ?>">
                </div>
            </div>
        </form>
        <div id="alert-{!!$type!!}"  class= "alert alert-error" style="visibility: hidden;">Wrong Password</div>
    </div>
    <div class="modal-footer">
        <button class="btn" data-dismiss="modal" aria-hidden="true">Cancel</button>
        <a href="#" class="btn btn-danger" onclick="topup('{!! $addAmountURL !!}', '{!! $type !!}')">Yes</a>
    </div>
</div>