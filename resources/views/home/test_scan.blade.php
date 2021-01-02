
</div>

<div><br/></div>
<h1>Test Scan</h1>


<div class="admin2">

	<div class="row-fluid">
		<div class="span12">
			<font style="font-size:16px;margin-bottom:7%;">Paste list of meters & scu's you wish to scan</font>
		</div>
	</div>
	<div class="row-fluid">
		<div class="span8">
			<div class="row-fluid">
				<div class="span12">
					<div class="well">
						<textarea id="input" style="width: 97%; height: 200px;" placeholder="List of Primary SCU & their corresponding Meters.&#10;Example:&#10;02004009 74700042&#10;02004008 74700059"></textarea>
					</div>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<h3> Running scans </h3>
					<div class="well">
						<table width="100%" class="table table-bordered">
							<thead>
								<tr>
									<th><b>#</b></th>
									<th><b>Username</b></th>
									<th><b>Progress</b></th>
									<th><b>Expected</b></th>
									<th><b>Temp change</b></th>
								</tr>
							</thead>
							<tbody>
							@foreach($scans as $s) 
								<tr>
									<td>{{ $s->id }}</td>
									<td>{{ $s->username }}</td>
									<td>{{ $s->progress }}%</td>
									<td><i class='fa fa-exchange-alt'></i> {{ $s->scu_end_status }}/{{ $s->expected_temp_change }}&deg;C</td>
									<td>{{ abs($s->meter_start_temp-$s->meter_end_temp) }}&deg;C  ({{ $s->meter_start_temp }} -> {{ $s->meter_end_temp }})</td>
								</tr>
							@endforeach
							</tbody>
						</table>
					</div>
				</div>
			</div>	
		</div>
		<div class="span4">
			<div class="row-fluid">
				<div class="span12">
					<center>
						<button type="button" id="scan" disabled class="btn btn-large btn-warning">Start</button>
					</center>
					<br/>
				</div>
			</div>
			<div class="row-fluid">
			<b>Scheme:</b>
				<select style="text-align: center; padding: 2%; width: 95%;" id="scheme" name="scheme">
					@foreach($schemes as $s)
						<option value="{{ $s->scheme_number }}">{{ $s->scheme_nickname }}</option>
					@endforeach
				</select>
			</div>
			<div class="row-fluid">
			<b>Refresh rate (minutes):</b>
				<div class="span12">
					<input type="text" style="text-align: center; padding: 2%; width: 95%;" placeholder="Refresh rate (minutes)" value="" id="refresh_rate"/>
				</div>
			</div>
			<div class="row-fluid">
			<b>Expected &deg;C change:</b>
				<div class="span12">
					<input type="text" style="text-align: center; padding: 2%; width: 95%;" placeholder="Expected &deg;C change" id="expected_change" value="10"/>
				</span>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<div class="well">
						<b>Output</b>&nbsp;(<span class="output-count"></span> entries)
						<hr/>
						<span id="output"></span>
					</div>
				</div>
			</div>
			<div id="error-row" style="display:none" class="row-fluid">
				<div class="alert alert-danger alert-block" id="error">
				
				</div>
			</div>
			<div id="warning-row" style="display:none" class="row-fluid">
				<div class="alert alert-warning alert-block" id="warning">
				
				</div>
			</div>
		</div>
	</div>
</div>

<div id="error-list" class="modal fade" role="dialog">
	  <div class="modal-dialog">
	  
	  <!-- Modal content-->
	  <div class="modal-content">
		
	  <div class="modal-header">
		<button type="button" class="close" data-dismiss="modal">&times;</button>
		<h4 class="modal-title">Errors</h4>
	  </div>
	
	  <div id="error-list-body" class="modal-body">
			
	  </div>
	  
	  <div class="modal-footer">
		<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
	  </div>
	  
	  
	</div>


<script>
	$(function(){
		
		
		var input_box = $('#input');
		var start_btn = $("#scan");
		var default_refresh_rate = 10;
		var default_refresh_after_submit = 2;
		
		input_box.on('keyup', function(){
			
			var list = getlist(getinput());
			var scheme = getscheme();
			
			req({list: list, action: 'check', scheme: scheme}, function(data){
				
				if(data.errors == undefined)
					return;
				
				if(isfatal(data.errors)) {
					error(data.errors[0].msg, data.errors);
					return;
				}
				
				if(data.errors.length > 0) {
					error("There are <b>" + data.errors.length + "</b> errors regarding the list you've entered. <a data-toggle='modal' id='show-errors' data-target='#error-list' href=''>Click here to view full details.</a>", data.errors);
					start_btn.attr("disabled", true);
				}
				else {
					$('.output-count').html(list.length);
					$('#error-row').hide();
					start_btn.removeAttr("disabled");
				}
				
			});
		});
		
		start_btn.on('click', function(){
			
			var list = getlist(getinput());	
			var refresh_rate = parseInt(jQuery.trim($('#refresh_rate').val()));
			var expected_change = parseInt(jQuery.trim($('#expected_change').val()));
			
			var scheme = getscheme();
			
			req({list: list, action: 'start', refresh_rate: refresh_rate, expected_change: expected_change, scheme: scheme}, function(data){
				
				if(data.already_running.length > 0) {
					var body = "<b>" + data.already_running.length + "</b> entries already have a scan in progress!:<br/>";
					$.each(data.already_running, function(k, v){
						body += "<b>Line " + v.line + "</b>: " + v.scu + " " + v.meter + "<br/>";
					});
					error(body, []);
				}
				
				if(data.duplicates.length > 0) {
					var body = "Skipped " + data.duplicates.length + " duplicate(s): <br/>";
					$.each(data.duplicates, function(k, v){
						body += "<b>Line " + v.line + "</b>: " + v.scu + " " + v.meter + "<br/>";
					});
					warning(body);
				}
				
				//console.log(Object.keysdata.parsed_list)
				append_output("..<b>loaded " + Object.keys(data.parsed_list).length + "</b> unique entries.");
				append_output("<font color='#4bab4b'>..<b>started</b> a new scan with refresh rate of <b>" + refresh_rate + " mins, and an expected change of " + expected_change + "&deg;</b>.</font>");
				
				if(data.already_running.length <= 0) {
					setTimeout(function(){
						window.location.reload();
					}, default_refresh_after_submit * 1000);
				}
			//	output(data);
				
			});
			
		});
		
		$('#show-errors').on('click', function(){
			$('#error-list').modal('show');
		});
		
		function getscheme()
		{
			return $('#scheme').val();
		}
		
		function getinput()
		{
			return $('#input').val();
		}
	
		function getlist(input)
		{
			var list = [];
			var lines = input.split("\n");
			
			$.each(lines, function(k, v){
				var parts = v.split(" ");
				var scu = parts[0];
				var meter = parts[1];
				list.push({
					scu: jQuery.trim(scu),
					meter: jQuery.trim(meter),
				});
			});
			
			return list;
		}
		
		function error(msg, errors)
		{
			var error_row = $('#error-row');
			var error_div = $('#error');
			error_div.html(msg);
			
			var body = "";
			$.each(errors, function(k, v){
				
				body += '<div class="alert alert-danger alert-block" id="error">';
					body += "<b>" + v.line + "</b>";
					body += "<br/><br/>";
					body += v.msg;
				body += '</div>';
			});
			$('#error-list-body').html(body);
				

			error_row.show();
		}
		
		function warning(msg)
		{
			var warning_row = $('#warning-row');
			var warning_div = $('#warning');
			warning_div.html(msg);

			warning_row.show();
		}
		
		function output(msg)
		{
			
			var output_div = $('#output');
			output_div.html(msg);
			output_div.html(msg);
			output_div.show();
			
		}
		
		function append_output(msg)
		{
			
			var output_div = $('#output');
			var current_val = output_div.val();
			
			if(jQuery.trim(current_val) == '')
				current_val = "<br/>";
			
			output_div.append(current_val + "" + msg + "<br/>");
			
		}
		
		function isfatal(errors) 
		{
			var fatal = false;
			
			$.each(errors, function(k,v) {
				if(v.msg.indexOf('Fatal') !== -1)
					fatal = true;
			});
			
			return fatal;
		}
		
		function req(data, callback)
		{
			$('#output').html('');
			$('#errors').html('');
			
			$.ajax({
				
				url: "/settings/test",
				method: "POST",
				data: data,
				dataType: "json",
				success: function(data){
					callback(data);
				}, error: function(){
					callback('error');
				}
			});
		}
		
	});
</script>