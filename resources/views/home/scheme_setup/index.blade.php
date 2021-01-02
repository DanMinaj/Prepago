</div>
<div class="cl"></div>

<h1>Scheme Set Up</h1>

<div class="admin2">

    
    @if ($message = Session::get('successMessage'))
        <div class="alert alert-success alert-block">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            {{ $message }}
        </div>
    @endif

	<div style="display:none" class="error_box alert alert-danger alert-block">
		<button type="button" class="close" data-dismiss="alert">&times;</button>
		<div class="error_msg">
		</div>
	</div>
    
   <ul class="nav nav-tabs" style="margin: 30px 0">
    
	
      <li id="s_1_1" class="active"><a id="s_1" href="#1" data-toggle="tab">Step 1 - Scheme creation</a></li>
      <li id="s_2_2" disabled="true"><a id="s_2" href="#2">Step 2 - Account creation</a></li>
      <li id="s_3_3" disabled="true"><a id="s_3" href="#3">Step 3 - Datalogger creation</a></li>

	
	  
	  
   </ul>
   
     
   <div class="tab-content">
    
		<div class="tab-pane active" id="1" style="text-align: left">
			
			<div class="alert alert-info alert-block">
			
				<i class="fa fa-info-circle"></i> Creating the Scheme. Please ensure you do not leave <b>any</b> fields blank. The scheme number & type fields are <b>disabled</b> as they are automatically set.
				
			</div>
			
				<div class="pull-right">
					<button type="button" name="button_submit" class="btn btn-primary" id="step_1_submit">Continue &gt;&gt;</button>
				
				</div>
				
			
			
			<table width="100%">
			
				<tr>
					
					<td width="20%" style="vertical-align:top;">
					
						
					
						<label for="scheme_number">Number</label>
						<input class="step1" type="text" id="scheme_number" value="{{ $newSchemeId }}" disabled name="scheme_number">
					
						<label for="scheme_type">Type</label>
						<input class="step1"  type="text" id="scheme_type" value="heating" disabled name="scheme_type">
						
						<label for="company_name">Name</label>
						<input class="step1"  type="text" placeholder="i.e SundayWell" id="company_name" value="" name="company_name">

						<label for="scheme_nickname">Nickname</label>
						<input class="step1"  type="text" placeholder="i.e SundayWell" id="scheme_nickname" value="" name="scheme_nickname">
					

						<input class="step1"  type="hidden" id="status_ok" value="1" name="status_ok">
						<input class="step1"  type="hidden" id="status_checked" value="<?php echo date('Y-m-d H:i:s'); ?>" name="status_checked">
						<input class="step1"  type="hidden" id="service_type" value="1" name="service_type">
						<input class="step1"  type="hidden" id="IOU_extra_amount" value="5" name="IOU_extra_amount">
						<input class="step1"  type="hidden" id="IOU_extra_charge" value="0.2" name="IOU_extra_charge">
						<input class="step1"  type="hidden" id="IOU_extra_text" value="You can avail of a €5 IOU extra. A 20c service charge applies." name="IOU_extra_text">
						
						<label for="scheme_description">Description</label>
						<input class="step1"  type="text" id="scheme_description" value="This is for ..." name="scheme_description">
					
						<label for="sms_password">SMS Password</label>
						<input class="step1"  type="text" id="sms_password" value="N/A" name="sms_password">
					
						<label for="accounts_email">Accounts email</label>
						<input class="step1"  type="text" id="accounts_email" value="aidan@prepago.ie" name="accounts_email">
						
						
						
					</td>
					
					
					<td width="20%" style="vertical-align:top;">
					
						
						<label for="vat_rate">VAT rate</label>
						<input class="step1"  type="text" id="vat_rate" value="0.135" name="vat_rate">
						
						<label for="currency_code">Currency code</label>
						<input class="step1"  type="text" id="currency_code" value="978" name="currency_code">
						
						<label for="currency_sign">Currency sign</label>
						<input class="step1"  type="text" id="currency_sign" value="&euro;" name="currency_sign">
						
						<label for="daily_customer_charge">Daily customer charge</label>
						<input class="step1"  type="text" id="daily_customer_charge" value="0.05" name="daily_customer_charge">
						
						<label for="commission_charge">Commission charge</label>
						<input class="step1"  type="text" id="commission_charge" value="0.07" name="commission_charge">
						
						<label for="prepago_registered_apps_charge">Prepago registered apps charge</label>
						<input class="step1"  type="text" id="prepago_registered_apps_charge" value="3" name="prepago_registered_apps_charge">
						
						<label for="IOU_chargeable">IOU Chargeable</label>
						<input class="step1"  type="text" id="IOU_chargeable" value="0" name="IOU_chargeable">
						
						<label for="IOU_amount">IOU amount</label>
						<input class="step1"  type="text" id="IOU_amount" value="5" name="IOU_amount">
						
						<label for="IOU_charge">IOU charge</label>
						<input class="step1"  type="text" id="IOU_charge" value="0.15" name="IOU_charge">
						
						<label for="IOU_text">IOU text</label>
						<input class="step1"  type="text" id="IOU_text" value="You can avail of a €5 IOU. A 15c service charge applies." name="IOU_text">
						
						<label for="prepage_SMS_charge">Prepago SMS charge</label>
						<input class="step1"  type="text" id="prepage_SMS_charge" value="0.08" name="prepage_SMS_charge">
						
						<label for="prepago_new_admin_charge">Prepago New Admin charge</label>
						<input class="step1"  type="text" id="prepago_new_admin_charge" value="100" name="prepago_new_admin_charge">
						
						<label for="prepago_in_app_message_charge">Prepago In-App Message charge</label>
						<input class="step1"  type="text" id="prepago_in_app_message_charge" value="0.03" name="prepago_in_app_message_charge">
					
					</td>
					
					
					<td width="20%" style="vertical-align:top;">
						
						<label for="prefix">Prefix</label>
						<input class="step1"  type="text" id="prefix" value="scheme_" name="prefix">					
										
						<label for="company_address">Address</label>
						<input class="step1"  type="text" id="company_address" value="" name="company_address">
						
						<label for="prefix">County</label>
						<input class="step1"  type="text" id="county" value="Dublin" name="county">
						
						<label for="prefix">Town</label>
						<input class="step1"  type="text" id="town" value="Dublin" name="town">
					
						<label for="prefix">Street 2</label>
						<input class="step1" type="text" placeholder="i.e honeypark" id="street2" value="" name="street2">
						
						<label for="prefix">Postcode</label>
						<input class="step1"  type="text" id="post_code" value="111100" name="post_code">
					
						<label for="country">Country</label>
						<input class="step1"  type="text" id="country" value="Ireland" name="country">
					
					
					
					</td>
					
					<td width="20%" style="vertical-align:top;">
						
						
						<label for="unit_abbreviation">Unit abbreviation</label>
						<input class="step1"  type="text" id="unit_abbreviation" value="kWh" name="unit_abbreviation">
					
						<label for="scu_type">SCU type</label>
						<input class="step1"  type="text" id="scu_type" value="m" name="scu_type">
					
						
					</td>
				
				</tr>
			
			
			</table>
			
		</div>
		
		
		
		<div class="tab-pane" id="2" style="text-align: left">
			
			<div class="alert alert-info alert-block">
			
				<i class="fa fa-info-circle"></i> <span class="step-2-msg"></span>
			
				
			</div>
			
			
			
			<div class="pull-right">
				
				<button type="button" name="button_submit" class="btn btn-primary" id="step_2_submit">Continue &gt;&gt;</button>
			
			</div>
			
			<table width="100%">
			
				<tr>	

				
					<td width="70%" style="vertical-align:top;">	
						
						<h3> * Installer account </h3>

						<label for="installer_username">Username</label>
						<input autocomplete="off" class="step2" type="text" id="installer_username" value="" name="installer_username">
						
						<label for="installer_password">Password</label>
						<input autocomplete="off" class="step2" type="password" id="installer_password" value="" name="installer_password">
						
						<label for="installer_group">Group</label>
						<input autocomplete="off" class="step2" disabled="1" type="text" id="installer_group" value="5" name="installer_group">
						
						<label for="installer_isInstaller">isInstaller</label>
						<input autocomplete="off" class="step2" disabled="1" type="text" id="installer_isInstaller" value="1" name="installer_isInstaller">
						
					</td>
					
					<td width="30%" style="vertical-align:top;">	
						
						<h3> Operator account (Optional) </h3>
						
						<label for="op_employee_name">Employee name</label>
						<input autocomplete="off" class="step2" type="text" id="op_employee_name" value="" name="op_employee_name">
						
						<label for="op_username">Username</label>
						<input autocomplete="off" class="step2" type="text" id="op_username" value="" name="op_username">
						
						<label for="op_password">Password</label>
						<input autocomplete="off" class="step2" type="password" id="op_password" value="" name="op_password">
				
						<label for="op_email_address">Email</label>
						<input autocomplete="off" class="step2" type="email" id="op_email_address" value="" name="op_email_address">
	
						<label for="op_group">Group</label>
						<select id="op_group" class="step2" name="op_group">
							<option value="1">Group 1</option>
							<option value="2">Group 2</option>
							<option value="6">Group 3</option>
							<option value="3">Group 4</option>
							<option value="4">Group 5</option>
							<option value="5">Installer</option>
						</select>
						
						<label for="op_isInstaller">isInstaller</label>
						<select disabled id="op_isInstaller" class="step2" name="op_isInstaller">
							<option value="0" selected="selected">no</option>
							<option value="1">yes</option>
						</select>
						
					</td>
					
					
				</tr>
				
			</table>
		</div>
		
		
		
		<div class="tab-pane" id="3" style="text-align: left">
			
			<div class="alert alert-info alert-block">
			
				<i class="fa fa-info-circle"></i> Creating the Datalogger. This will create the sim cards associated with them automatically too. <br/><br/>
				<span class="step-3-msg"></span>
				
			</div>
			
			<div class="pull-right">
				
				<button type="button" name="button_submit" class="btn btn-primary" id="step_3_submit">Continue &gt;&gt;</button>
			
			</div>
			
			<table width="100%">
			
				<tr>	

				
					<td width="50%" style="vertical-align:top;">	
						
						<h3> Elvaco device </h3>
						
						<label for="ICCID">ICCID</label>
						<input autocomplete="off" class="step3" type="text" id="ICCID" value="" name="ICCID">
						
						<label for="MSISDN">MSISDN</label>
						<input autocomplete="off" class="step3" type="text" id="MSISDN" value="" name="MSISDN">
						
						<label for="IP_Address">IP Address</label>
						<input autocomplete="off" class="step3" type="text" id="IP_Address" value="" name="IP_Address">
						
						<label for="scu_last8">SCU Last 8</label>
						<input autocomplete="off" class="step3" type="text" id="scu_last8" value="96152800" name="scu_last8">
						
						<label for="meter_last8">Meter Last 8</label>
						<input autocomplete="off" class="step3" type="text" id="meter_last8" value="D3100204" name="meter_last8">
						
						
					</td>
					
					<td width="50%" style="vertical-align:top;">	
						
					
						<h3> Scheme controller device </h3>
						
						<label for="c_ICCID">ICCID</label>
						<input autocomplete="off" class="step3" type="text" id="c_ICCID" value="" name="c_ICCID">
						
						<label for="c_MSISDN">MSISDN</label>
						<input autocomplete="off" class="step3" type="text" id="c_MSISDN" value="" name="c_MSISDN">
						
						<label for="c_IP_Address">IP Address</label>
						<input autocomplete="off" class="step3" type="text" id="c_IP_Address" value="" name="c_IP_Address">
						
						
					</td>
					
				</tr>
				
			</table>
			
		</div>
		
	</div>
   
   
   
   <script>
		
			$(function() {
				
				var step_1_submit = $('#step_1_submit');
				var step_2_submit = $('#step_2_submit');
				var step_3_submit = $('#step_3_submit');
				
				step_1_submit.on('click', function(){
					
					var step_1_array = {};
					
					$.each($('input[class=step1]'),function(){
						
						var key = $(this).attr('name');
						var value = $(this).val();
						
						step_1_array[key] = value;
						
						//console.log("Key: " + key + "\nValue: " + step_1_array[key] + "\n\n"); 
					  
					});
					
					
					$.ajax({
						
						url: "{{ URL::to('scheme-setup/step1') }}",
						data: {
							step_1_vars: step_1_array
						},
						type: "POST",
						
					}).done(function(data){
						
						if(data.indexOf("Error") === -1) {
							
							$('#s_1').css({
								
								background: '#3fb103',
								color: 'white',
								
							});
							
							  $('.error_box').hide();
							  $('#s_1').removeAttr('data-toggle');
							  $('#s_2_2').removeAttr('disabled');
							  $('#s_2').attr('data-toggle', 'tab');
							  $('#s_2').trigger('click');
							 
								$('.step-2-msg').html(data); 
							  
							
						} else {
							
							
							$('.error_msg').html(data);
							$('.error_box').show();
							
							
						}
						
					});
					
				});
				
				step_2_submit.on('click', function(){
					
					var step_2_array = {};
					
					$.each($('input[class=step2]'),function(){
						
						var key = $(this).attr('name');
						var value = $(this).val();
						
						step_2_array[key] = value;
						
						//console.log("Key: " + key + "\nValue: " + step_1_array[key] + "\n\n"); 
					  
					});
					
					$.each($('select[class=step2]'),function(){
						
						var key = $(this).attr('name');
						var value = $(this).val();
						
						step_2_array[key] = value;
						
						//console.log("Key: " + key + "\nValue: " + step_1_array[key] + "\n\n"); 
					  
					});
					
					
					$.ajax({
						
						url: "{{ URL::to('scheme-setup/step2') }}",
						data: {
							step_2_vars: step_2_array
						},
						type: "POST",
						
					}).done(function(data){
						
						if(data.indexOf("Error") === -1) {
							
							
							$('.step-3-msg').html(data); 
							
							$('#s_2').css({
								
								background: '#3fb103',
								color: 'white',
								
							});
							
							  $('.error_box').hide();
							  $('#s_2').removeAttr('data-toggle');
							  $('#s_3_3').removeAttr('disabled');
							  $('#s_3').attr('data-toggle', 'tab');
							  $('#s_3').trigger('click');
							  
						} else {
							
							
							$('.error_msg').html(data);
							$('.error_box').show();
							
						}
						
					});
					
				});
				
				step_3_submit.on('click', function(){
					
					var step_3_array = {};
					
					$.each($('input[class=step3]'),function(){
						
						var key = $(this).attr('name');
						var value = $(this).val();
						
						step_3_array[key] = value;
						
						//console.log("Key: " + key + "\nValue: " + step_1_array[key] + "\n\n"); 
					  
					});
					
					
					$.ajax({
						
						url: "{{ URL::to('scheme-setup/step3') }}",
						data: {
							step_3_vars: step_3_array
						},
						type: "POST",
						
					}).done(function(data){
						
						if(data.indexOf("Error") === -1) {
							
							$('#s_3').css({
								
								background: '#3fb103',
								color: 'white',
								
							});
							
							$('#s_3').removeAttr('data-toggle');
							//  $('#s_3_3').removeAttr('disabled');
							//  $('#s_3').attr('data-toggle', 'tab');
							//  $('#s_3').trigger('click');
							

							$.ajax({
						
								url: "{{ URL::to('scheme-setup/complete') }}",
								type: "POST",
								
							}).done(function(data){
								
								$('.tab-content').html(data);
							
							});
							  
						    $('.error_box').hide();
							
						} else {
							
							
							$('.error_msg').html(data);
							$('.error_box').show();
							
						}
						
					});
					
				});
			});
	
   </script>
   

</div>