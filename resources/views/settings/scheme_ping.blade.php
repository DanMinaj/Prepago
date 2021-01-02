</div>

<div><br/></div>
<h1>Ping Schemes</h1>

</div>
<div class="cl"></div>
<div class="admin2">

	@if(Session::has('successMessage'))
	<div class="alert alert-success" style="padding:2%;font-size:1.2em;">
		{!! Session::get('successMessage') !!}
	</div>
	@endif
	
	@if(Session::has('errorMessage'))
	<div class="alert alert-danger" style="padding:2%;font-size:1.2em;">
		{!! Session::get('errorMessage') !!}
	</div>
	@endif
	
	<a href="/welcome-schemes">
		<button class="btn btn-info">
			<i class="fa fa-arrow-left"></i> Back
		</button>
	</a>
	<hr/>
	<button class="btn btn-primary ping_all">
		Ping all
	</button>
	
	<hr/>
	<table class="table table-bordered">
	<tr>
		<th><b>ID</b></th>
		<th><b>Scheme</b></th>
		<th><b>Last Status</b></th>
		<th><b>Actions</b></th>
		<th><b>Info</b></th>
		<th><b>Ping</b></th>
	</tr>
	@foreach($schemes as $s) 
	
		<tr>
			<td width="10%"> {!! $s->scheme_number !!} </td>
			<td width="10%"> {!! $s->scheme_nickname !!} </td>
			<td class="ping_old_status_{!! $s->scheme_number !!}"  width="20%"> 
				<span class="ping_response_{!! $s->scheme_number !!}" style='{!! $s->statusCss !!}'>{!! $s->status !!}</span> &#8211; <font class="ping_time_{!! $s->scheme_number !!}" style='font-size:10px;color: grey;'>{!! \Carbon\Carbon::parse($s->status_checked)->diffForHumans() !!}</font>
			</td>
			<td width="39%">
				<center>
					<button class="btn btn-primary reboot" scheme_number="{!! $s->scheme_number !!}"> 
					<i class="fa fa-power-off"></i> Reboot</button>
					<button data-toggle="modal" data-target="#create_ticket" class="btn btn-info" scheme_number="{!! $s->scheme_number !!}"> 
					<i class="fa fa-envelope-open-text"></i> Ticket</button>
					<a href="{!! URL::to('settings/' . $s->SIM->ICCID . '/sms') !!} ">
					<button class="btn btn-success"> 
					<i class="fa fa-mobile-alt"></i> SMS
					</button>
					</a>
				</center>
			</td>
			<td width="15%">
				<center> <b> <span style="font-size:11px;" class="reboot_response_{!! $s->scheme_number !!}">&#8211;</span> </b> </center>
			</td>
			<td width="10%">
				<button class="btn btn-warning ping" scheme_number="{!! $s->scheme_number !!}"> 
					<i class="fa fa-sync"></i></button>
			</td>
		</tr>
		
	@endforeach
	</table>
	
<div id="create_ticket" class="modal fade" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Create Eseye Ticket</h4>
      </div>
      <div class="modal-body">
     	<div class="alert alert-success alert-block" id="ticket_msg" style="display:none;">
			Successfully created ticket!
		</div>
		<div class="alert alert-danger alert-block" id="ticket_error" style="display:none;">
			
		</div>
		
		<h4> Quick ticket presets </h4>
		<div class="form-group">
			<label for="ticket_preset"><font color='red'></font> (Optional) Preset</label>
			<select class="form-control" name="ticket_preset" style="width:98%;" id="ticket_preset" >
				<option selected value="0">None</option>
				<option value="1">SIM Offline After connecting to Meteor</option>
			</select>
		  </div>
		  <hr/>
		  
		  
		  <h4 id="custom_ticket"> Custom ticket </h4>
		  <div class="form-group">
			<label for="ticket_subject"><font color='red'>*</font>  Subject</label>
			<input type="text" class="form-control" name="ticket_subject" id="ticket_subject" placeholder="Subject">
		  </div>
		  
		  <div class="form-group">
			<label for="ticket_comment"><font color='red'>*</font>  Comment</label>
			<textarea style="width:97%;height:200px;"  class="form-control" placeholder="Comment" name="ticket_comment" id="ticket_comment"></textarea>
		  </div>
		  
		   <div class="form-group">
			<label for="ticket_type"><font color='red'>*</font>  Type</label>
			<select class="form-control" name="ticket_type" id="ticket_type" >
				<option selected value="problem">Problem</option>
				<option value="incident">Incident</option>
				<option value="question">Question</option>
				<option value="task">Task</option>
			</select>
		  </div>
		   <div class="form-group">
			<label for="ticket_priority"><font color='red'>*</font>  Priority</label>
			<select class="form-control" name="ticket_priority" id="ticket_priority" >
				<option value="low">Low</option>
				<option value="normal">Normal</option>
				<option selected value="high">High</option>
				<option value="critical">Critical</option>
			</select>
		  </div>
		  
	
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-success"  id="create_ticket_submit"><i class="fa fa-envelope-open-text"></i>  Create Ticket</button>
        <button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
      </div>
    </div>

  </div>
</div>
<script>
    
	$(function(){
		
		var presets = [
			{},
			{
				subject: "Preset 1 itle",
				comment: "comment",
				type: "problem",
				priority: "high",
			}
		];
		var ticket_modal = $('#create_ticket');
		var ticket_preset = $('#ticket_preset');
		
		var ticket_subject = $('#ticket_subject');
		var ticket_comment = $('#ticket_comment');
		var ticket_type = $('#ticket_type');
		var ticket_priority = $('#ticket_priority');
	
		var ticket_msg = $('#ticket_msg');
		var ticket_error = $('#ticket_error');
		
		function hide_create_ticket(){
			ticket_modal.modal('hide');
		}
		
		ticket_preset.on('change', function(){
			var val = $(this).val();
			if(val != 0) {
				
				$('#custom_ticket').hide();
				$('label[for="ticket_subject"]').hide();
				ticket_subject.val('');
				ticket_subject.hide();
				$('label[for="ticket_comment"]').hide();
				ticket_comment.val('');
				ticket_comment.hide();
				$('label[for="ticket_type"]').hide();
				ticket_type.val('');
				ticket_type.hide();
				$('label[for="ticket_priority"]').hide();
				ticket_priority.val('');
				ticket_priority.hide();
				
				
				var preset = presets[parseInt(val)];
				if(preset) {
					console.log(preset);
					console.log("Loaded preset #" + parseInt(val));
					ticket_subject.val(preset.subject);
					ticket_comment.val(preset.comment);
					ticket_type.val(preset.type);
					ticket_priority.val(preset.priority);
				}
				
			}
			else {
				$('#custom_ticket').show();
				$('label[for="ticket_subject"]').show();
				ticket_subject.val('');
				ticket_subject.show();
				$('label[for="ticket_comment"]').show();
				ticket_comment.val('');
				ticket_comment.show();
				$('label[for="ticket_type"]').show();
				ticket_type.val('');
				ticket_type.show();
				$('label[for="ticket_priority"]').show();
				ticket_priority.val('');
				ticket_priority.show();
			}
			
			
			
		});
		
		$('#create_ticket_submit').on('click', function(){
			
			var subject = ticket_subject.val();
			var comment = ticket_comment.val();
			var type = ticket_type.val();
			var priority = ticket_priority.val();

			
			console.log("ticket_subject: " + subject);
			console.log("ticket_comment: " + comment);
			console.log("ticket_type: " + type);
			console.log("ticket_priority: " + priority);
			
			$.ajax({
				
				url: '/settings/create_eseye_ticket',
				method: "POST", 	
				data: {subject: subject, comment: comment, type: type, priority: priority},
				success: function(data){
					
					if(data.error) {
						ticket_error.html(data.error);
						ticket_error.show();
						return;
					}
					
					ticket_error.hide();
					ticket_msg.show();
					
					setTimeout(function(){
						hide_create_ticket();
					},2000);
					
				}, error: function(e){
					
					ticket_error.html("An error occured: " + e.responseText);
					ticket_msg.hide();
					ticket_error.show();
				
					console.log(e);
					console.log(e.responseText);
				}
				
			});
			
		});
	
		$('.ping').on('click', function(){
			ping_scheme($(this));
		});
		
		$('.reboot').on('click', function(){
			reboot_scheme($(this));
		});
		
		$('.ping_all').on('click', function(){
			$('.ping').each(function(){
				$(this).trigger('click');
			});
		});
		
		function ping_scheme(div) {
			
			//
			var scheme_id = div.attr('scheme_number');
			var response_div = $('.ping_response_' + scheme_id);
			var response_time_div = $('.ping_time_' + scheme_id);
			var old_status_div = $('.ping_old_status_' + scheme_id);
			
			//old_status_div.html("<center>&#8211;</center>");
			//old_status_div.css({ backgroundColor: "#f5f5f5" });
			//
			response_div.html('<font color="orange">Pinging..</font>');
			$.ajax({
				
				url: "/settings/ping/" + scheme_id,
				data: {},
				method: "POST",
				success: function(data){
					
					response_div.html(data);
					response_time_div.html("Just now");
					
					
				}, error: function(){
					
					console.log("error");
					
				}
			});
		}
		
		function get_scheme_status(div, callback) {
			
			var scheme_id = div.attr('scheme_number');
			var response_div = $('.ping_response_' + scheme_id);
			var response_time_div = $('.ping_time_' + scheme_id);
			var old_status_div = $('.ping_old_status_' + scheme_id);
			
			$.ajax({
				
				url: "/settings/ping/" + scheme_id + "/0",
				data: {},
				method: "POST",
				success: function(data){
					callback(data);
				}, error: function(){
					
					console.log("error");
					
				}
			});
			
		}
		
		function reboot_scheme(div) {
			
		//	
			var step0 = false;
			var step1 = false;
			var step2 = false;
			var check_success = null;
			var check_back_up = null;
			var start_status = 0;
			var scheme_id = div.attr('scheme_number');
			var response_div = $('.reboot_response_' + scheme_id);
			var response_time_div = $('.ping_time_' + scheme_id);
			var old_status_div = $('.ping_old_status_' + scheme_id);
			
			// checking initial status
			response_div.html("please wait..");
			
			get_scheme_status(div, function(data){
				
				if(data.indexOf('Online') !== -1) {
					//response_div.append("<span style='color:#66CD00;font-size:11px;'>Online!</span><br/>");
					start_status = 1;
				} else {
					//response_div.append("<span style='color:red;font-size:11px;'>Offline!</span><br/>");
					start_status = 0;
				}
				
				$.ajax({
					url: "/settings/reboot_scheme",
					method: "POST",
					data: {scheme_id: scheme_id,},
					success: function(data){
						
						response_div.html("");
						response_div.append("sent reboot command..");
			
						//response_div.append("reboot command sent..");
						//response_div.append("checking current status..");
						check_success = setInterval(function(){
							if(step1) return;
							
							get_scheme_status(div, function(data){
								var new_start_status = 0; 
								if(data.indexOf('Online') !== -1) {
									new_start_status = 1;
								} else {
									new_start_status = 0;
								}
								
								// reboot sucessfully shut it down
								if( new_start_status == 0 || start_status == 0 ) {
									
									if(step1) return;
									
									response_div.append("<span style='color:#66CD00;font-size:11px;'>success!</span><br/>");
									console.log("stopping interval!");
									step1 = true;
									
									clearInterval(check_success);
									
									response_div.append("booting..");
									
									check_back_up = setInterval(function(){
										if(step2) return;
										
										get_scheme_status(div, function(data){
											var new_up_status = 0; 
											if(data.indexOf('Online') !== -1) {
												new_up_status = 1;
											} else {
												new_up_status = 0;
											}
											if(new_up_status == 1) {
												if(step2) return;
												response_div.append("<span style='color:#66CD00;font-size:11px;'>success!</span><br/>");
												clearInterval(check_back_up);
												step2 = true;
												return;
											} else {
												console.log("waiting to come back up!");
											}
										});
									}, 8000);
									
									return;
								} else {
									console.log("continuing interval");
								}
								
							});
						
						}, 2000);
						
					},
					error: function(){
						console.log("Error in reboot_scheme()");
					},
					
				});
			
			});
			
					
			
		}
	});
	
</script>

</div>