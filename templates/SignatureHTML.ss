<html $Embed.XMLNS>
	<head>
		<meta charset="utf-8" />
		$Embed.Head
	</head>
    <body style="word-wrap: break-word; -webkit-nbsp-mode: space; -webkit-line-break: after-white-space; font-size: 10pt; font-family: Arial; color: black;">
		&nbsp;<br />
		<table border="0" cellpadding="0" cellspacing="0">
            <tr>
				<td>
					$Name
				</td>
			</tr>
			<% if Position %>
				<tr>
					<td>
						$Position
					</td>
				</tr>
			<% end_if %>
			<tr>
				<td>
					<table border="0" cellpadding="0" cellspacing="0">
						<% if DirectDial %>
							<tr>
								<td>DDI</td>
								<td>$DirectDial</td>
							</tr>
						<% end_if %>
						<% if Mobile %>
							<tr>
								<td>MOB</td>
								<td>$Mobile</td>
							</tr>
						<% end_if %>
						<% if Email %>
							<tr>
								<td>EML</td>
								<td><a href="mailto:$Email.ATT">$Email</a></td>
							</tr>
						<% end_if %>
					</table>
				</td>
			</tr>
        </table>
    </body>
</html>
