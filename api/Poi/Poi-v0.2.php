<?php
include 'DAO.php';

class Poi {
	public function getPoint($pointID) {
		$points = new pointsDAO(false);
		$pointArray = array();
		$pointArray[] = $points->getByPointID($pointID); 
		return $this->pointsArrayToJSON($pointArray);
	}

	private function pointsArrayToJSON($arrayOfPoints) {
		return json_encode(array("points" => $arrayOfPoints));
	}
}

?>