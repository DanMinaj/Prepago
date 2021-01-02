<style type="text/css">
    .iniSetup-container {
        border: 1px solid #e5e5e5;
        float: left;
        margin-right: 1em;
        margin-top: 1em;
        max-width: 320px;
        padding: 0 1em 1em;
        width: 90%;
    }
    .iniSetupTitle {
        font-weight: 700;
        margin-top: 2em;
    }
    .test_msg, .ping_msg, .reboot_msg {
        font-size: 2em;
        margin-bottom: 0.5em;
        margin-top: 0.5em;
        width: 100%;
    }
    .btn_setup {
        background: none repeat scroll 0 0 #67308e;
        border: medium none;
        color: #ffffff;
        cursor: pointer;
        float: right;
        margin-top: 1em;
        padding: 5px 10px;
    }
    .btn_setup_other {
        background: none repeat scroll 0 0 #67308e;
        border: medium none;
        color: #ffffff;
        cursor: pointer;
        float: left;
        margin-top: 1em;
        padding: 5px 10px;
		margin-right: 5px;
    }
</style>

@if(isset($dataLogger) && $dataLogger)
	@if($dataLogger->datalogger_active)
	<div class="alert alert-warning alert-block" id="support-error">
		<center>The system has detected that the meters for this scheme are undergoing <b>meter reads</b>. This <i>may</i> prevent the diagnostics tools from working until this process is completed.</center>
		@if($dataLogger->processing_meters_no > 0)
			<br/>
			<center>
				This process is currently 
				<b>{!! number_format(($dataLogger->processing_progress/$dataLogger->processing_meters_no)*100, 2) !!}%</b> complete.
			</center>
		@endif
	</div>
	@else
	
		@if(Auth::user()->scheme->statusCode == "11" || Auth::user()->scheme->statusCode == "21" || Auth::user()->scheme->statusCode == "2")
			<div style="padding: 3px !important; width: 34%;" class="alert alert-warning alert-block" id="support-error">
				<center>The diagnostic tools may be <b>temporarily un-available</b> due to technical issues with this scheme's SIM. <a target="_blank" href="{!! URL::to('schemes/status', ['status_id' => Auth::user()->scheme->statusCode]) !!}">Status code {!! Auth::user()->scheme->statusCode !!}.</a></center>
			</div>
		@endif
	
	@endif
@endif


	<table width="100%">
		
		<tr>
		
				
			<td width="50%" style="vertical-align:top">			
				
				<table width="100%">
				
				<tr>
				<td>
				@if (!isset($meter_read_only))
					
				<div id="service_control_test" class="iniSetup-container">

						<div class="iniSetupTitle">
							Service  
							<input name="command_port" value="2221" type="text" style="text-align:center;width: 8%; font-size: 11px !important; height: 4px; margin-top: 2%; margin-left: 5% !important; padding: 3% !important;">
							<input name="command_attempts" value="1" type="text" style="text-align:center;width: 5%; font-size: 11px !important; height: 4px; margin-top: 2%; margin-left: 5% !important; padding: 3% !important;">
						</div>

						<div class="test_msg"></div>

						<div class="test_btns">
						
							<button class="btn_setup_other" onclick="service_control_restart()">Primary Open</button>
							<button class="btn_setup_other" onclick="telegramAndCheckValveBtnsTest('relay_telegram')">Relay Telegram</button>
										

							<div class="clear"></div>
							
							@if ($pm_scu_type == 'm')
							
								<div id="m_scu_type_btns">
									<div>
										<button class="btn_setup_other" onclick="telegramAndCheckValveBtnsTest('check_valve')">Check Valve</button>
									</div>

									<div class="clear"></div>

									<div>
										<button class="btn_setup_other" onclick="telegramAndCheckValveBtnsTest('meter_telegram')">Meter Telegram</button>
										
									
									</div>

									
									<div class="clear"></div>
									
								<button class="btn_setup_other" onclick="service_control_test_on()">Basic Open</button>
								<button class="btn_setup_other" onclick="service_control_test_off()">Basic Close</button>
								
								<hr>
								
								</div>
							@endif
						</div>
						
						
					</div>
				
				<td>
				</tr>
				
				<tr>
				<td>
				@if(Auth::user()->username == "test")
				@if(is_object($data) && $data->districtHeatingMeter->shut_off_device_status == 1)
					<div id="meter_read_test" class="iniSetup-container">

						<div class="iniSetupTitle"><font color="red" class="stop_schedule_msg">This customer is shut off.</font></div>

						<!--<div class="test_msg"></div>-->

						<button class="btn_setup_other" id="stop_schedule_to_shutoff" onclick="cancel_schedule_shutoff()">Restore</button>

						<div class="clear"></div>
						
					</div>
				@endif
				@endif
				</td>
				</tr>
				
				
				</table>
			</td>
		
			<td width="50%" style="vertical-align:top">
				<table width="100%">
					<tr>						
					<td>
						<div id="meter_read_test" class="iniSetup-container">

							<div class="iniSetupTitle">Meter read test</div>

							<div class="test_msg"></div>

							<button class="btn_setup" onclick="meter_read_test()">Test</button>

							<div class="clear"></div>
						</div>
					</td>		
					</tr>
					<tr>
					<td>
						
						<div id="service_control_test" class="iniSetup-container">

							<div class="iniSetupTitle">Technical</div>
							
							<div class="ping_msg"></div>
							<div class="reboot_msg"></div>
							<script>
							
								$(function(){
									
									$('#reboot_sim').on('click', function(){
										
										var scheme_id = $(this).attr('scheme_id');
										if(interval != null)
											clearInterval(interval);

										var in_progress = false;
										var interval = null;
										var response_div = $('.reboot_msg');

											jQuery("#service_control_test .reboot_msg").Loadingdotdotdot({
											   "speed": 400,
											   "maxDots": 4,
											   "word": "Rebooting.."
										   });

												   
										jQuery.ajax({
										   url: "/settings/reboot_scheme/1",
										   method: "POST",
										   data: {scheme_id: scheme_id, url: window.location.href},
										   success: function(html, textStatus) {
												
													
												if(html.rebooted) {
													
													if(html.res || html.msg_res) {
														var msg = (html.res) ? html.res : html.msg_res;
														jQuery("#service_control_test .reboot_msg").html("<span style='color:#4caf50'>"+msg+"</span>");
														
													}
													else
														jQuery("#service_control_test .reboot_msg").html("<span style='color:#4caf50'>Reboot sent</span>");
												
												} else {
													
													if(html.res || html.msg_res) {
														var msg = (html.res) ? html.res : html.msg_res;
														jQuery("#service_control_test .reboot_msg").html("<span style='color:red'>"+msg+"</span>");
														
													}
													else
														jQuery("#service_control_test .reboot_msg").html("<span style='color:red'>Reboot failed</span>");
													
												}
												// interval = setInterval(function(){
													
													// if(in_progress) {
														// console.log("Cannot send another check status while one is pending..");
														// return;
													// } else {
														// in_progress = true;
														// get_scheme_status(scheme_id, function(data){

															// console.log("get_scheme_status(): " + data);
															// var current_status = 0; 
															// if(data.indexOf('Online') !== -1) {
																// current_status = 1;
															// } else {
																// current_status = 0;
															// }
															// if(current_status == 0) {
																// in_progress = true;
																// clearInterval(interval);
																// console.log("Interval stopped. Desired state reached.");
																// jQuery("#service_control_test .reboot_msg").html("<span style='color:#4caf50'>Reboot sent</span>");
																// return;
															// } else {
																// in_progress = false;
															// }
														// });
													// }

												// }, 1000);
											   
										   }
										});
										
									});
								});
								
							</script>
							
							@if(Auth::user()->username == "test")
								<button class="btn_setup_other" onclick="check_sim_status()">Ping</button>	
							@endif
							<button @if($data->scheme && Carbon\Carbon::parse($data->scheme->status_last_reboot)->diffInSeconds() < 1800) disabled='true' onclick="alert('You must wait 30 minutes before you can do this action again')" style="opacity:0.5;background: none repeat scroll 0 0 #333 !important;" @else id="reboot_sim" scheme_id="{!! $data['scheme_number'] !!}" @endif class="btn_setup_other" >Reboot SIM</button>
		
						</div>

						
					
					</td>
					</tr>
					<tr>
						<td>
							<div id="service_control_test" class="iniSetup-container">

								<div class="iniSetupTitle">Having an issue with this customer?</div>

								<button data-toggle="modal" data-target="#fix-an-issue" class="btn_setup_other" >Fix an issue</button>
										
							</div>
							@endif
						</td>
					</tr>
					<tr>
						<td>
							@if(isset($data) && isset($data['permanent_meter_id']))
							
								<div id="service_control_test" class="iniSetup-container">
									
									<div class="iniSetupTitle">Watchdog<div class="pull-right">@if(!WatchDog::runningInScheme()) <font color='green'>Available</font> @else <font color='red'>Occupied</font> @endif</div></div>
									<br/>
									@if(!WatchDog::runningInScheme()) 
										
										<?php $lastWatchDog = WatchDog::lastWatchDog($data['id']); ?>
										<button data-toggle="modal" data-target="#watchdog" class="btn_setup_other" >Run</button>
												
										@if($lastWatchDog)
											
										<table width="100%" class=''>
											<tr>
												<td><a href="/view_watchdog/{!! $lastWatchDog->id !!}">
												<button class="btn_setup_other" ><i class="fa fa-eye"></i> View last watchdog</button>
												</a></td>
											</tr>
										</table>
										@endif
										
									@else
										
										There is currently a Watchdog running in this scheme. Please try again later.
										<br/>
										<br/>
										
										<b>Current Watchdog information</b><br/>
										{!! nl2br(WatchDog::runningInScheme()->getInfo()) !!}
										
										<br/><br/>
										
										<a href="/view_watchdog/{!! WatchDog::runningInScheme()->id !!}">
										<button class="btn_setup_other" ><i class="fa fa-eye"></i> View</button>
										</a>
										
										<button data-toggle="modal" data-target="#watchdog" class="btn_setup_other" >Force stop</button>
										
									@endif
									
								</div>
									
								@include('modals.watchdog')
								
							@endif	
						</td>
					</tr>
				</table>
			</td>
		
		
		</tr>


	</table>



<hr>

<br/>




	<!--
	<div id="service_control_test" class="iniSetup-container">

		<div class="iniSetupTitle">Prepago Service</div>
		<br/>
		Services Running: <span id="running_services">0</span>/12
		<br/>
		
		<div>
			<button class="btn_setup_other" id="stop_all_services">Stop All Services</button>
			<button class="btn_setup_other" id="start_all_services">Start All Services</button>
		</div>
				
	</div>
	-->


								@include('modals.watchdog')
<input type="hidden" id="baseInstallerURL" value="{!! URL::to('prepago_installer') !!}">
<input type="hidden" id="unitID" value="{!! $meter_id !!}">
<input type="hidden" id="meter_read_test_success" value="0">
<input type="hidden" id="service_control_teston_success" value="0">
<input type="hidden" id="service_control_testoff_success" value="0">

{!! HTML::script('resources/js/installer.js?4874') !!}