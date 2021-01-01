
</div>

<div><br/></div>
<h1>Test requests</h1>


<div class="admin2">

	
	<table width="100%">
		
		<tr>
			
			<td colspan='3'>
				<select id="request_preset" style="width:100%">
					<option>No preset</option>
					<option>datatable/deviceMeasurementserie</option>
				</select>
			</td>
			
		</tr>
		
		<tr>
			<td width="30%">
				<select name="request_method">
					<option>GET</option>
					<option>POST</option>
				</select>	
			</td>
			
			<td width="50%">
				<input type="text" style="width:80%" name="request_data">	
			</td>
			
			
			<td width="20%">
				<select name="request_datatype">
					<option>application/x-www-form-urlencoded; charset=UTF-8</option>
					<option>application/json</option>
				</select>
			</td>
			
		</tr>
		
		<tr>
		
			<td colspan='3'>
				<input name="request_url" type="text" style="width:98.5%" placeholder="http://testurl.com/directory" value="http://">		
			</td>
		
		</tr>
		
		<tr>
			
			<td colspan='3'>
				<input type="submit" id="submit_request" style="width:100%" class="btn btn-primary">
			</td>
			
		</tr>
		
	</table>
		
	<br/>
	<br/>
	<hr/>
	<br/>
	<br/>
	
	<table width="100%">
		
		<tr>
			
			<td>
				<textarea id="response" style="width:100%;height:300px;"></textarea>
			</td>
			
		</tr>
		
	</table>
	
	
	

</div>

<script>
	$(function(){
		
		var default_preset_IP = "89.101.112.68";
		var default_preset_port = 47809;
		var default_secondary_address = "";
		var default_language = "EN";
		
		$('#request_preset').on('change', function(e){
			
			////	
			var val = $(this).val();
			
			if(val != "No preset") {
				
				$('select[name=request_method]').prop("disabled", "disabled");
				$('select[name=request_datatype]').prop("disabled", "disabled");
				$('input[name=request_data]').prop("disabled", "disabled");
				$('input[name=request_url]').prop("disabled", "disabled");
				
				
			}
			else {
				
				$('select[name=request_method]').prop("disabled", false);
				$('select[name=request_datatype]').prop("disabled", false);
				$('input[name=request_data]').prop("disabled", false);
				$('input[name=request_url]').prop("disabled", false);
					
			}
			
		});
		
		
	
		function handlePreset(preset) {
				
			switch(preset) {
				
				case 'datatable/deviceMeasurementserie':
					
					var url = "http://" + default_preset_IP + ":" + default_preset_port + "/rest/datatable/deviceMeasurementserie/secondaryAddress/" + default_secondary_address + "/language/" + defaut_language;
					
					$.ajax({
						
						url: url,
						type: 'GET',
						
					});
					
				break;
				
				default:
					console.log('Preset ' + preset + ' not found!');
				break;	
			}
			
		}
		
		$('#submit_request').on('click', function(){
			
			if($('#request_preset').val() != 'No Preset') {
				handlePreset($('#request_preset').val());
				return;
			}
			
			var request_method = $('select[name=request_method]').val().toLowerCase();
			var request_datatype = $('select[name=request_datatype]').val().toLowerCase();
			var request_data = $('input[name=request_data]').val();
			var request_url = $('input[name=request_url]').val().toLowerCase();
			
			if(request_url.indexOf('http://') === -1 && request_url.indexOf('https://') == -1) {
				
				if(request_url.indexOf('.') !== -1) {
					request_url = "http://" + request_url;
				} else {
					if(request_url.indexOf('/') == -1)
						request_url = "/" + request_url;
				}
			}
			
			if(request_datatype == 'application/json' || request_method == 'post') {
				
				
				var data = request_data;
				var arr = {};
				
				if(data.indexOf(',') !== -1)
				{
					data = data.split(',');

					for(var i=0; i<data.length; i++) {
						
						var parts = data[i];
						
						if(parts.indexOf(':') !== -1) {
							parts = data[i].split(':');
							var k = parts[0];
							var v = parts[1];
							
							arr[k] = v;
						} else {
							arr[i] = v;
						}
					}
				} else {
					
					var parts = data;
						
					if(parts.indexOf(':') !== -1) {
						parts = data.split(':');
						var k = parts[0];
						var v = parts[1];
						arr[k] = v;
					} else {
						arr[0] = v;
					}
					
				}
				
				
				if(request_method == 'post') {
					request_data = arr;
				}
				
				if(request_datatype == 'application/json') {
					request_data = JSON.stringify(arr);
				}
		
				console.log(request_data)
				
			}
			
			$.ajax({
				//
				url: request_url,
				type: request_method,
				contentType: request_datatype,
				data: request_data,
				success: function(data){
					
					if(data.length <= 0) {
						data = "No data returned!";
					}
					
					$('#response').html(data);
					
				}, 
				error: function (request, status, error) {
				
					
					$('#response').html(status);
					
					console.log(error)
					
				},
			});
			
			
		});
		
		
	});
</script>