<?php
include 'DAO.php';

class Poi {
	public function getPoint($pointID) {
		$points = new pointsDAO(false);
		return $this->pointsArrayToJSON($points->getByPointID($pointID));
	}

	private function pointsArrayToJSON($arrayOfPoints) {
		return json_encode(array("points" => $arrayOfPoints));
	}
}

?>