<div id="sim_settings" class="modal fade" role="dialog">
<div class="modal-dialog">

<!-- Modal content-->
<div class="modal-content">
  <div class="modal-header">
	<button type="button" class="close" data-dismiss="modal">&times;</button>
	<h4 class="modal-title">
	<ul class="nav nav-tabs" style="margin: 30px 0">
	  <li class="active"><a href="#network" data-toggle="tab"><i class='fa fa-network-wired'></i>&nbsp;Network</a></li>
	  <li><a href="#logs" data-toggle="tab"><i class='fa fa-stream'></i>&nbsp;Logs</a></li>
	  <li><a href="#sendsms" data-toggle="tab"><i class='fa fa-comments'></i>&nbsp;Send SMS</a></li>
	  <li><a href="#setup" data-toggle="tab"><i class='fa fa-settings'></i>&nbsp;Setup</a></li>
	</ul>
	</h4>
  </div>
  <div class="modal-body">
	
	<input type="hidden" name="cur_ip" value="">
	<input type="hidden" name="cur_scheme_number" value="">
	<input type="hidden" name="cur_scheme_name" value="">
	
	
	<div class="alert alert-success success_msg" style="display:none;"></div>
	<div class="alert alert-danger error_msg" style="display:none;"></div>
	<div class="alert alert-info info_msg" style="display:none;"></div>
	<div class="alert alert-warning warning_msg" style="display:none;"></div>
			
	<div class="tab-content">
		<div class="tab-pane active" id="network" style="">
			<div class="row-fluid">
				<div class="span5">
					<font style="font-size:1.6rem;font-weight:bold;" class="network_holder">Loading..</font>
				</div>
				<div class="span3">
					<font style="font-size:1.1rem;font-weight:bold;" class="mb_holder">Loading..</font>
				</div>
				<div class="span4">
					<font style="font-size:0.9rem;" class="active_holder">Loading..</font>
				</div>
			</div>
		</div>
		<div class="tab-pane" id="logs" style="">
			
		</div>
		<div class="tab-pane" id="setup" style="">
		
			<font style="font-size:1.6rem;font-weight:bold;">Setup SMS</font>
			<hr/>
			<button style='margin-bottom:3px;display:block;width: 85%;' class="btn btn-primary" onclick="$('.send_sms_msg').val('qset net em');$('.send_sms').trigger('click')">qset net em</button>
			
			<button style='margin-bottom:3px;display:block;width: 85%;' class="btn btn-primary" onclick="$('.send_sms_msg').val('qset tmbus2 on 2400 2221');$('.send_sms').trigger('click')">qset tmbus2 on 2400 2221</button>
			
			<button style='margin-bottom:3px;display:block;width: 85%;' class="btn btn-primary" onclick="$('.send_sms_msg').val('qset tmbus1 on 2400 2222');$('.send_sms').trigger('click')">qset tmbus1 on 2400 2222</button>
			
			<button style='margin-bottom:3px;display:block;width: 85%;' class="btn btn-primary" onclick="$('.send_sms_msg').val('set common.tcp.tmbus2.timeout=3');$('.send_sms').trigger('click')">set common.tcp.tmbus2.timeout=3</button>
			
			<button style='margin-bottom:3px;display:block;width: 85%;' class="btn btn-primary" onclick="$('.send_sms_msg').val('qset console on 12000');$('.send_sms').trigger('click')">qset console on 12000</button>
			
			<hr/>
			
		</div>
		<div class="tab-pane" id="sendsms" style="">
			<div class="row-fluid">
				<div class="span8">
					<div class="row-fluid"><div class="span2"><input class="wait_for_delivery" type="checkbox"></div><div class="span10">Wait for delivery</div></div>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span8">
					<div class="row-fluid"><div class="span2"><input class="wait_for_res" checked type="checkbox"></div><div class="span10">Wait for response</div></div>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span8">
					<input style="width:90%;padding:2% 2% 2% 2%;width:95%;" value="20" class="wait_time" type="text" placeholder="Wait for (in seconds)">
				</div>
			</div>
			<div class="row-fluid">
				<div class="span8">
					<input style="width:90%;padding:2% 2% 2% 2%;width:95%;" class="send_sms_msg" type="text" placeholder="SMS">
				</div>
			</div>
			<div class="row-fluid">
				<div class="span8">
					<button type="button" style="width:95%;" class="btn btn-primary send_sms" ip="">Send</button>
				</div>
			</div>
			<font style="font-size:1.6rem;font-weight:bold;"><button class="btn btn-success refresh_sim"><i class="fa fa-sync"></i></button>&nbsp;SMS Logs</font>
			<hr/>
			<div class="sms_holder">
				
			</div>
		</div>
	</div>
	
  </div>
  <div class="modal-footer">
	<button type="button" class="btn" data-dismiss="modal">Dismiss</button>
  </div>
</div>

</div>
</div>


<script>
    $(function(){
		
		var auth_token = null;
		var init = false;
		var sim_data = [];
		emnifyInit();
		
		function currentScheme()
		{
			return {
				ip: $("[name='cur_ip']").val(),
				name: $("[name='cur_scheme_name']").val(),
				number: $("[name='cur_scheme_number']").val(),
			};		
		}
			
		function getEmnifyToken(callback)
		{
			var application_token = "eyJhbGciOiJIUzUxMiJ9.eyJlc2MuYXBwc2VjcmV0IjoiOGUyYzg4ZjctNzcxZS00NTE4LThkNjQtYTQ2N2Q0NjQyNDM0Iiwic3ViIjoiYWNjb3VudHNAcHJlcGFnby5pZSIsImF1ZCI6IlwvYXBpXC92MVwvYXBwbGljYXRpb25fdG9rZW4iLCJlc2MuYXBwIjo2MDA4LCJhcGlfa2V5IjpudWxsLCJlc2MudXNlciI6MjA4Mjc0LCJlc2Mub3JnIjoxMTEzNSwiZXNjLm9yZ05hbWUiOiJQcmVwYWdvIFBsYXRmb3JtIEx0ZCIsImlzcyI6InNwYy1mcm9udGVuZDAwMUBzcGMtZnJvbnRlbmQiLCJpYXQiOjE1OTk0NzQ5OTF9.HN63jLWIS4baDg1pGsUfT2wTkDgaWDZSPLKH0MciM00T3TZED2v_SxNVsdJ_B3JHqxyf2WIPmWyk_sLvEQ4O9g";
			if(auth_token == null) {
				$.ajax({
				url: "https://cdn.emnify.net/api/v1/authenticate",
				method: "POST",
				data: JSON.stringify({
					"application_token": application_token,
				}),
				headers: {
					'Accept':'application/json',
					//'Authorization':'Bearer ' + application_token,
					//'X-CSRF-TOKEN':'xxxxxxxxxxxxxxxxxxxx',
					'Content-Type':'application/json',
				},
				success: function(data){
					callback(data.auth_token);
				},
				});
			} else {
				callback(auth_token);
			}
		}
		
		function emnifyAPI(api, auth_token, callback)
		{
			$.ajax({
				url: "https://cdn.emnify.net/api/v1/" + api,
				method: "GET",
				headers: {
					'Accept':'application/json',
					'Authorization':'Bearer ' + auth_token,
					//'X-CSRF-TOKEN':'xxxxxxxxxxxxxxxxxxxx',
					'Content-Type':'application/json',
				},
				success: function(data){
					callback(data);
				},
			});
		}
		
		function emnifyInit()
		{
			
			sim_data = [];
			
			getEmnifyToken(function(auth_token){
				emnifyAPI('sim', auth_token, function(res){
					try {
						$.each(res, function(k, s){
							
							let sim_id = s.id;
							let endpoint_id = '';
							let sim_ip = '';
							let s_data = {
								sim_id: s.id,
								sim_ip: '',
								endpoint_id: '',
								endpoint: null,
								stats: null,
								sms: null,
								logs: null,
							};
							
							if(s.endpoint != null) {
								s_data.sim_ip = s.endpoint.ip_address; 
								s_data.endpoint_id = s.endpoint.id; 
								endpoint_id = s.endpoint.id;
								sim_ip = s.endpoint.ip_address;
							}
							
							sim_data.push(s_data);
							
							//console.log(s)
							
							if(endpoint_id == '') {} else {
								emnifyAPI('endpoint/'+endpoint_id+'/connectivity', auth_token, function(res2){
									
									let pdp_context = res2.pdp_context;
									let location_context = res2.location;
									
									if(location_context == null || pdp_context == null) {
										
									} else {
									let operator = location_context.operator;
									let operator_name = "";
									let connection_type = "";
									
									if(pdp_context != null) {
										connection_type = pdp_context.rat_type.description;
									} else
										connection_type = "unknown";
								
									if(operator != null) {
										operator_name = operator.name;
									}
									
									$.each(sim_data, function(k, s){
										if(s.sim_id == sim_id) {
											s.endpoint = res2;
											return false;
										}
									});
									//let network_holder = $(".network_holder[ip='" + sim_ip + "']");
									//network_holder.html(operator_name + " (" + connection_type + ")");
									}
								});	
							}

							emnifyAPI('sim/'+sim_id+'/stats', auth_token, function(res3){	
								
								if(res3.current_month == null || res3.current_month.data == null) {} else {
								let data = res3.current_month.data;
								let last_active = data.last_updated;
								let mb_usage = data.volume;
								let total_cost = data.cost;
								
								//let mb_holder = $(".mb_holder[ip='" + sim_ip + "']");
								//mb_holder.html(Number.parseFloat(mb_usage).toFixed(2) + " MB");		

								//let active_holder = $(".active_holder[ip='" + sim_ip + "']");
								//active_holder.html(last_active);	
								$.each(sim_data, function(k, s){
									if(s.sim_id == sim_id) {
										s.stats = res3;
										return false;
									}
								});	
								}
								
							});	
							
							if(endpoint_id == '') {} else {
								emnifyAPI('endpoint/'+endpoint_id+'/sms', auth_token, function(res4){
									let attached = false;
									let last_sms = "";
									let last_sms_time = "";
									$.each(res4, function(k1, sms){
										if(!attached && sms.sms_type.description == "MT") {
											//console.log(sms)
											attached = true;
											last_sms = sms.payload;
											last_sms_time = sms.delivery_date;	
										}
									});
									$.each(sim_data, function(k, s){
										if(s.sim_id == sim_id) {
											s.sms = res4;
											return false;
										}
									});	
								});	
							}
							
							$.ajax({
								url: "/settings/log",
								data: {ip: s_data.sim_ip},
								type: "POST",
								success: function(data){
									if(data.error)
										return;
									$.each(sim_data, function(k, v){
										if(v.sim_id == s_data.sim_id) {
											v.logs = data.logs;
										}
									});
								}
							});
							
							$('.sim_settings').each(function(){
								$(this).removeAttr('disabled');
							});
							
							
						});					
					} catch(e) {
						
					}
				});
			});	
		}
		
		function getSIMData(ip, callback) {
			try {
				$('.send_sms').attr('ip', ip);
				console.log('Getting Sim ' + ip);
				var found = false;
				$.each(sim_data, function(k, s){
					if(s.sim_ip == ip) {
						
						found = true;
						//console.log(s);
						if(s.endpoint) {
							if(s.endpoint.location && s.endpoint.location.operator) {
								$('.network_holder').html(s.endpoint.location.operator.name);
							}
							if(s.endpoint.pdp_context && s.endpoint.pdp_context.rat_type) {
								$('.network_holder').append(" (" + s.endpoint.pdp_context.rat_type.description + ")");
							}
						}
						if(s.stats && s.stats.current_month && s.stats.current_month.data) {
							$(".mb_holder").html(Number.parseFloat(s.stats.current_month.data.volume).toFixed(2) + "MB");
							$(".active_holder").html(s.stats.current_month.data.last_updated);
						}
						if(s.logs) {
							$('#logs').html('');
							$.each(s.logs, function(k1, log) {
								//console.log(log)
								$('#logs').append('<div class="row-fluid"> <div class="span6"> <font style="font-size:1.1rem;">' + (log.status == 'Online' ? '<i style="color:green;" class="fa fa-check-circle"></i>&nbsp;' : '<i style="color:red;" class="fa fa-times-circle"></i>&nbsp;') + '' + log.status + '</font> </div> <div class="span6"> <font style="font-size:1.1rem;">'+ log.time +'</font> </div> </div>');
							});
						}
						if(s.sms) {
							
							var sms = s.sms;
							var sms_tbl = $('.sms_holder');
							sms_tbl.html("");
							$.each(sms, function(k2, sm){
								//
								sm.submit_date = (sm.submit_date.replace('T', ' ')).replace(".000+0000", "");
								sms_tbl.append('<div class="row-fluid" style="margin-bottom: 3%;padding: 1%; border-radius: 6px;' 
								+ ((sm.status.description == 'BUFFERED' ? 'color: white; background:#ee5f5b;' : '')) + 
								'' + ((sm.status.description == 'DELIVERED' ? 'color: white; background:#62c462;' : '')) + 
								'"> <div class="span5"> <font style="font-size:0.8rem;">' + sm.payload + '</font> </div> <div class="span3"> <font style="font-size:0.8rem;">' + sm.submit_date + '</font> </div><div class="span3">+' + sm.msisdn + '</div> </div>');
								//console.log(sm);
							});
							
						}
	
						return false;
					} 
					
				
					$('#logs').html('-');
					$('.network_holder').html('-');
					$('.active_holder').html('');
					$('.mb_holder').html('');
					
				});
				callback();
			} catch(e) {
				
			}
		}
		
		function warning(msg) {
			$('.error_msg').hide();
			$('.success_msg').hide();
			$('.info_msg').hide();
			$('.warning_msg').fadeIn();
			$('.warning_msg').html(msg);
		}
		
		function info(msg) {
			$('.warning_msg').hide();
			$('.error_msg').hide();
			$('.success_msg').hide();
			$('.info_msg').fadeIn();
			$('.info_msg').html(msg);
		}
		
		function success(msg) {
			$('.warning_msg').hide();
			$('.error_msg').hide();
			$('.info_msg').hide();
			$('.success_msg').fadeIn();
			$('.success_msg').html(msg);
		}
		
		function error(msg) {
			$('.warning_msg').hide();
			$('.success_msg').hide();
			$('.info_msg').hide();
			$('.error_msg').fadeIn();
			$('.error_msg').html(msg);
		}		
		
		function pingSIM(IP, callback) {
			$.ajax({
				url: "/sim/ping",
				data: {IP: IP},
				success: function(data){
					callback(data);
				}
			});
		}
		
		function rebootSIM(IP, type, callback) {
			if(type == 'emnify') {
				var found = false;		
				$.each(sim_data, function(k, s){	
					if(s.sim_ip == IP) {
						$.ajax({
							url: "/sim/reboot",
							data: {IP: IP, type: type, endpoint_id: s.endpoint_id, sim_id: s.sim_id},
							success: function(data){		
								callback(data);
							}
						});
					}
				});
			} else {
				$.ajax({
					url: "/sim/reboot",
					data: {IP: IP, type: type},
					success: function(data){		
						callback(data);
					}
				});
			}
		}
		
		function msgSIM(IP, msg, type, callback) {
			
			var wait = $('.wait_for_delivery').prop("checked");
			var waitTimeout = $('.wait_time').val();
			var getResponse = $('.wait_for_res').prop("checked")
			
			if(type == 'emnify') {
				$.ajax({
					url: "/sim/msg",
					data: {IP: IP, type: type, sms: msg, wait: wait, waitTimeout: waitTimeout, getResponse: getResponse},
					success: function(data){		
						callback(data);
					}
				});
			} else {
				alert('Currently unavailable');
			}
		}
		
		$('.send_sms').on('click', function(){
			var msg = $('.send_sms_msg').val();
			var ip = $(this).attr('ip');
			warning("Sending " + msg + " to " + ip + "...");
			
			msgSIM(ip, msg, 'emnify', function(data){
				console.log(data);
				success("Sent '" + msg + "' to " + ip + ". Status: " + data.delivered + ", Elapsed: " + data.elapsed + "s, Timed-out: " + data.timed_out);
				if(data.response != null) {
					info(data.response);
				}
				$('.send_sms_msg').val('');
				getSIMData(ip, function(){
					
				});
			});
		});
		
		$('.sim_settings').on('click', function(){
			
			var ip = $(this).attr('ip');
			var scheme_name = $(this).attr('scheme_name');
			var scheme_number = $(this).attr('scheme_number');
			
			$("[name='cur_ip']").val(ip);
			$("[name='cur_scheme_name']").val(scheme_name);
			$("[name='cur_scheme_number']").val(scheme_number);
		
			getSIMData(ip, function(){
	
			});
		});
		
		$('.refresh_sim').on('click', function(){
			alert(currentScheme().ip)
			getSIMData(currentScheme().ip, function(){
	
			});
		});
		
		$('.refresh').on('click', function(){
			emnifyInit();
			$.notify('Refreshed Emnify API Data', 'success');
		});
		
		$('.ping').on('click', function(){
			$(this).removeClass('btn-success');
			$(this).addClass('btn-warning');
			//$(this).html($(this).html().replace('Ping', 'Pinging..'));
			var thi = $(this);
			pingSIM($(this).attr('ip'), function(data){
				thi.removeClass('btn-warning');
				thi.addClass('btn-success');
				//thi.html(thi.html().replace('Pinging..', 'Ping'));
				let scheme_number = thi.attr('scheme_number');
				
				$('.last_checked').text('Just now');
				if(data.online == 1) {
					$(".last_status[ip='" + thi.attr('ip') + "']").html("<span class='ping_response_"+scheme_number+"'  style='color: #66CD00;font-weight: bold;'>SIM Online</span> &#8211; <font class='ping_time_"+scheme_number+"' style='font-size:10px;color: grey;'>Just now</font>");
				} else {
					$(".last_status[ip='" + thi.attr('ip') + "']").html("<span class='ping_response_"+scheme_number+"'  style='color: #FF0000;font-weight: bold;'>SIM Offline</span> &#8211; <font class='ping_time_"+scheme_number+"' style='font-size:10px;color: grey;'>Just now</font>");
				}
			});
		});
		
		$('.ping_all').on('click', function(){
			$('.ping').each(function(){
				$(this).trigger('click');
			});
		});
		
		$('.reboot').on('click', function(){
			$(this).removeClass('btn-danger');
			$(this).addClass('btn-warning');
			$(this).html($(this).html().replace('Reboot', 'Rebooting..'));
			var thi = $(this);
			rebootSIM($(this).attr('ip'), $(this).attr('dl_type'), function(data){
				
				if(data == null) {
					thi.removeClass('btn-warning');
					thi.addClass('btn-danger');
					thi.html(thi.html().replace('Rebooting..', 'Reboot'));
					error("Failed to reboot. Please try again");
					return;
				}
				
				thi.removeClass('btn-warning');
				thi.addClass('btn-danger');
				thi.html(thi.html().replace('Rebooting..', 'Reboot'));
				if(data.rebooted == 1) {
					success(data.rebooted_msg);
				} else {
					error(data.rebooted_msg);
				}
			});
		});
		
		$('.toggle_logs').on('click', function(){
			$('.logs').toggle();
		});
		
		
	});
</script>