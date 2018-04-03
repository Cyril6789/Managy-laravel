<?php session_start();?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title>elFinder 2.1.x source version with PHP connector</title>
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=2" />

		<!-- Require JS (REQUIRED) -->
		<!-- Rename "main.default.js" to "main.js" and edit it if you need configure elFInder options or any things -->

		<?php


		$_SESSION['path'] = Array();
		$_SESSION['path'][0]['path'] = '5/c';
		$_SESSION['path'][0]['alias'] = 'Clients';
		$_SESSION['path'][1]['path'] = '5/i';
		$_SESSION['path'][1]['alias'] = 'Interventions';
		?>

		<script data-main="./main.js" src="//cdnjs.cloudflare.com/ajax/libs/require.js/2.3.2/require.min.js"></script>

	</head>
	<body>

		<!-- Element where elFinder will be created (REQUIRED) -->
		<div id="elfinder"></div>

	</body>
</html>
