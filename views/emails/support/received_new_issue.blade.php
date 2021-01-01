<!DOCTYPE html>
<html lang="en-US">
	<head>
		<meta charset="utf-8"/>
	</head>
	
	<body>
	<style>
		.prepago{
			color: rgb(153, 51, 153);
			font-weight: bold;
		}
		.ie{
			colour:rgba(255, 72, 41);
			font-weight: bold;
		}
	</style>
		<div>
		
			Dear {{$name}},
			<br/><br/>
			
			A issue has been created and allocated #{{ $issue->id }}.
			<br/><br/>
			<a style='background: #f8f8f8; padding: 5px; display: block; margin-top: 2%; border-radius: 3px; border: 1px solid #ccc;' 
			href="https://prepagoplatform.com/support/view/{{$issue->id}}">Click here to view & start it.</a>
			<br/>
			
			<p style='background: #ffffff; padding: 20px; /* border-radius: 2px; */ color: #000000; border-top: 1px solid #636363;'>
				<i>
				<b>Title: {{ $issue->issue_title }}</b><br/><br/>
					{{ $issue->issue }}
				</i>
				<br/><br/><br/>
				<i>{{ $issue->operator }} - {{ $issue->operator_email }}</i>
			</p>
			
			<hr>
			
			<br/>
			
			<b>Prepago Platform Support</b>
			<br/>
			<span class="prepago">Prepago</span><span class="ie">.ie</span>
			<br/><br/>
			1 Woodbine Avenue, Blackrock<br/>
			Co.Dublin, Ireland

			<br/><br/>
			<b>Mobile</b> +353 (0) 87 253 4708
			<br/><br/>
			www.Prepago.ie
            <br/><br/>
			<b style='color:rgb(153, 0, 255)'>Prepay; done right.</b>
			
		</div>
		
	</body>
</html>
