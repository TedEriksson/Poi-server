<?php
include 'DAO.php';

class Poi {
	public function getPoint($pointID) {
		$points = new pointsDAO(false);
		$pointsArray = $points->getByPointID($pointID);
		var_dump($pointsArray);
		if(empty($pointsArray))
			return PointNotFound::printError();
		else
			return $this->pointsArrayToJSON();
	}

	public function getPoints() {
		return Unauthorized::printError();
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

?>