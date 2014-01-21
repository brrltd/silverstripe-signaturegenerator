<!DOCTYPE html>
<html lang="$ContentLocale">
	<head>
		<% base_tag %>
		<title>Email Form</title>
		<meta charset="utf-8" />
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<link rel="shortcut icon" href="favicon.ico" />
		<!--[if lt IE 9]>
		<script src="//html5shiv.googlecode.com/svn/trunk/html5.js"></script>
		<![endif]-->
		<% require themedCSS('signature', 'signaturegenerator') %>
	</head>
	<body class="$ShowClassName">
		<div class="Header">
			<div class="Instructions">
				<p>Please enter your details below in order to generate an email signature</p>
			</div>
		</div>
		<div class="Container">
			<div class="Form">
				$Form
			</div>
		</div>
	</body>
</html>
