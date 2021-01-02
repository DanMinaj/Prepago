<!DOCTYPE html>
<html lang="en-US">
	<head>
		<meta charset="utf-8">
	</head>
	<body>
		Dear administrator,
		
		<br/>
		<br/>
		<?php
			$status = ucfirst(str_replace('for_', '', $watch->watch_type));
			if($status == 'Online') {
				$status = "has just come <font color='green'>Online!</font>";
			}
			if($status == 'Offline') {
				$status = "has just went <font color='green'>Offline :(</font>";
			}
		?>
		<p> 
			This email is to inform you that <b>{{ ucfirst($scheme->scheme_nickname) }}</b> {{ $status }}</b>
		</p>
		
		<br/>
		
		
		<a style='background: #f8f8f8; padding: 5px; display: block; margin-top: 2%;margin-right:2%; border-radius: 3px; border: 1px solid #ccc;' 
		href="https://prepagoplatform.com/settings/ping">Manage SIMS</a>
		
		<a style='background: #f8f8f8; padding: 5px; display: block; margin-top: 2%; border-radius: 3px; border: 1px solid #ccc;' 
		href="https://prepagoplatform.com/system_reports/sim_reports">View SIM Graph</a>
		
	</body>
</html>
