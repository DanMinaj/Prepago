<!DOCTYPE html>
<html lang="en-US">
	<head>
		<meta charset="utf-8">
	</head>
	<body>
		<div>
		
			Dear {{ $name }},
			<br/><br/>
			
			<div style='display:block;'>
			Your issue #{{ $issue->id }} has been seen by a member of support and is being worked on.
			</div>
			
			<br/><br/>
			
			<a style='background: #f8f8f8; padding: 5px; display: block; margin-top: 2%; border-radius: 3px; border: 1px solid #ccc;' 
			href="https://prepagoplatform.com/support/view/{{$issue->id}}#latest_reply">Click here to view it.</a>
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
