<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="keywords" content="">
	<meta name="description" content="">
	<title>Data Logger Test</title>
	<script src="http://code.jquery.com/jquery-1.11.0.min.js"></script>
</head>
<body>

<table>
	
	<tr>
		<th>Function</th>
		<th>&nbsp;</th>
		<th>Result</th>
	</tr>

	<tr>
		<td>meter_information_upload</td>
		<td>
			<input type="button" onclick="meter_information_upload()" value="Send JSON">
		</td>
		<td id="meter_information_upload_result">
			&nbsp;
		</td>
	</tr>

	

</table>


<script>


	function meter_information_upload()
	{
		var json = {
				  "ID": "(raspberry pi ID)",
				  "Password": "(raspberry pi Password)",
				  "Prefix": "(raspberry pi prefix)",
				  "Meters": {
				    "meter_type": "M-Bus",
				    "meter_number": "00802717",
				    "install_date": "2014-02-12",
				    "scu_type": "A",
				    "scu_number": "12345678",
				    "scu_port": "2",
				    "address_number": "1",
				    "address_name": "Woodbine Avenue",
				    "meter_make": "danfoss",
				    "meter_model": 1,
				    "meter_manufacturer": "fossy",
				    "meter_baud_rate": "2400",
				    "HIU_make": "",
				    "HIU_model": "",
				    "HIU_manufacturer": "",
				    "valve_make": "",
				    "valve_model": "",
				    "valve_manufacturer": ""
				  }
				};

		send_test('meter_information_upload', json);
	}
		
	
	function send_test(ftn, data){
		
		var jax = new XMLHttpRequest();
		jax.open("POST", "http://162.13.37.69/prepago_admin/data_logger/"+ftn, true);
		jax.setRequestHeader("Content-Type", "application/json");
		jax.send(JSON.stringify(data));
		jax.onreadystatechange = function() {
		    if(jax.readyState === 4) { jQuery("#"+ftn+"_result").html(jax.responseText); }
		}	

	}



</script>

</body>
</html>