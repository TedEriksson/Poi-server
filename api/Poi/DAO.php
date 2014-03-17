<?php

/**
* Base Abstract DAO
*/
abstract class BaseDAO {

	private $dbConnection;

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
	private $authenticatedAs = null;

	function __construct($auth = true, $userID = "", $authKey = "") {
		parent::__construct();
		if($auth) {
			$this->authenticatedAs = $this>authenticateUser($accessToken, $owner_id);
			if(is_null($this->authenticatedAs)) {
				Unauthorized::printError();
				exit();
			}
		}
	}

	public function insert($keyedInsertObject) {}
	
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

	public function delete($id){}

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
		return (is_null($this->authenticatedAs)) ? false : true;
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

	public function update($keyedUpdateObject) {
		return parent::update($keyedUpdateObject);
	}
}

?>