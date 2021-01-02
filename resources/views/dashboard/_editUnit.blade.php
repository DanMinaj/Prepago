

<div class="title"><div class="ico_units"></div><h1>Edit Unit</h1></div>

<div class="iniSetup-container">

	<div class="iniSetupTitle">Installation:</div>
	
	@if($unit['installation_confirmed'] == 1)
	<div class="test_msg">Confirmed</div>
	@else
	<div class="test_msg">Unsuccessful</div>
	@endif
	<div class="clear"></div>
</div>

<div id="meter_read_test" class="iniSetup-container">

	<div class="iniSetupTitle">Meter read test</div>

	<div class="test_msg"></div>
	
	<button class="btn_setup" onclick="meter_read_test()">Test</button>

	<div class="clear"></div>
</div>

<div id="service_control_test" class="iniSetup-container">

	<div class="iniSetupTitle">Service Control Port</div>

	<div class="test_msg"></div>
	
	<button class="btn_setup_other" onclick="service_control_test_on()">Test On</button>
	<button class="btn_setup_other" onclick="service_control_test_off()">Test Off</button>

	<div class="clear"></div>
</div>

@if($unit['heat_port'] != -1)
<div id="heat_control_test" class="iniSetup-container">

	<div class="iniSetupTitle">Heat Control Port </div>

	<div class="test_msg"></div>
	
	<button class="btn_setup_other" onclick="heat_control_test_on()">Test On</button>
	<button class="btn_setup_other" onclick="heat_control_test_off()">Test Off</button>

	<div class="clear"></div>
</div>
@endif


<div id="completeInstall" class="iniSetup-container">

	<div class="iniSetupTitle">Complete Installation</div>

	{{ Form::open(array('url' => 'prepago_installer/complete-install')) }}
	{{ Form::hidden('unitID', $unitID) }}
	{{ Form::submit('Mark as complete', array('class' => 'btn_setup')) }}
	{{ Form::close() }}

	<div class="clear"></div>

</div>

<div class="clear"></div>

<div id="mbusmbus" class="max-container" style="margin-top: 1em;">
{{-- Form::open(array('url' => 'save-unit')) --}}
{{ Form::open(array('url' => ['prepago_installer/save-unit', $unitID] )) }}

	{{ Form::hidden('ID', $unitID) }}
	
	<div class="iniSetup-container">

		<div class="iniSetupTitle">Address</div>

		{{ Form::label('house_apartment_number', 'House/Apartment Number') }}
		{{ Form::text('house_apartment_number', $unit['house_name_number']) }}
		{{ $errors->first('house_apartment_number', '<span class="formerror">:message</span>') }}

		{{ Form::label('building_street_name', 'Building/Street Name') }}
		{{ Form::text('building_street_name', $unit['street1']) }}
		{{ $errors->first('building_street_name', '<span class="formerror">:message</span>') }}
		
		{{ Form::label('street2', 'Street2') }}
		{{ Form::text('street2', $unit['street2'] ? $unit['street2'] : $schemeStreet2) }}
		{{ $errors->first('street2', '<span class="formerror">:message</span>') }}

	</div>
	<div id="mbussim" class="iniSetup-container">

		<div class="iniSetupTitle">SCU</div>

		{{ Form::label('scu_number', 'SCU Number') }}
		{{ Form::text('scu_number', $unit['scu_number']) }}
		{{ $errors->first('scu_number', '<span class="formerror">:message</span>') }}

		{{ Form::label('iccid', 'ICCID (long sim number)') }}
		{{ Form::text('iccid', $simcard['ICCID']) }}
		{{ $errors->first('iccid', '<span class="formerror">:message</span>') }}

	</div>
	<div id="mbussim" class="iniSetup-container">

		<div class="iniSetupTitle">Ports</div>

		{{ Form::label('service_control_port', 'Service Control Port') }}
		{{ Form::select('service_control_port', array( '' => '', '1' => '1', '2' => '2', '3' => '3', '4' => '4', '5' => '5', '6' => '6', '7' => '7' ), $unit['scu_port']) }}
		{{ $errors->first('service_control_port', '<span class="formerror">:message</span>') }}

		{{ Form::label('heat_control_port', 'Heat Control Port ') }}
		{{ Form::select('heat_control_port', array( '' => '', '-1' => 'None', '1' => '1', '2' => '2', '3' => '3', '4' => '4', '5' => '5', '6' => '6' ), $unit['heat_port']) }}
		{{ $errors->first('heat_control_port', '<span class="formerror">:message</span>') }}

	</div>
	<div class="clear"></div>
	<div id="mbussim" class="iniSetup-container">

		<div class="iniSetupTitle">Meter Details</div>
	
		{{ Form::label('meter_number', 'Meter Number') }}
		{{ Form::text('meter_number', $unit['meter_number']) }}
		{{ $errors->first('meter_number', '<span class="formerror">:message</span>') }}
	
		{{ Form::label('baud_rate', 'Meter Baud Rate') }}
		{{ Form::text('baud_rate', $unit['meter_baud_rate']) }}
		{{ $errors->first('baud_rate', '<span class="formerror">:message</span>') }}

		{{ Form::label('readings_per_day', 'Readings per day') }}
		{{ Form::select('readings_per_day', array( '' => '', '1' => '1', '2' => '2', '3' => '3', '4' => '4', '6' => '6', '8' => '8', '12' => '12', '16' => '16', '24' => '24', '48' => '48' ), $unit['readings_per_day']) }}
		{{ $errors->first('readings_per_day', '<span class="formerror">:message</span>') }}

		{{ Form::label('md_make', 'Make') }}
		{{ Form::text('md_make', $unit['meter_make']) }}
		{{ $errors->first('md_make', '<span class="formerror">:message</span>') }}

		{{ Form::label('md_model', 'Model') }}
		{{ Form::text('md_model', $unit['meter_model']) }}
		{{ $errors->first('md_model', '<span class="formerror">:message</span>') }}

		{{ Form::label('md_manufacturer', 'Manufacturer') }}
		{{ Form::text('md_manufacturer', $unit['meter_manufacturer']) }}
		{{ $errors->first('md_manufacturer', '<span class="formerror">:message</span>') }}
		
		{{ Form::customcheckbox('is_boiler_meter', 'default', 'Is Boiler Room Meter ', ($unit['is_boiler_room_meter']==0)?false:true) }}
		{{ $errors->first('is_boiler_meter', '<span class="formerror">:message</span>') }}

	</div>
	<div id="mbussim" class="iniSetup-container">

		<div class="iniSetupTitle">HIU Details</div>

		{{ Form::label('hd_make', 'Make') }}
		{{ Form::text('hd_make', $unit['HIU_make']) }}
		{{ $errors->first('hd_make', '<span class="formerror">:message</span>') }}

		{{ Form::label('hd_model', 'Model') }}
		{{ Form::text('hd_model', $unit['HIU_model']) }}
		{{ $errors->first('hd_model', '<span class="formerror">:message</span>') }}

		{{ Form::label('hd_manufacturer', 'Manufacturer') }}
		{{ Form::text('hd_manufacturer', $unit['HIU_manufacturer']) }}
		{{ $errors->first('hd_manufacturer', '<span class="formerror">:message</span>') }}

	</div>
	<div id="mbussim" class="iniSetup-container">

		<div class="iniSetupTitle">Valve Details</div>

		{{ Form::label('vd_make', 'Make') }}
		{{ Form::text('vd_make', $unit['valve_make']) }}
		{{ $errors->first('vd_make', '<span class="formerror">:message</span>') }}

		{{ Form::label('vd_model', 'Model') }}
		{{ Form::text('vd_model', $unit['valve_model']) }}
		{{ $errors->first('vd_model', '<span class="formerror">:message</span>') }}

		{{ Form::label('vd_manufacturer', 'Manufacturer') }}
		{{ Form::text('vd_manufacturer', $unit['valve_manufacturer']) }}
		{{ $errors->first('vd_manufacturer', '<span class="formerror">:message</span>') }}

		<div class="clear"></div>

	</div>
	@if($message = Session::get('unitSaveError'))
        <span class="formerror">{{ $message }}</span>
    @endif

	<div class="clear"></div>

	{{ Form::submit('Save Unit', array('class' => 'btn_iniSetup')) }}
	{{ Form::close() }}
</div>

<input type="hidden" id="base" value="{{ URL::to('prepago_installer') }}">
<input type="hidden" id="unitID" value="{{ $unit['ID'] }}">
<input type="hidden" id="meter_read_test_success" value="0">
<input type="hidden" id="service_control_teston_success" value="0">
<input type="hidden" id="service_control_testoff_success" value="0">

<script>

	jQuery(document).ready(function(){

		jQuery('#completeInstall').hide();

	});

	function meter_read_test()
	{
		var base_url = jQuery('#base').val();
		var unitID = jQuery('#unitID').val();
		var req_url = base_url + "/test-unit/meter-read-test/" + unitID;
		var confirm_url = base_url + "/test-unit/meter-read-test-confirm/" + unitID;

		jQuery('#meter_read_test .btn_setup').hide();

		jQuery.ajax({
                type:'GET',
                url: req_url,
                datatype:'html',
                success: function(html, textStatus) {

                	if (html == 'success') {

                		jQuery("#meter_read_test .test_msg").Loadingdotdotdot({
						    "speed": 400,
						    "maxDots": 4,
						    "word": "Running"
						});
                   
	                	setTimeout(function(){

	                		jQuery.ajax({
				                type:'GET',
				                url: confirm_url,
				                datatype:'html',
				                success: function(html, textStatus) {
				                   
				                	if (html != 'failed') {
				                		jQuery('#meter_read_test .test_msg').html(html);
				                		jQuery('#meter_read_test_success').val(1);
				                	}else{
				                		jQuery('#meter_read_test .test_msg').html('Reading Failed!');
				                		jQuery('#meter_read_test_success').val(0);
				                	}

				                	jQuery('#meter_read_test .btn_setup').show();

				                	calculateSuccess();
				                   
				                },
				                error: function(xhr, textStatus, errorThrown) {
				                    alert('An error occurred! ' + textStatus);
				                }
				            });

	                	}, 15000);

                	}else{
                		jQuery('#meter_read_test .test_msg').html('Test initiation failed.');
                	}
                   
                },
                error: function(xhr, textStatus, errorThrown) {
                    alert('An error occurred! ' + textStatus);
                }
            });
		
	}

	function service_control_test()
	{
		var base_url = jQuery('#base').val();
		var unitID = jQuery('#unitID').val();
		var req_url = base_url + "/test-unit/service-control-test/" + unitID;
		var confirm_url = base_url + "/test-unit/service-control-test-confirm/" + unitID;

		jQuery('#service_control_test .btn_setup').hide();

		jQuery.ajax({
                type:'GET',
                url: req_url,
                datatype:'html',
                success: function(html, textStatus) {

                	if (html == 'success') {

                		jQuery("#service_control_test .test_msg").Loadingdotdotdot({
						    "speed": 400,
						    "maxDots": 4,
						    "word": "Running"
						});
                   
	                	setTimeout(function(){

	                		jQuery.ajax({
				                type:'GET',
				                url: confirm_url,
				                datatype:'html',
				                success: function(html, textStatus) {
				                   
				                	if (html == 'success') {
				                		jQuery('#service_control_test .test_msg').html('Success!');
				                	}else{
				                		jQuery('#service_control_test .test_msg').html('Confirmation have failed.');
				                	}

				                	jQuery('#service_control_test .btn_setup').show();

				                   calculateSuccess();
				                },
				                error: function(xhr, textStatus, errorThrown) {
				                    alert('An error occurred! ' + textStatus);
				                }
				            });

	                	}, 15000);

                	}else{
                		jQuery('#service_control_test .test_msg').html('Test initiation failed.');
                	}
                   
                },
                error: function(xhr, textStatus, errorThrown) {
                    alert('An error occurred! ' + textStatus);
                }
            });
		
	}

	function service_control_test_on()
	{
		var base_url = jQuery('#base').val();
		var unitID = jQuery('#unitID').val();
		var req_url = base_url + "/test-unit/service-control-test-switch/" + unitID + "/on";

		jQuery.ajax({
                type:'GET',
                url: req_url,
                datatype:'html',
                success: function(html, textStatus) {

                	if (html == 'success') {
                		jQuery('#service_control_test .test_msg').html('Test is on');
                		jQuery('#service_control_teston_success').val(1);
                	}else{
                		jQuery('#service_control_test .test_msg').html('Test on failed.');
                		jQuery('#service_control_teston_success').val(0);
                	}

                	calculateSuccess();
                   
                },
                error: function(xhr, textStatus, errorThrown) {
                    alert('An error occurred! ' + textStatus);
                }
            });
	}

	function service_control_test_off()
	{
		var base_url = jQuery('#base').val();
		var unitID = jQuery('#unitID').val();
		var req_url = base_url + "/test-unit/service-control-test-switch/" + unitID + "/off";

		jQuery.ajax({
                type:'GET',
                url: req_url,
                datatype:'html',
                success: function(html, textStatus) {

                	if (html == 'success') {
                		jQuery('#service_control_test .test_msg').html('Test is off');
                		jQuery('#service_control_testoff_success').val(1);
                	}else{
                		jQuery('#service_control_test .test_msg').html('Test off failed.');
                		jQuery('#service_control_testoff_success').val(0);
                	}

                	calculateSuccess();
                   
                },
                error: function(xhr, textStatus, errorThrown) {
                    alert('An error occurred! ' + textStatus);
                }
            });
	}


	function heat_control_test()
	{
		var base_url = jQuery('#base').val();
		var unitID = jQuery('#unitID').val();
		var req_url = base_url + "/test-unit/heat-control-test/" + unitID;
		var confirm_url = base_url + "/test-unit/heat-control-test-confirm/" + unitID;

		jQuery('#heat_control_test .btn_setup').hide();

		jQuery.ajax({
                type:'GET',
                url: req_url,
                datatype:'html',
                success: function(html, textStatus) {

                	if (html == 'success') {
                		
                		jQuery("#heat_control_test .test_msg").Loadingdotdotdot({
						    "speed": 400,
						    "maxDots": 4,
						    "word": "Running"
						});

                   
	                	setTimeout(function(){

	                		jQuery.ajax({
				                type:'GET',
				                url: confirm_url,
				                datatype:'html',
				                success: function(html, textStatus) {
				                   
				                	if (html == 'success') {
				                		jQuery('#heat_control_test .test_msg').html('Success!');
				                	}else{
				                		jQuery('#heat_control_test .test_msg').html('Confirmation have failed.');
				                	}

				                	jQuery('#heat_control_test .btn_setup').show();

				                   calculateSuccess();
				                },
				                error: function(xhr, textStatus, errorThrown) {
				                    alert('An error occurred! ' + textStatus);
				                }
				            });

	                	}, 15000);

                	}else{
                		jQuery('#heat_control_test .test_msg').html('Test initiation failed.');
                	}
                   
                },
                error: function(xhr, textStatus, errorThrown) {
                    alert('An error occurred! ' + textStatus);
                }
            });
		
	}

	function heat_control_test_on()
	{
		var base_url = jQuery('#base').val();
		var unitID = jQuery('#unitID').val();
		var req_url = base_url + "/test-unit/heat-control-test-switch/" + unitID + "/on";

		jQuery.ajax({
                type:'GET',
                url: req_url,
                datatype:'html',
                success: function(html, textStatus) {

                	if (html == 'success') {
                		jQuery('#heat_control_test .test_msg').html('Test is on');
                	}else{
                		jQuery('#heat_control_test .test_msg').html('Test on failed.');
                	}
                   
                },
                error: function(xhr, textStatus, errorThrown) {
                    alert('An error occurred! ' + textStatus);
                }
            });
	}

	function heat_control_test_off()
	{
		var base_url = jQuery('#base').val();
		var unitID = jQuery('#unitID').val();
		var req_url = base_url + "/test-unit/heat-control-test-switch/" + unitID + "/off";

		jQuery.ajax({
                type:'GET',
                url: req_url,
                datatype:'html',
                success: function(html, textStatus) {

                	if (html == 'success') {
                		jQuery('#heat_control_test .test_msg').html('Test is off');
                	}else{
                		jQuery('#heat_control_test .test_msg').html('Test off failed.');
                	}
                   
                },
                error: function(xhr, textStatus, errorThrown) {
                    alert('An error occurred! ' + textStatus);
                }
            });
	}

	function calculateSuccess(){
		var meter_read_test_success = jQuery('#meter_read_test_success').val();
		var service_control_teston_success = jQuery('#service_control_teston_success').val();
		var service_control_testoff_success = jQuery('#service_control_testoff_success').val();

		if(meter_read_test_success == 1 && service_control_teston_success == 1 && service_control_testoff_success == 1){
		// if(meter_read_test_success == 1){
			jQuery('#completeInstall').show();
			jQuery('#completeInstall .btn_setup').show();
		}
	}


	(function($) {
	    
	    $.Loadingdotdotdot = function(el, options) {
	        
	        var base = this;
	        
	        base.$el = $(el);
	                
	        base.$el.data("Loadingdotdotdot", base);
	        
	        base.dotItUp = function($element, maxDots) {
	            if ($element.text().length == maxDots) {
	                $element.text("");
	            } else {
	                $element.append(".");
	            }
	        };
	        
	        base.stopInterval = function() {    
	            clearInterval(base.theInterval);
	        };
	        
	        base.init = function() {
	        
	            if ( typeof( speed ) === "undefined" || speed === null ) speed = 300;
	            if ( typeof( maxDots ) === "undefined" || maxDots === null ) maxDots = 3;
	            
	            base.speed = speed;
	            base.maxDots = maxDots;
	                                    
	            base.options = $.extend({},$.Loadingdotdotdot.defaultOptions, options);
	                        
	            base.$el.html("<span>Loading<em></em></span>");
	            
	            base.$dots = base.$el.find("em");
	            base.$loadingText = base.$el.find("span");
	            
	            base.$el.css("position", "relative");
	                        
	            base.theInterval = setInterval(base.dotItUp, base.options.speed, base.$dots, base.options.maxDots);
	            
	        };
	        
	        base.init();
	    
	    };
	    
	    $.Loadingdotdotdot.defaultOptions = {
	        speed: 300,
	        maxDots: 3
	    };
	    
	    $.fn.Loadingdotdotdot = function(options) {
	        
	        if (typeof(options) == "string") {
	            var safeGuard = $(this).data('Loadingdotdotdot');
				if (safeGuard) {
					safeGuard.stopInterval();
				}
	        } else { 
	            return this.each(function(){
	                (new $.Loadingdotdotdot(this, options));
	            });
	        } 
	        
	    };
	    
	})(jQuery);


</script>