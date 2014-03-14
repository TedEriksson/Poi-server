<?php
include 'DAO.php';

class Poi {
	public function getPoint($pointID) {
		$points = new pointsDAO(false);
		return json_encode($points->getByPointID($pointID));
	}
}

?>