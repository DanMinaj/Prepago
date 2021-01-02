<!DOCTYPE html>
<html lang="en-US">
	<head>
		<meta charset="utf-8">
	</head>
	<body>	
		<div>
		
			Dear {{ $name }},
			<br/><br/>
			
			<font color='green' style='font-style:bold;'><a href="{{ URL::to('support/view', ['id' => $issue->id])  }}">Your issue #{{ $issue->id }} has been marked as solved.</a></font>
			
			<br/><br/>
			
			@if($issue->customer_ID != 0)
			<a style='background: #ffffff; font-weight: bold; text-decoration: none; color: black; border-radius: 2px; border: 1px solid #666;' href="https://prepagoplatform.com/customer_tabview_controller/show/{{$issue->customer_ID}}">&lt;&lt; Go back to the customer {{$issue->customer_ID}}'s page to check</a>
			@endif
			
			<br/>
			
			<p style='background: #ffffff; padding: 20px; /* border-radius: 2px; */ color: #000000; border-top: 1px solid #636363;'>
				<i>
				<b>Title: {{ $issue->issue_title }}</b><br/><br/>
					{{ $issue->issue }}
				</i>
				<br/><br/><br/>
				<i>{{ $issue->operator }} - {{ $issue->operator_email }}</i>
			</p>
			
			@include('emails.includes.footer')
			
		</div>
		
	</body>
</html>
