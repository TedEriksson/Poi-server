<?php
	error_reporting(E_ALL);
	include 'Poi/Poi.php';
	include 'connectinfo.php';

	header('Content-Type: application/json');

	/**
	*	
	*/
	class ApiParser {

		private $segs;
		private $json = null;
		private $id = null;
		private $where = null;
		private $request;
		
		function __construct($url, $json = null) {
			$uri_parts = explode('?', $url, 2);
			$url = $uri_parts[0];
			$segs = explode("/", $url);
			$segs = array_values(array_diff($segs, array("","api","poi")));

			$this->segs = $segs;
			//if($this->isJson($json))
				$this->json = $json;
			$this->where = $_GET;
			$this->request = new NullRequest();

			if (isset($this->segs[0]) && ($this->segs[0] == "points" || $this->segs[0] == "users") && !isset($this->segs[2])) {
				if ($this->json == null) {
					if (isset($this->segs[1]) || $this->segs[0] == "users") {
						if($this->segs[0] == "users") {
							$this->request = new Get(null, $this->segs[1]);
						} else {
							$this->request = new Get($this->segs[1]);
						}
					} else {
						$this->request = new Search($this->where);
					}
				} else {
					$this->request = new Insert($this->json);
					if (isset($this->segs[1]) && is_array($this->segs[1])) {
						$this->request = new Update();
					} else if (isset($this->segs[1])) {
						$this->request = new Insert($this->json);
					}
				}
			}
		}

		function isJson($string) {
			json_decode($string);
			return (json_last_error() == JSON_ERROR_NONE);
		}

		function getrequest() {
			$this->request->getrequest();
		}

		function performRequest() {
			return $this->request->performRequest();
		}
	}

	interface Request {
		public function getRequest();
		public function performRequest();
	} 

	class Get implements Request {
		private $poi;
		private $id = null;
		private $user = null;

		function __construct($id = null, $user = null) {
			$this->poi = new Poi(DATABASE_HOSTNAME,DATABASE_DATABASE,DATABASE_USER,DATABASE_PASSWORD);
			$this->id = $id;
			$this->user = $user;
		}

		public function performRequest() {
			if($user != null) {
				echo "user!";
				return $this->poi->get(null, $this->user);
			}
			return $this->poi->get($this->id);
		}

		public function getRequest() {
			echo "GET";
		}
	}

	class Update implements Request {
		public function getRequest() {
			echo "UPDATE";
		}

		public function performRequest() {

		}
	}

	class Insert implements Request {

		private $json = null;

		function __construct($json) {
			$this->poi = new Poi(DATABASE_HOSTNAME,DATABASE_DATABASE,DATABASE_USER,DATABASE_PASSWORD);
			$this->json = $json;
		}

		public function getRequest() {
			echo "INSERT";
		}

		public function performRequest() {
			return $this->poi->insert($this->json);
		}
	}

	class Search implements Request {

		private $poi;
		private $where = null;

		function __construct($where) {
			$this->poi = new Poi(DATABASE_HOSTNAME,DATABASE_DATABASE,DATABASE_USER,DATABASE_PASSWORD);
			$this->where = $where;
		}

		public function getRequest() {
			echo "SEARCH";
		}

		public function performRequest() {
			return $this->poi->search($this->where);
		}
	}

	class NullRequest implements Request {
		public function getRequest() {
			echo "NULL REQUEST";
		}

		public function performRequest() {

		}
	}

	if($post = file_get_contents("php://input")) {
		$api = new ApiParser($_SERVER['REQUEST_URI'], $post);
	} else {
		$api = new ApiParser($_SERVER['REQUEST_URI']);
	}

	//echo $api->getRequest();
	echo $api->performRequest();
?>