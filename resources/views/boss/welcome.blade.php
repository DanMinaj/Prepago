
<h1> Schemes 


<div class="pull-right">
@if(Auth::user()->username == "test")
<table width="100%">
	<tr><!--
		<td>
			<button type="button" data-toggle="modal" data-target="#prepago_services" class="btn btn-warning"><i class="fa fa-desktop"></i> Manage Prepago Services</button>
		</td>
		-->
		<td>
			<a href="{!! URL::to('settings/ping') !!}">
				<button type="button" class="btn btn-info"><i class="fas fa-sim-card"></i>  Manage SIMs</button>
			</a>
		</td>
		<td>
			<a href="{!! URL::to('system_reports/sim_reports') !!}">
				<button type="button" class="btn btn-success"><i class="fas fa-chart-line"></i>  SIM Graphs</button>
			</a>
		</td>
		<td>
			<a style="color: #666;" href="/logout"><button type="button" class="btn btn-primary"><i class="fa fa-sign-out-alt"></i>&nbsp;Logout</button></a>
		</td>
	</tr>
</table>
@else
<table width="100%">
	<tr>
		<td>
			<a style="color: #666;" href="/logout"><button type="button" class="btn btn-primary"><i class="fa fa-sign-out-alt"></i>&nbsp;Logout</button></a>
		</td>
	</tr>
</table>
@endif
</div>

		
</h1>



<div class="admin2">

 @if ($message = Session::get('successMessage'))
        <div class="alert alert-success alert-block">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            {!! $message !!}
        </div>
 @endif
 @if ($e_message = Session::get('errorMessage'))
        <div class="alert alert-danger alert-block">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            {!! $e_message !!}
        </div>
 @endif

    <table style="width: 100%">
        @foreach ($schemes as $scheme)
            <tr>
                {!! Form::open(['url' => URL::to('schemes/set'), 'method' => 'PUT', 'id' => 'scheme_' . $scheme['scheme_number']]) !!}
                    {!! Form::hidden('scheme_number', $scheme['scheme_number']) !!}
                    <td valign="top">
                        <a href="javascript: ;" onclick="$('#scheme_' + {!! $scheme['scheme_number'] !!}).submit()"><span style="font-size: 16px; font-weight: bold;">
						{{ $scheme['scheme_name'] }}
						</span></a>
						@if($scheme['dl'] && $scheme['dl']->datalogger_active)
						<br/>
							@if($scheme['dl']->processing_meters_no > 0)
								Reading -
								<b>{!! number_format(($scheme['dl']->processing_progress/$scheme['dl']->processing_meters_no)*100, 2) !!}%</b> complete.
							@endif
						@else
						<br />
                        {{ $scheme['scheme_desc'] }}
						@endif
						<br/>
						<span class="scheme scheme_{!! $scheme['scheme_number'] !!}_test"></span>
                    </td>
					@if (\Illuminate\Support\Facades\Auth::user()->isUserTest())
                        <td valign="top">
                         
							<span style="{!! $scheme['statusCss'] !!};cursor:pointer;" onclick="generateSchemeInfo({!! $scheme['id'] !!})">
								{!! ucfirst($scheme['status']) !!}
								<font size='1dp'>({!! \Carbon\Carbon::createFromTimeStamp(strtotime($scheme['status_checked']))->diffForHumans(); !!})</font> 
							</span>
							
                        </td>
						<td valign="top">
                            <a href="{!! URL::to('system_reports/advice_notes/' . $scheme['scheme_number']) !!}" style="font-weight: bold; font-size: 14px;">Payout Report</a>
                        </td>
                    @endif
                    <td valign="top" style="width: 5%">
                        <table>
						<!--
                            <tbody>
							
                                <tr>
                                    <td rowspan="4" style="padding-right: 5px">
                                        <a href="javascript: ;" onclick="$('#scheme_' + {!! $scheme['scheme_number'] !!}).submit()">
                                            <img height="65" width="20" src="https://www.prepago-admin.biz/resources/img/traffic_light.png?123" class="img-rounded" >
                                        </a>
                                    </td>
                                    <td>{{ $scheme['white'] }}</td>
                                </tr>
								<tr><td>{{ $scheme['red'] }}</td></tr>
                                <tr><td>{{ $scheme['yellow'] }}</td></tr>
                                <tr><td>{{ $scheme['green'] }}</td></tr>
                            </tbody>
							-->
							 <tbody>
                                <tr>
                                    <td rowspan="3" style="padding-right: 5px">
                                        <a href="javascript: ;" onclick="$('#scheme_' + {!! $scheme['scheme_number'] !!}).submit()">
                                            <img src="http://www.prepago-admin.biz/resources/img/traffic_light.png" class="img-rounded" height="47" width="20">
                                        </a>
                                    </td>
                                    <td>{{ $scheme['red'] }}</td>
                                </tr>
                                <tr><td>{{ $scheme['yellow'] }}</td></tr>
                                <tr><td>{{ $scheme['green'] }}</td></tr>
                            </tbody>
                        </table>
                    </td>
                {!! Form::close() !!}
            </tr>
            <tr><td colspan="2">&nbsp;</td></tr>
        @endforeach
		{{--<td colspan="1" align="left"><br /><a href="javascript: ;" onclick="datalogger_test()"class="btn btn-primary">Run DataLogger Test</a></td>--}}
		<tr><td colspan="2" align="right"><br /><a href="{!! URL::to('scheme-setup') !!}" class="btn btn-success">Set Up a New Scheme</a></td></tr>
		<tr><td colspan="2" align="right"><br /><a data-toggle="modal" data-target="#create_sim" class="btn btn-warning">Set Up a New Simcard</a></td></tr>
		
    	</table>
		
		<script type="text/javascript" src="https://www.prepago-admin.biz/resources/js/bootstrap.min.js"></script>
		<script type="text/javascript">
			
			//prepago_installer/test-dataloggers
			//prepago_installer/test-dataloggers-confirm',
			
			function generateSchemeInfo(scheme_id)
			{
					
				$.ajax({
					
					url: '',
					method: 'POST',
					data: {scheme_id: scheme_id},
					dataType: "json",
					success: function(data){
						
						$('#scheme-title').html('Scheme Info: ' + data.scheme_nickname);
						
						var body = "";
						body += "<table width='100%' class='table table-bordered'>";
							body += "<tr>";
								body += "<td>";
									body += "<b>ID: </b> ";
								body += "</td>";
								body += "<td>";
									body += data.scheme_number;
								body += "</td>";
							body += "</tr>";
							body += "<tr>";
								body += "<td>";
									body += "<b>Status Code: </b> ";
								body += "</td>";
								body += "<td>";
									body += "<a target='_blank' href='schemes/status/" + data.statusCode + "'>" + data.statusCode + "</a>";
								body += "</td>";
							body += "</tr>";
							body += "<tr>";
								body += "<td>";
									body += "<b>IP Address: </b> ";
								body += "</td>";
								body += "<td>";
									body += data.sim.IP_Address;
								body += "</td>";
							body += "</tr>";
							body += "<tr>";
								body += "<td>";
									body += "<b>ICCID: </b> ";
								body += "</td>";
								body += "<td>";
									body += data.sim.ICCID;
								body += "</td>";
							body += "</tr>";
							body += "<tr>";
								body += "<td>";
									body += "<b>Phone #: </b> ";
								body += "</td>";
								body += "<td>";
									body += '+' + data.sim.MSISDN;
								body += "</td>";
							body += "</tr>";
						body += "</table>";
						body += "<hr/>";
						body += "<table width='100%' class='table table-bordered'>";
							body += "<tr>";
								body += "<td>";
									body += "<b>Online % (24hrs): </b> ";
								body += "</td>";
								body += "<td>";
									body += Math.floor(data.track.uptime_percentage) + "% <a href='{!!  URL::to('system_reports/sim_reports') !!}#s_" + data.scheme_number + "'>(View graph)</a>";
								body += "</td>";
							body += "</tr>";
							body += "<tr>";
								body += "<td>";
									body += "<b>Last offline: </b> ";
								body += "</td>";
								body += "<td>";
									body += data.track.last_offline;
								body += "</td>";
							body += "</tr>";
						body += "</table>";
						body += "<hr/>";
						body += "<table width='100%' class='table table-bordered'>";
							body += "<tr>";
								body += "<td>";
									body += "<b>Last command: </b> ";
								body += "</td>";
								body += "<td>";
									body += data.sim.last_sms + " (" + data.sim.last_sms_timestamp + ")";
								body += "</td>";
							body += "</tr>";
							body += "<tr>";
								body += "<td>";
									body += "<b>Status: </b> ";
								body += "</td>";
								body += "<td>";
									body += data.sim.last_sms_status;
								body += "</td>";
							body += "</tr>";
						body += "</table>";
						body += "<table width='100%' class='table table-bordered'>";
							body += "<tr>";
								body += "<td>";
									body += "<b>Last network change time: </b> ";
								body += "</td>";
								body += "<td>";
									if(data.extra != null && data.extra.last_network_time != null && data.extra.last_network_time_formatted != null) {
										body += data.extra.last_network_time + " (" + data.extra.last_network_time_formatted + ")";
									} else {
										body += "n/a";
									}
								body += "</td>";
							body += "</tr>";
							body += "<tr>";
								body += "<td>";
									body += "<b>Last network: </b> ";
								body += "</td>";
								body += "<td>";
									if(data.extra != null && data.extra.last_network != null) {
										body += data.extra.last_network;
									} else {
										body += "n/a";
									}
								body += "</td>";
							body += "</tr>";
							body += "<tr>";
								body += "<td>";
									body += "<b>Mobile Country Code / Mobile Network Code: </b> ";
								body += "</td>";
								body += "<td>";
									if(data.extra != null && data.extra.mcc_mnc != null) {
										body += data.extra.mcc_mnc;
									} else {
										body += "n/a";
									}
								body += "</td>";
							body += "</tr>";
						body += "</table>";
						body += "<hr/>";
						body += "<table width='100%' class='table table-bordered'>";
							body += "<tr>";
								body += "<td>";
									body += "<b>Readings today: </b> ";
								body += "</td>";
								body += "<td>";
									body += data.track.readings;
								body += "</td>";
							body += "</tr>";
						body += "</table>";
						body += "<hr/>";
						
						
						var track_log = JSON.parse(data.track_log);
						
						if(track_log instanceof Array) {
						body += "<table width='100%' class='table table-bordered'>";
							body += "<hr/>";
							$.each(track_log, function(k, v) {
								//
								console.log(v[1]);
								var status = v[0];
								var time = v[1];
								
								body += "<tr>";
									body += "<td>";
										if(status == "1") {
											body += "SIM was online.";
										} else {
											body += "SIM was OFFLINE." //
										}
									body += "</td>";
									body += "<td>";
										body += time;
									body += "</td>";
								body += "</tr>";
							});
						body += "</table>";
						}
						
						
						
						$('#scheme-body').html(body);
						
						$('#scheme-info').modal({show:true});
						
					},
					error: function(xhr, ajaxOptions, thrownError){
						console.log('Failed to generate scheme info for scheme' + scheme_id + " | " + thrownError);
					}
					
				});
				
			}
			
			function datalogger_test()
			{
				
				$('.scheme').each(function(){
					
					var scheme_id = $(this).attr('class').split(' ')[1].split('scheme_')[1].split('_test')[0];
					var scheme_text = $(this);
					
					$.ajax({
						
					    type:'GET',
						url: 'prepago_installer/test-dataloggers/' + scheme_id,
						success: function(data){
							
							if (data == 'success') {
								
								  scheme_text.Loadingdotdotdot({
									"speed": 400,
									"maxDots": 4,
									"word": "Running"
									});
								
								
									var intervalCount = 0;
									var datalogger_test_interval = setInterval(function(){

										intervalCount++;

										if (intervalCount === 70)
										{
											clearInterval(datalogger_test_interval);
											scheme_text.html("<font color='red'>FAILED</font>");
										}

										$.ajax({
											type:'GET',
											url: 'prepago_installer/test-dataloggers-confirm/' + scheme_id,
											success: function(html, textStatus) {
													
												if(html.length > 0)
													scheme_text.html(html);
													
											},
											error: function(xhr, textStatus, errorThrown) {
												clearInterval(datalogger_test_interval);
												scheme_text.html("<font color='red'>FAILED</font>");
											}
										});

									}, 3000);
					
							}
							else
							{
								
								alert('initiation failed');
								
							}
						}
					});
					
				});
				
			}	
			
			function datalogger_test_response()
			{
				
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

            base.$el.html("<span style='color:orange'>Running<em></em></span>");

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
		<script>
	$(function(){

		function refreshProcesses()
		{
			var running_services = 0;
			var offline_services = 0;
			var total_services = 0;
			var services_list = $('#services_list');
			var append = "";
			
			$.ajax({
				url: 'running_services',
				type: 'GET',
				success: function(data){
					
					services = $.parseJSON(data);
					
					if(services.error != undefined)
					{
						services_list.html(services.error);
						return;
					}
					
					$.each(services, function(key, val){
						
						// rogue services that shouldn't be here?
						if(val.process_name <= 4)
						{
							return;
						}
						
						append += "<tr>";
						append += "<td>" + ( (val.process_id != 0) ? ('<font color=green>' + val.process_id+"."+val.process_name + '</font>') : ('<font color=red>' + val.process_id + '.'+val.process_name+'</font>')) + "</td><td></td>";
						append += "</tr>";
						append += "<tr>";
						if(val.process_id == 0 || val.process_status != 'Ss')
						{
							offline_services++;
							append += "<td><b>Status</b>: <font color='red'>Offline " +( (val.process_status != 'Ss' && val.process_id != 0) ? '-Process unresponsive-' : '' )+ "</font></td>";
						}
						else
						{
							running_services++;
							append += "<td><b>Running since</b>: "+val.process_started+"</td>";
							
							innerbar_1 = "<div style='width:" + val.process_cpu + "%' class='usage'></div>";
							bar_1 = "<div class='usage_container'>"+innerbar_1+"</div>";
							innerbar_2 = "<div style='width:" + val.process_memory + "%' class='usage'></div>";
							bar_2 = "<div class='usage_container'>"+innerbar_2+"</div>";
							
							append += "</tr><td><b>CPU Usage</b>:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; "+bar_1+"&nbsp;" + val.process_cpu + "%</td>";
							append += "</tr><td><b>Memory Usage</b>: "+bar_2+"&nbsp;" + val.process_memory + "%</td>";
						}
						
						total_services++;
						
						append += "</tr>";
						append += "<tr>";
						
						if(val.process_id == 0)
						{
							if(val.process_name != "paypointServer"){
							append += "<td><div class='start_btn' service='"  + val.process_name + "'>Start</div></td>";
							append += "<td><div class='stop_btn disabled' service='"  + val.process_name + "'>Stop</div></td>";
							append += "<td><div class='restart_btn' service='"  + val.process_name + "'>Restart</div></td>";
							}
						}
						
						if(val.process_id != 0)
						{
							if(val.process_name != "paypointServer"){
							append += "<td><div class='start_btn disabled' service='"  + val.process_name + "'>Start</div></td>";
							append += "<td><div class='stop_btn' service='"  + val.process_name + "'>Stop</div></td>";
							append += "<td><div class='restart_btn' service='"  + val.process_name + "'>Restart</div></td>";
							//append += "<td><div class='output_btn' service='"  + val.process_name + "'>Output</div></td>";
							}
						}
						
						
						append += "<td> </td>";
						append += "</tr>";
						append += "<tr>";
						append += "<td><br/><br/></td>";
						append += "</tr>";
						
						//console.log(val.process_start_cmd);
						
					});
					
					var pre_append = "";
					pre_append += "<tr>";
					pre_append += "<td><b>Online services</b>: <font color='green'>" + +running_services+ "/" + total_services + "</font> <br/><b>Offline services</b>: <font color=red>" + offline_services + "</font></td>";
					pre_append += "</tr>";
					pre_append += "<tr><td><br/></td></tr>";
					
					append = pre_append + "" + append;
					services_list.html(append);
					
					$('.restart_btn').on('click', function(){
						var process_name = $(this).attr('service');
						$('.stop_btn[service='+process_name+']').trigger('click');
						setTimeout(function(){
							$('.start_btn[service='+process_name+']').trigger('click');
						}, 1000);
						
					});
					
					$('.stop_btn').on('click', function(){
						
						var svc_name = $(this).attr('service');
						var svc_info = $.parseJSON(data)[svc_name];
						var cmd_start = svc_info.process_start_cmd;
						var cmd_stop = "kill " + svc_info.process_id;
						var action_name = svc_name;
						
						$.ajax({
							url: '/running_services_stop/'+svc_info.process_id+'/' + svc_name,
							type: 'GET',
							success: function(data){
								console.log(data);
								$('.stop_btn[service='+svc_name+']').addClass('disabled');
								$('.start_btn[service='+svc_name+']').removeClass('disabled');
								refreshProcesses();
								$('.output').val($('.output').val() + "- Stopped " + action_name  + "\n");
							},
							error: function(data){console.log('error');}
						});
						
					});
					
					$('.start_btn').on('click', function(){
						
						var svc_name = $(this).attr('service');
						var svc_info = $.parseJSON(data)[svc_name];
						var cmd_start = svc_info.process_start_cmd;
						var cmd_stop = "kill " + svc_info.process_id;
						var action_name = svc_name;
						
						$.ajax({
							url: '/running_services_start/' + svc_name,
							type: 'GET',
							success: function(data){
								console.log(data);
								$('.stop_btn[service='+svc_name+']').removeClass('disabled');
								$('.start_btn[service='+svc_name+']').addClass('disabled');
								refreshProcesses();
								$('.output').val($('.output').val() + "+ Started " + action_name  + "\n");
							},
							error: function(data){console.log('error');}
						});
						
					});
					
					
				},
				error: function(){
					
				}
			});
			
		}
		
		$('#refresh_services').on('click', function(){
			refreshProcesses();
		});
		
		refreshProcesses();
		
		// Auto refresh processes every 4secs
		setInterval(function(){
			refreshProcesses();
		}, 4000);
	});
</script>

		
</div>

@if(Auth::user()->username == "test")

<!--include('modals.prepago_services')-->

<div id="create_sim" class="modal fade" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Create a sim</h4>
      </div>
      <div class="modal-body">
		
		<form action="{!! URL::to('scheme-setup/sim-setup') !!}" method="POST">
		  <div class="form-group">
			<label for="sim_name"><font color='red'>*</font> SIM Name</label>
			<input type="text" class="form-control"name="sim_name" id="sim_name" placeholder="SIM Name">
		  </div>
		  <div class="form-group">
			<label for="sim_ip"><font color='red'>*</font> SIM IP Address</label>
			<input type="text" class="form-control"name="sim_ip" id="sim_ip" placeholder="SIM IP Address">
		  </div>
		  <div class="form-group">
			<label for="iccid">ICCID (optional)</label>
			<input type="text" class="form-control"name="iccid" id="iccid" placeholder="ICCID (optional)">
		  </div>
		  <div class="form-group">
			<label for="msisdn">MSISDN (optional)</label>
			<input type="text" class="form-control"name="msisdn" id="msisdn" placeholder="MSISDN (optional)">
		  </div>
		  <button type="submit" class="btn btn-primary">Setup sim</button>
		</form>
		
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>

  </div>
</div>

<div id="scheme-info" class="modal fade" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title" id="scheme-title">Scheme</h4>
      </div>
      <div class="modal-body" id="scheme-body">
        
      </div>
      <div class="modal-footer" id="scheme-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>

  </div>
</div>

@endif