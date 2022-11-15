<?php

require_once "dotenv.php";

class Database implements \SplSubject {
	private static $instance = null;

	private $observers = array();
	private $pdo = null;

	private function __construct()
	{
		loadDotenv();
		$this->pdo = new PDO($_ENV['MYSQL_DSN'], $_ENV['MYSQL_USER'], $_ENV['MYSQL_PASS']);
	}

	public static function getInstance(): Database
	{
		if (self::$instance == null) {
			self::$instance = new Database();
		}

		return self::$instance;
	}

	public function getPDO(): PDO
	{
		return $this->pdo;
	}

	public function getUserID($login)
	{
		$pdo = createDB();
		$stmt= $pdo->prepare("SELECT MemberID FROM Member WHERE Login = :login");
		$stmt->execute(['login' => $login]);
		$user = $stmt->fetch();
		if (!$user) {
			return NULL;
		}
		return $user['MemberID'];
	}

	//add observer
	public function attach(\SplObserver $observer) : void
	{
		$this->observers[] = $observer;
	}

	//remove observer
	public function detach(\SplObserver $observer) : void
	{
		$key = array_search($observer,$this->observers, true);
		if($key) {
			unset($this->observers[$key]);
		}
	}

	public function notify() : void
	{
		foreach ($this->observers as $value) {
			$value->update($this);
		}
	}
}

class DbObserver implements SplObserver {

	private $callback;

	public function __construct($callback)
	{
		$this->callback = $callback;
		$db = Database::getInstance();
		$db->attach($this);
	}

	public function update(\SplSubject $subject): void
	{
		$this->callback();
	}

	function __destruct() {
		$db = Database::getInstance();
		$db->detach($this);
	}
}

function createDB(): PDO
{
	return Database::getInstance()->getPDO();
}
