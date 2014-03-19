<?php

/**
* Base Abstract DAO
*/
abstract class BaseDAO {

	protected $dbConnection;

	function __construct() {
		$this->connectToDB(DATABASE_HOSTNAME,DATABASE_DATABASE,DATABASE_USER,DATABASE_PASSWORD);
	}
	
	private function connectToDB($hostname,$database,$username,$password) {
		$this->dbConnection = new PDO("mysql:host=$hostname;dbname=$database",$username,$password);
		$this->dbConnection->setAttribute(PDO::ATTR_EMULATE_PREPARES,TRUE);
	}

	public function fetch($value,$key = null) {
		if(is_null($key)) {
			$key = $this->_primaryKey;
		}

		$statement = $this->dbConnection->prepare("SELECT * FROM {$this->_tableName} WHERE {$key}=:{$key}");
		$statement->execute(array(":{$key}" => $value));
		return $statement->fetchAll(PDO::FETCH_ASSOC);
	}
}

/**
* Extention of BaseDAO that requires the user to be authenticated for safety critical operations.
*/
abstract class AuthDAO extends BaseDAO {
	//The google user ID. If null, the user is not authenticated.
	protected $_authenticatedAs = null;

	function __construct($auth = true, $accessToken = "", $owner_id = "") {
		parent::__construct();
		if($auth) {
			$this->_authenticatedAs = $this->authenticateUser($accessToken, $owner_id);
			if(is_null($this->_authenticatedAs)) {
				Unauthorized::printError();
				exit();
			}
		}
	}

	public function insert($keyedInsertObject) {
		$pdoValues = array();
		$keys = array();
		$keyvalues = array();
		foreach ($keyedInsertObject as $key => $value) {
			$pdoValues[":{$key}"] = $value;
			$keys[] = $key;
			$keyvalues[] = ":{$key}";
		}
		$keys = implode(",", $keys);
		$keyvalues = implode(",", $keyvalues);
		$sql = "INSERT INTO {$this->_tableName} ({$keys}) VALUES ({$keyvalues})";
		$statement = $this->dbConnection->prepare($sql);
		$statement->execute($pdoValues);
		return $this->dbConnection->lastInsertId($this->_primaryKey);
	}
	
	/**
	* updates table in database.
	* returns number of rows affected.
	*/
	public function update($keyedUpdateObject) {
		$sql = "UPDATE {$this->_tableName} set ";

		$updates = array();
		$pdoValues = array();
		foreach ($keyedUpdateObject as $key => $value) {
			$updates[] = "{$key}=:{$key}";
			$pdoValues[":{$key}"] = $value;
		}

		$sql .= implode(",", $updates);
		$sql .= " WHERE {$this->_primaryKey}=:{$this->_primaryKey}";

		$statement = $this->dbConnection->prepare($sql);
		$statement->execute($pdoValues);
		return $statement->rowCount();
	}

	public function delete($value, $key = null){
		if(is_null($key)) {
			$key = $this->_primaryKey;
		}
		$sql = "DELETE FROM {$this->_tableName} WHERE {$key}=:{$key}";
		$statement = $this->dbConnection->prepare($sql);
		$statement->execute(array(":{$key}" => $value));
		return $statement->rowCount();
	}

	private function authenticateUser($accessToken, $owner_id) {
		if(is_null($accessToken) || is_null($owner_id))
			return null;
		$url = "https://www.googleapis.com/plus/v1/people/me?access_token=".$accessToken;
		$ch = curl_init();
		curl_setopt($ch,CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$content = curl_exec($ch);
		$array = json_decode($content, true);
		if (isset($array["id"]) && $array["id"] == $owner_id)
			return $array["id"];
		return null;
	}

	public function isAuthenticated() {
		return (is_null($this->_authenticatedAs)) ? false : true;
	}
}

/**
* DAO for the Points table.
*/
class pointsDAO extends AuthDAO {
	protected $_tableName = "points";
	protected $_primaryKey = "point_id";

	public function getByPointID($pointID) {
		return $this->fetch($pointID);
	}

	public function getWithinRadius($lat, $lng, $rad) {
		$sql = "SELECT * FROM {$this->_tableName}  HAVING ( 6371 * acos( cos( radians(:clat) ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians(:clng) ) + sin( radians(:clat) ) * sin( radians( latitude ) ) ) ) < :rad";
		$statement = $this->dbConnection->prepare($sql);
		$statement->execute(array(":clat" => $lat, ":clng" => $lng, ":rad" => $rad));
		return $statement->fetchAll(PDO::FETCH_ASSOC);
	}

	public function update($keyedUpdateObject) {
		if($this->isAuthenticated() && $this->_authenticatedAs == $this->fetch($keyedUpdateObject['point_id'])[0]['owner_id'])
			return parent::update($keyedUpdateObject);
		else
			PointNotFound::printError();
	}

	public function insert($keyedInsertObject) {
		if($this->isAuthenticated() && $this->_authenticatedAs == $keyedInsertObject['owner_id'])
			return parent::insert($keyedInsertObject);
		else
			PointNotFound::printError();
	}

	public function delete($value, $key = null) {
		$point = $this->fetch($value)[0];
		if($this->isAuthenticated() && $this->_authenticatedAs == $point['owner_id']) {
			return parent::delete($value, $key);
		} else
			PointNotFound::printError();
	}
}

/**
* DAO for the Parts table.
*/
class partsDAO extends AuthDAO {
	protected $_tableName = "parts";
	protected $_primaryKey = "part_id";

	public function getByPartID($partID) {
		return $this->fetch($partID);
	}

	public function getPartOwner($partID) {
		$statement = $this->dbConnection->prepare("SELECT owner_id FROM {$this->_tableName} INNER JOIN points ON {$this->_tableName}.point_id=points.point_id WHERE {$this->_primaryKey}=:{$this->_primaryKey}");
		$statement->execute(array(":{$this->_primaryKey}" => $partID));
		return $statement->fetchAll(PDO::FETCH_ASSOC)[0]['owner_id'];
	}

	public function update($keyedUpdateObject) {
		if($this->isAuthenticated() && $this->_authenticatedAs == $this->getPartOwner($keyedUpdateObject['part_id']))
			return parent::update($keyedUpdateObject);
		else
			PartNotFound::printError();
	}

	public function insert($keyedInsertObject) {
		if(isset($keyedInsertObject['part_id'])) unset($keyedInsertObject['part_id']);
		$points = new pointsDAO(false);

		if($this->isAuthenticated() && $this->_authenticatedAs == $points->getByPointID($keyedInsertObject['point_id'])[0]['owner_id'])
			return parent::insert($keyedInsertObject);
		else
			PartNotFound::printError();
	}

	public function delete($value, $key = null) {
		if($this->isAuthenticated() && $this->_authenticatedAs == $this->getPartOwner($value))
			return parent::delete($value, $key);
		else
			PartNotFound::printError();
	}
}

?>