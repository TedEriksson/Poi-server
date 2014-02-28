<? 
	include 'Poi/Poi.php';

	$poi = new Poi("127.0.0.1","Poi","root","");

	
	$result;
	if (isset($_GET['id'])) {
		echo $_GET['id'];
		$result = $poi->get($_GET['id']);
	} else {
		$result = $poi->get();
	}

	echo $result;
?>