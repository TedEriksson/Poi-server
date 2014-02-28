<!DOCTYPE html>
<html>
<head>
	<title>Poi Server</title>
	<link href='http://fonts.googleapis.com/css?family=Allerta' rel='stylesheet' type='text/css'>
	<link rel="stylesheet" type="text/css" href="./main.css">
	<link rel="shortcut icon" href="/favicon.ico" type="image/x-icon">
	<link rel="icon" href="/favicon.ico" type="image/x-icon">
</head>
<body>
	<script type="text/javascript" src="./jquery.js"></script>
	<script type="text/javascript" src="./main.js"></script>
	<header>
		<h1>Poi Server</h1>
		<div class="subtitle">By Ted Eriksson</div>
	</header>
	<pre class="half" id="results"></pre>
	<div class="half">
		<input id="pointID" placeholder="Point ID">
		<input id="pointName" placeholder="Point Name">
		<input id="pointLng" placeholder="Point Long">
		<input id="pointLat" placeholder="Point Lat">
		<textarea id="pointMsg" placeholder="Point Message" rows="4"></textarea>
		<ul id="pointOptions">
			<li id="get" class="btn">Get Point by ID</li>
			<li id="update" class="btn">Update Point by ID</li>
			<li id="delete" class="btn">Delete Point by ID</li>
		</ul>

		<a id="pointCreate" class="btn" href="#">Create Point</a>
	</div>
	<footer>
		<div class="subtitle">Copyright 2013 - 2014</div>
	</footer>
</body>
</html>