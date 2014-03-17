<?php
include 'DAO.php';

class Poi {
	public function getPoint($pointID) {
		$points = new pointsDAO(false);
		$pointsArray = $points->getByPointID($pointID);

		if(empty($pointsArray))
			return PointNotFound::printError();
		else
			return $this->pointsArrayToJSON($pointsArray);
	}

	public function getPoints() {
		return Unauthorized::printError();
	}

	public function updatePoint($updateJson) {
		$updateArray = json_decode($updateJson, true);
		if(isset($updateArray['access_token'])) {
			$points = new pointsDAO(false,$updateArray['owner_id'],$updateArray['access_token']);
			unset($updateArray['access_token']);
			return $points->update($updateArray);
		}
		return NoAccessKey::printError();
		exit();
	}

	private function pointsArrayToJSON($arrayOfPoints) {
		return json_encode(array("points" => $arrayOfPoints), JSON_PRETTY_PRINT);
	}
}

abstract class JsonErrorMessage {

	public static function printError() {
		$error = array();
		$error['code'] = static::$_code;
		$error['message'] = static::$_message;
		http_response_code(static::$_code);
		echo json_encode(array('error' => $error), JSON_PRETTY_PRINT);
		exit();
	}
}

class Unauthorized extends JsonErrorMessage {
	protected static $_code = 403;
	protected static $_message = "Unauthorized. You are not allowed to view this";
}

class PointNotFound extends JsonErrorMessage {
	protected static $_code = 404;
	protected static $_message = "Point not found. There is no point with this ID.";
}

class URIRequestError extends JsonErrorMessage {
	protected static $_code = 404;
	protected static $_message = "There was an error in your URI. The page you are looking for does not exist";
}

class NoAccessKey extends JsonErrorMessage {
	protected static $_code = 400;
	protected static $_message = "There was no access key provided with your request. This type of request requires an access key.";
}

class BadRequest extends JsonErrorMessage {
	protected static $_code = 400;
	protected static $_message = "Your request is malformed.";
}
?>