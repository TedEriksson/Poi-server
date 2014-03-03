<?php
	$files = glob('docs/*.{html}', GLOB_BRACE);
	$docs = array();
	foreach ($files as $key => $value) {
		$docs[basename($value, ".html")] = file_get_contents($value);
	}
?>
<!DOCTYPE html>
<html>
<head>
	<title>Poi Docs</title>
	<link rel="stylesheet" type="text/css" href="../web/main.css">
</head>
<body>
	<header>
		<h1>Documentation</h1>
	</header>
	<nav>
		<h2>Navigation</h2>
		<ul>
			<?php
				foreach ($docs as $key => $value) {
					?>
					<li><a href=<?php echo("'#".str_replace(' ','',$key)."'");?>><?php echo($key);?></a></li>
					<?php
				}
			?>
		</ul>	
	</nav>
	<div class="blocks">
	<?php
				foreach ($docs as $key => $value) {
					?>
					<div class="block light" id=<?php echo("'".str_replace(' ','',$key)."'");?>><?php echo($value);?></div>
					<?php
				}
			?>
	</div>
</body>
</html>