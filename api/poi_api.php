<?php
	error_reporting(E_ALL);
	include 'Poi/Poi.php';
	include 'connectinfo.php';

	//Routes
	const POINTS = "points";
	const USERS = "users";


	header('Content-Type: application/json');

	$poi = new Poi(DATABASE_HOSTNAME,DATABASE_DATABASE,DATABASE_USER,DATABASE_PASSWORD);

	$uri_parts = explode('?', $_SERVER['REQUEST_URI'], 2);

	//Contains URI route
	$request = array_values(array_diff(explode("/", $uri_parts[0]), array("","api","poi")));
	//var_dump($segments);


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
		if($request[0] == POINTS && isset($request[1]) && is_numeric($request[1])) {
				$response = $this->poi->update(file_get_contents("php://input"));
				if ($response != -1) {
					echo $response;
					return 200;
				}
		}
		return 404;
	}

	function rest_post($request) {
		if($request[0] == POINTS && !isset($request[1])) {
				$response = $this->poi->insert(file_get_contents("php://input"));
				if ($response != -1) {
					echo $response;
					return 201;
				}
		}
		return 404;
	}

	function rest_get($request) {
		$response = -1;
		if($request[0] == POINTS) {
			if(isset($request[1]) && is_numeric($request[1])) {
				$response = $this->poi->get($request[1]);
			} else {
				if (!isset($request[1])) {
					if(isset($_GET) && !empty($_GET)) {
						$response = $this->poi->search($_GET);
					} else {
						$response = $this->poi->get();
					}
				}
			}
		} elseif ($request[0] == USERS) {
			if(isset($request[1]) && is_numeric($request[1])) {
				$response = $this->poi->get(null,$request[1]);
			} else {
				if (!isset($request[1])) {
					$response = -1;
				}
			}
		}
		if ($response != -1) {
			return 200;
		}
		return 404;
	}

	function rest_delete($request) {
		if($request[0] == POINTS && isset($request[1]) && is_numeric($request[1])) {
			echo "delete point";
			//return 200;
		}
		return 404;
	}
?>