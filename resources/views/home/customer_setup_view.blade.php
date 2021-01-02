</div>

<div class="cl"></div>
<h1>Customer Set up</h1>
<div class="cl"></div>

@include('includes.notifications')

@if(Auth::user()->scheme->isBlueScheme)
<div class="alert alert-info alert-block">
	<b>Note: </b> You are setting up a bill-pay only customer as this Scheme is dedicated to such.
</div>
@endif

{!! Form::open(['url' => 'open_account/action', 'id' => 'customer_set_up']) !!}

	<div class="custome_left" style="position:float-right;">


    	<!-- <label id="warning" style="text-transform:none;color: black">Please fill up all '*' marked fields </label>
    	<form id="form1" name="form1" method="post" action='<?php echo URL::to('open_account/action') ?>' onsubmit="return validateForm()"> -->

    	{!! Form::hidden('selected_unit', $customerToSwap ? $customerToSwap->meter_ID : '', ['id' => 'selected_unit']) !!}
		{!! Form::hidden('selected_role', 'normal', ['id' => 'selected_role']) !!}
		@if ($customerToSwap)
			{!! Form::hidden('swap_from_id', $customerToSwap->id) !!}
			{!! Form::hidden('balanceToSwap', $customerToSwap->balance, ['id' => 'balanceToSwap']) !!}
		@endif

		@if (!$customerToSwap)
			<div class="control-group{!! $errors->has('selected_unit') ? ' error' : '' !!}">
				<label>Available Units*<br/>
				<span style='font-size: 12px; cursor: pointer; color: #95d10b; padding-top: 4px; padding-bottom: 4px; border-top: 1px solid #e5e5e5; border-bottom: 1px solid #e5e5e5;'  data-toggle="modal" data-target="#missing-apartment">Missing apartment?</span></label>
				<select id="prepop" name="select_units">
					<option id="" value="">&nbsp;</option>
					<?php foreach($usernames as $user){ ?>
						<option id="<?php echo $user->username; ?>" value="<?php echo $user->meter_number; ?>">
						<?php echo $user->username; ?> 
						
						<?php /*echo $user->house_name_number . ' ' . $user->street1;*/ ?>

						@if(substr($user->username, 0, 1) == 'h') (*Fairways Hall) @endif
						</option>
					<?php } ?>
				</select>
			</div>
		@else
			<div class="control-group">
				<label>{!! Form::checkbox('swap_credit', 1, true, ['id' => 'swap_credit', 'onclick' => 'javascript: swapCredit()']) !!} Would you like to swap the credit?</label>
			</div>
		@endif
        
        <div class="control-group{!! $errors->has('username') ? ' error' : '' !!}">
	        {!! Form::label('username', 'Username or Account Number*') !!}
	        {!! Form::text('username', $customerToSwap ? $customerToSwap->username : null, ['id' => 'username', 'class' => 'cus-in', 'readonly' => true]) !!}
	    </div>   
        
        <div class="control-group{!! $errors->has('balance') ? ' error' : '' !!}">
        	{!! Form::label('balance', 'Starting Balance') !!}
        	{!! Form::text('balance', Input::old('balance'), ['class' => 'cus-in', 'id' => 'balance']) !!}
        </div>
        
        <div class="control-group{!! $errors->has('first_name') ? ' error' : '' !!}">
	        {!! Form::label('first_name', 'First Name*') !!}
    	    {!! Form::text('first_name', Input::old('first_name'), ['id' => 'firstname', 'class' => 'cus-in']) !!}
    	</div>
       	
       	<div class="control-group{!! $errors->has('surname') ? ' error' : '' !!}">
       		{!! Form::label('surname', 'Last Name*') !!}
        	{!! Form::text('surname', Input::old('surname'), ['id' => 'surname', 'class' => 'cus-in']) !!}
        </div>   
        
		
		@if(Auth::user()->scheme->isBlueScheme)
			
		@else
        <div class="control-group{!! $errors->has('arrears') ? ' error' : '' !!}">
       		{!! Form::label('arrears', 'Arrears') !!}
        	{!! Form::text('arrears', Input::old('arrears'), ['id' => 'arrears', 'class' => 'cus-in']) !!}
        </div>   
        
        <div class="control-group{!! $errors->has('arrears_daily_repayment') ? ' error' : '' !!}">
       		{!! Form::label('arrears_daily_repayment', 'Arrears Daily Repayment') !!}
        	{!! Form::text('arrears_daily_repayment', Input::old('arrears_daily_repayment'), ['id' => 'arrears_daily_repayment', 'class' => 'cus-in']) !!}
        </div>       	
       	@endif
		
	</div>

	<div class="custome_left"><br /></div>

	<div class="custome_left">
		<div class="control-group{!! $errors->has('email_address') ? ' error' : '' !!}">
			{!! Form::label('email_address', 'Email Address*') !!}
        	{!! Form::text('email_address', Input::old('email_address'), ['id' => 'email_address', 'class' => 'cus-in']) !!}
        </div>
        
        <div class="control-group{!! $errors->has('mobile_number') ? ' error' : '' !!}">
        	{!! Form::label('mobile_number', 'Mobile Number*') !!}
        	{!! Form::text('mobile_number', Input::old('mobile_number'), ['id' => 'mobile_number', 'class' => 'cus-in', 'placeholder' => '+3538x xxx xxxx']) !!}
        </div>
        
        <div class="control-group{!! $errors->has('nominated_telephone') ? ' error' : '' !!}">
        	{!! Form::label('nominated_telephone', 'Nominated Mobile Number') !!}
        	{!! Form::text('nominated_telephone', Input::old('nominated_telephone'), ['id' => 'nominated_telephone', 'class' => 'cus-in']) !!}
       	</div>
        
         <div class="control-group{!! $errors->has('commencement_date') ? ' error' : '' !!}">
        	{!! Form::label('commencement_date', 'Commencement Date*') !!}
        	{!! Form::text('commencement_date', Input::old('commencement_date'), ['id' => 'datepicker', 'class' => 'cus-in']) !!}
        </div>

		@if(Auth::user()->scheme->isBlueScheme)
			<input type="hidden" name="role" value="normal">
		@else
		<div class="control-group{!! $errors->has('role') ? ' error' : '' !!}">
			<label>Customer Type*</label>
			<select id="role" name="role">
				<option value="normal">Normal Customer</option>
				<option value="landlord">Landlord</option>
			</select>
		</div>
		@endif
        
        <br /><br />
		<button class="btn btn-success" style="padding:4% 25% 4% 25%;" type="submit" name="submit" value="submit"><i class="fas fa-user-plus"></i> Submit</button>

        <!--<input name="" id="submit" type="image" src="<?php echo URL::to('/') ?>/resources/images/submit.png" />-->
        
		@if(Auth::user()->isUserTest())
		<br /><br />
        <button class="btn btn-warning" style="padding:4% 25% 4% 25%;" name="submit" value="queue"><i class="fas fa-tasks"></i> Queue</button>
		@endif

	</div>
	
	<input type="hidden" id="base" value="<?php echo URL::to('/'); ?>">
	
</form>

</div>
</div>

<script type="text/javascript">
     $(document).ready(function() {

		$('form#customer_set_up').submit(function(){
			$("#submit").attr('disabled', 'disabled');
		});
	 
		 if ($("#swap_credit").length)
		 {
			 swapCredit();
		 }

		//if redirected with errors -> check if selected_unit was selected and populate the select box with default value
		if ($("#selected_unit").val()) {
			$("#prepop option").each(function() {
				if ($(this).val() === $("#selected_unit").val()) {
					$(this).attr('selected', 'selected');
				} 
			});
		}

		 //if redirected with errors -> check if role was selected and populate the select box with default value
		 if ($("#selected_role").val()) {
			 $("#role option").each(function() {
				 if ($(this).val() === $("#selected_role").val()) {
					 $(this).attr('selected', 'selected');
				 }
			 });
		 }


        $( "#datepicker" ).datepicker({ dateFormat: 'yy-mm-dd', minDate: 1 });
        
        $('#prepop').change(function(){
            var prepop = $('#prepop').val();
          	//populate the hidden field with the selected value -> needed for the validation
			$("#selected_unit").val(prepop);                    
            $('#username').val($('#prepop').find(":selected").attr("id"));
        });

		 $('#role').change(function(){
			 var role = $('#role').val();
			 //populate the hidden field with the selected value -> needed for the validation
			 $("#selected_role").val(role);
		 });
     });

	 function swapCredit()
	 {
		 if ($("#swap_credit").is(':checked'))
		 {
			 $("#balance").val($("#balanceToSwap").val()).attr('disabled', 'disabled');
		 }
		 else
		 {
			 $("#balance").val('').attr('disabled', false);
		 }
	 }
   
</script>
@include('modals.missing_apartment')