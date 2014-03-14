<?php
	error_reporting(E_ALL);
	include 'connectinfo.php';
	include 'Poi/Poi-v0.2.php';

	//Routes
	const POINTS = "points";
	const USERS = "users";


	header('Content-Type: application/json');

	$poi = new Poi(DATABASE_HOSTNAME,DATABASE_DATABASE,DATABASE_USER,DATABASE_PASSWORD);

	$uri_parts = explode('?', $_SERVER['REQUEST_URI'], 2);

	//Contains URI route
	$request = array_values(array_diff(explode("/", $uri_parts[0]), array("","api","poi")));
	
	//Request method
	$method = $_SERVER['REQUEST_METHOD'];

	$response_code = 200;

	switch ($method) {
		case 'PUT':
			$response_code = rest_put($request);
			break;
		case 'POST':
			$response_code = rest_post($request);  
			break;
		case 'GET':
			$response_code = rest_get($request);  
			break;
		case 'DELETE':
			$response_code = rest_delete($request);  
			break;
		default:
			$response_code = rest_error($request);  
			break;
	}

	http_response_code($response_code);

	function rest_put($request) {
		global $poi;
		if($request[0] == POINTS && isset($request[1]) && is_numeric($request[1])) {
				$response = $poi->update(file_get_contents("php://input"));
				if ($response != -1) {
					echo $response;
					return 200;
				}
		}
		return 404;
	}

	function rest_post($request) {
		global $poi;
		if($request[0] == POINTS && !isset($request[1])) {
				$response = $poi->insert(file_get_contents("php://input"));
				if ($response != -1) {
					echo $response;
					return 201;
				}
		}
		return 404;
	}

	function rest_get($request) {
		global $poi;
		$response = -1;
		if($request[0] == POINTS) {
			if(isset($request[1]) && is_numeric($request[1])) {
				$response = $poi->getPoint($request[1]);
			} else {
				if (!isset($request[1])) {
					if(isset($_GET) && !empty($_GET)) {
						$response = $poi->search($_GET);
					} else {
						$response = $poi->getPoints();
					}
				}
			}
		} elseif ($request[0] == USERS) {
			if(isset($request[1]) && is_numeric($request[1])) {
				$response = $poi->get(null,$request[1]);
			} else {
				if (!isset($request[1])) {
					$response = -1;
				}
			}
		}
	}

	function rest_delete($request) {
		global $poi;
		if($request[0] == POINTS && isset($request[1]) && is_numeric($request[1])) {
			echo "delete point";
			//return 200;
		}
		return 404;
	}
?>