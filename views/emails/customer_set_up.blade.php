<!DOCTYPE html>
<html lang="en-US">
	<head>
		<meta charset="utf-8">
	</head>
	<body>

		<div>
			Hi {{{ $first_name }}}, <br /><br />
			You are now registered with <a href="http://www.snugzone.biz/">SnugZone.biz</a>. Download the app from the <a href="https://play.google.com/store/apps/details?id=com.snugzone">Play Store</a> or the <a href="https://itunes.apple.com/us/app/snugzone/id635231568?mt=8">App Store.</a><br />
			Login Credentials:
			<ul>
				<li>Email: {{{ $email_address }}}</li>
				<li>Username/Account Number: {{{ $username }}}</li>
				<li>Password: The password that you enter on your first login attempt will become your password.</li>
			</ul>
			If you have any trouble logging in please contact your scheme operator.
			<br /><br />
			Starting balance: {{ $currency_sign }}{{{ $starting_balance }}}
			<br /><br />
			Kind Regards,<br />
			SnugZone Industries Ltd.
		</div>
		
	</body>
</html>
