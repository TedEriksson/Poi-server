<?php
include 'DAO.php';

class Poi {
	public function getPoint($pointID) {
		$points = new pointsDAO(false);
		return $this->pointsArrayToJSON($points->getByPointID($pointID));
	}

	public function getPoints() {
		return Unauthorized::printError();
	}

	private function pointsArrayToJSON($arrayOfPoints) {
		return json_encode(array("points" => $arrayOfPoints));
	}
}

abstract class JsonErrorMessage {

	public static function printError() {
		$error = array();
		$error['code'] = $this->_code;
		$error['message'] = $this->_message;
		return json_encode(array('error' => $error));
	}
}

class Unauthorized extends JsonErrorMessage {
	protected $_code = 400;
	protected $_message = "Unauthorized. You are not allowed to view this";
}

?>