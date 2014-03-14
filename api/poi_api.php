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

	switch ($method) {
		case 'PUT':
			rest_put($request);
			break;
		case 'POST':
			rest_post($request);  
			break;
		case 'GET':
			rest_get($request);  
			break;
		case 'DELETE':
			rest_delete($request);  
			break;
		default:
			rest_error($request);  
			break;
	}

	function rest_put($request) {
		global $poi;
		if($request[0] == POINTS && isset($request[1]) && is_numeric($request[1])) {
			$response = $poi->updatePoint(file_get_contents("php://input"));
			if ($response > 0) {
				echo $response;
				return;
			}
			BadRequest::printError();
			return;
		}
		URIRequestError::printError();
		exit();
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
		URIRequestError::printError();
		exit();
	}

	function rest_get($request) {
		global $poi;
		$response = null;
		if($request[0] == POINTS) {
			if(isset($request[1]) && is_numeric($request[1])) {
				if(!isset($request[2]))
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
		if(is_null($response)) {
			URIRequestError::printError();
			exit();
		}
		echo $response;
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