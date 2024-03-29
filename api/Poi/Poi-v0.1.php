<?php
class Poi {

	private $pdo;

	const POINTS_TABLE = 'points';

	function __construct($hostname,$database,$username,$password) {
		// $this->pdo = new PDO("mysql:host=$hostname;dbname=$database",$username,$password);
		// $this->pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES,TRUE);
	}

	public function get($id = null, $user = null) {
		if($id == null) {
			$get = "SELECT * FROM points";
			if($user != null) {
				$get = "SELECT * FROM points WHERE owner_id = :user";
				$statement = $this->pdo->prepare($get);
				$statement->execute(array(':user' => $user));
			} else {
				$statement = $this->pdo->prepare($get);
				$statement->execute();
			}
		} else {
			$statement = $this->pdo->prepare("SELECT * FROM points WHERE point_id = :id");
			$statement->execute(array(':id' => $id));
			$statement2 = $this->pdo->prepare("SELECT * FROM parts WHERE point_id = :id");
			$statement2->execute(array(':id' => $id));
		}
		$results = $statement->fetchAll(PDO::FETCH_ASSOC);
		$results = array('points' => $results);
		if($id != null) $results['points'][0]['parts'] =  $statement2->fetchAll(PDO::FETCH_ASSOC);
		return json_encode($results);
	}

	public function update($vals, $id = null) {
		$vals = json_decode($vals, true);

		//Is Authenticated user
		if(!isset($vals['point_id']) || !isset($vals["access_token"]) || !isset($vals["owner_id"]) || !$this->validateUser($vals["access_token"],$vals["owner_id"])) return -1;

		unset($vals["access_token"]);

		//Extract parts
		$parts = null;
		if(isset($vals["parts"])) {
			$parts = $vals["parts"];
			unset($vals["parts"]);
		}

		$updateString = "UPDATE points SET ";
		$isFirst = true;
		foreach ($vals as $key => $value) {
			$pdoVals[":$key"] = $value;
			if ($key != 'point_id' && $key != 'owner_id') {
				if($isFirst) {
					$updateString .= "$key=:$key";
					$isFirst = false;
				} else {
					$updateString .= ", $key=:$key";
				}
			}
		}
		$updateString .= " WHERE point_id=:point_id AND owner_id=:owner_id";
		$statement = $this->pdo->prepare($updateString);
		if($statement->execute($pdoVals)) {
			if($parts != null) {
				$error = false;
				foreach ($parts as $part) {
					$partString = "";
					$pdoVals = array();
					if(isset($part["part_id"]) && ($part["part_id"] != "-1" || $part["part_id"] != -1)) {
						$pdoVals[":point_id"] = $vals["point_id"];
						$pdoVals[":part_id"] = $part["part_id"];
						$partString = "UPDATE parts SET ";
						$isFirst = true;
						foreach ($part as $key => $value) {
							if ($key != "part_id" && $key != "point_id") {
								$pdoVals[":$key"] = $value;
								if($isFirst) {
									$partString .= "$key=:$key";
									$isFirst = false;
								} else {
									$partString .= ", $key=:$key";
								}
							}
						}
						$partString .= " WHERE part_id=:part_id AND point_id=:point_id";
					} else {
						$part["point_id"] = $vals["point_id"];
						if(isset($part["part_id"])) unset($part["part_id"]);
						$partString = "INSERT INTO parts ";
						$first = true;
						$keys = "";
						$values = "";
						foreach ($part as $key => $value) {
							if($first) {
								$keys .= "$key";
								$values .= ":$key";
								$first = false;
							} else {
								$keys .= ", $key";
								$values .= ", :$key";
							}
							$pdoVals[":$key"] = urldecode($value);
						}
						$partString .= "($keys) VALUES ($values)";
					}
					$statement = $this->pdo->prepare($partString);
					if(!$statement->execute($pdoVals)) {
						$error = true;
					}
				}
			}
			if ($error) return -1;
			return $this->get($vals["point_id"]);
		}
	}

	public function insert($vals) {
		$vals = json_decode($vals, true);

		//Is Authenticated user
		if(!isset($vals["access_token"]) || !isset($vals["owner_id"]) || !$this->validateUser($vals["access_token"],$vals["owner_id"])) return -1;

		unset($vals["access_token"]);

		if(isset($vals["point_id"])) unset($vals["point_id"]);
		$parts = null;
		if(isset($vals["parts"])) {
			$parts = $vals["parts"];
			unset($vals["parts"]);
		}
		$insertString = "INSERT INTO points ";
		$first = true;
		$keys = "";
		$values = "";
		foreach ($vals as $key => $value) {
			if($first) {
				$keys .= "$key";
				$values .= ":$key";
				$first = false;
			} else {
				$keys .= ", $key";
				$values .= ", :$key";
			}
			$pdoVals[":$key"] = urldecode($value);
		}
		$insertString .= "($keys) VALUES ($values)";
		$statement = $this->pdo->prepare($insertString);

		//die($insertString . "     " . var_dump($pdoVals));
		if($statement->execute($pdoVals)) {
			$error = false;
			$insertId = $this->pdo->lastInsertId('point_id');
			if($parts != null) {
				foreach ($parts as $part) {
					$pdoVals = array();
					if(isset($part["part_id"])) unset($part["part_id"]);
					$part["point_id"] = $insertId;
					$insertString = "INSERT INTO parts ";
					$first = true;
					$keys = "";
					$values = "";
					foreach ($part as $key => $value) {
						if($first) {
							$keys .= "$key";
							$values .= ":$key";
							$first = false;
						} else {
							$keys .= ", $key";
							$values .= ", :$key";
						}
						$pdoVals[":$key"] = urldecode($value);
					}
					$insertString .= "($keys) VALUES ($values)";
					$statement = $this->pdo->prepare($insertString);
					if(!$statement->execute($pdoVals)) {
						$error = true;
					}
				}
			}
			if ($error) return -1;
			return $insertId; 
		} else {
			return -1;
		}
	}

	public function search($where, $token = null) {
		$searchString = "SELECT * FROM points";
		$pdoVals = null;
		if (count($where) > 0) {
			$first = true;

			if (!empty($where['clat']) && !empty($where['clng']) && !empty($where['rad'])) {
				$having = " HAVING ( 6371 * acos( cos( radians(:clat) ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians(:clng) ) + sin( radians(:clat) ) * sin( radians( latitude ) ) ) ) < :rad";
				$pdoVals[":clat"] = $where['clat'];
				$pdoVals[":clng"] = $where['clng'];
				$pdoVals[":rad"] = $where['rad'];

				unset($where['clat']);
				unset($where['clng']);
				unset($where['rad']);
			}

			foreach ($where as $key => $value) {
				if (!$first) {
					$searchString .= " AND";
				} else {
					$searchString .= " WHERE";
					$first = false;
				}
				if ($key == "minlat") {
					$searchString .= " latitude >= :minlatitude";
					$pdoVals[":minlatitude"] = $value;
				} else if ($key == "maxlat") {
					$searchString .= " latitude <= :maxlatitude";
					$pdoVals[":maxlatitude"] = $value;
				} else if ($key == "minlng") {
					$searchString .= " longitude >= :maxlongitude";
					$pdoVals[":maxlongitude"] = $value;
				} else if ($key == "maxlng") {
					$searchString .= " longitude <= :minlongitude";
					$pdoVals[":minlongitude"] = $value;
				}
			}

			$searchString .= $having;

		}
		die($searchString);
		$statement = $this->pdo->prepare($searchString);

		if($statement->execute($pdoVals)) {
			$results = $statement->fetchAll(PDO::FETCH_ASSOC);
			$results = array('points' => $results);
			return json_encode($results);
		} else {
			return json_encode(array('points' => array()));
		}
	} 

	private function validateUser($accessToken, $owner_id) {
		$url = "https://www.googleapis.com/plus/v1/people/me?access_token=".$accessToken;
		$ch = curl_init();
		curl_setopt($ch,CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$content = curl_exec($ch);
		$array = json_decode($content, true);
		return ($array["id"] == $owner_id);
	}
}
?>