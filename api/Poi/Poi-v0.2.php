<?php
include 'DAO.php';

class Poi {
	public function getPoint($pointID) {
		$points = new pointsDAO(false);
		$parts = new partsDAO(false);
		$pointsArray = $points->getByPointID($pointID);

		if(empty($pointsArray))
			return PointNotFound::printError();
		else
			$pointsArray[0]["parts"] = $parts->fetch($pointID, 'point_id');
			return $this->pointsArrayToJSON($pointsArray);
	}

	public function getPoints() {
		return Unauthorized::printError();
	}

	public function getUsersPoints($ownerID) {
		$points = new pointsDAO(false);
		$pointsArray = $points->fetch($ownerID,'owner_id');
		if(empty($pointsArray))
			return UserNotFound::printError();
		else
			return $this->pointsArrayToJSON($pointsArray);
	}

	public function updatePoint($updateJson) {
		$updateArray = json_decode($updateJson, true);
		$partsArray = array();
		$stats = array('type' => "Update", 'points' => 0, 'parts' => 0, 'new_parts' => 0);
		if(isset($updateArray['parts'])) {
			$partsArray = $updateArray['parts'];
			unset($updateArray['parts']);
		}
		if(isset($updateArray['access_token'])) {
			$points = new pointsDAO(true,$updateArray['access_token'],$updateArray['owner_id']);
			$parts = new partsDAO(true,$updateArray['access_token'],$updateArray['owner_id']);
			unset($updateArray['access_token']);

			foreach ($partsArray as $part) {
				if($part['part_id'] == "-1" || $part['part_id'] == -1) {
					//Insert new point
					$part['point_id'] = $updateArray['point_id'];
					$stats['new_parts'][] = $parts->insert($part);
				} else {
					//Update existing point
					$stats['parts'] += $parts->update($part);
				}
			}
			$stats['points'] = $points->update($updateArray);
			return json_encode($stats);
		}
		return NoAccessKey::printError();
		exit();
	}

	public function insertPoint($insertJson) {
		$insertArray = json_decode($insertJson, true);
		$partsArray = array();
		$stats = array('type' => "Insert", 'parts' => array());
		if(isset($insertArray['parts'])) {
			$partsArray = $insertArray['parts'];
			unset($insertArray['parts']);
		}
		if(isset($insertArray['access_token'])) {
			$points = new pointsDAO(true,$insertArray['access_token'],$insertArray['owner_id']);
			$parts = new partsDAO(true,$insertArray['access_token'],$insertArray['owner_id']);
			unset($insertArray['access_token']);

			$stats['point_id'] = $points->insert($insertArray);
			
			foreach ($partsArray as $part) {
				$part['point_id'] = $stats['point_id'];
				$stats['new_parts'][] = $parts->insert($part);
			}
			
			return json_encode($stats);
		}
		return NoAccessKey::printError();
		exit();
	}

	public function deletePoint($pointID, $credentials) {
		$credentialsArray = json_decode($credentials, true);
		if(isset($credentialsArray['access_token'])) {
			$points = new pointsDAO(true,$credentialsArray['access_token'],$credentialsArray['owner_id']);
			$points = $points->fetch($pointID);
			if(empty($points)) {
				PointNotFound::printError();
			}
			$parts = $points[0]['parts'];
			foreach ($parts as $key) {
				$this->deletePart($key['part_id'], $credentials);
			}
			$points->delete($credentialsArray['point_id']);
		}
		return NoAccessKey::printError();
	}

	public function deletePart($partID, $credentials) {
		$credentialsArray = json_decode($credentials, true);
		if(isset($credentialsArray['access_token'])) {
			$parts = new partsDAO(true,$credentialsArray['access_token'],$credentialsArray['owner_id']);
			$parts->delete($partID);
		}
		return NoAccessKey::printError();	
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

class AuthError extends JsonErrorMessage {
	protected static $_code = 403;
	protected static $_message = "The credentials you provided were incorrect.";
}

class UserNotFound extends JsonErrorMessage {
	protected static $_code = 404;
	protected static $_message = "User not found. There is no user with this ID.";
}

class PointNotFound extends JsonErrorMessage {
	protected static $_code = 404;
	protected static $_message = "Point not found. There is no point with this ID.";
}

class PartNotFound extends JsonErrorMessage {
	protected static $_code = 404;
	protected static $_message = "Part not found. There is no part with this ID.";
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

class NothingChanged extends JsonErrorMessage {
	protected static $_code = 200;
	protected static $_message = "Your request was correct, but nothing was changed.";
}
?>