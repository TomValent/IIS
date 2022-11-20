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
		$stmt= $pdo->prepare("SELECT MemberID FROM Member WHERE Login = :login;");
		$stmt->execute(['login' => $login]);
		$user = $stmt->fetch();
		if (!$user) {
			return NULL;
		}
		return $user['MemberID'];
	}

	public function getTournamentByID($id)
	{
		$pdo = createDB();
		$stmt = $pdo->prepare("SELECT * FROM Tournament WHERE TournamentID=:id;");
		$stmt->execute(["id" => $id]);
		$tournament = $stmt->fetch();
		if (!$tournament) {
			return NULL;
		}
		return $tournament;
	}

	private function getParticipantState($sql, $data): ?string
	{
		$pdo = createDB();
		$stmt= $pdo->prepare($sql);
		$stmt->execute($data);
		$data = $stmt->fetch();
		if (!$data) {
			return 'none';
		}
		return $data['AcceptanceState'];
	}

	public function getMemberParticipantState($tournament_id, $user_id): ?string
	{
		$sql = 'SELECT AcceptanceState FROM TournamentParticipant WHERE TournamentID = ? AND MemberID = ?;';
		return $this->getParticipantState($sql, [$tournament_id, $user_id]);
	}

	public function getTeamParticipantState($tournament_id, $team_id): ?string
	{
		$sql = 'SELECT AcceptanceState FROM TournamentParticipant WHERE TournamentID = ? AND TeamID = ?;';
		return $this->getParticipantState($sql, [$tournament_id, $team_id]);
	}

	public function getTeamParticipants($id, $leader_id = NULL) {
		$sql = "SELECT * FROM TournamentParticipant TP "
			."INNER JOIN Team T ON TP.TeamID = T.TeamID "
			."WHERE TP.TournamentID = :id";
		$data = ['id' => $id];
		if ($leader_id !== NULL) {
			$sql .= " AND T.LeaderID = :l_id";
			$data['l_id'] = $leader_id;
		}

		$pdo = createDB();
		$stmt = $pdo->prepare($sql);
		$stmt->execute($data);

		return $stmt->fetchAll();
	}

	public function getMemberParticipants($id) {
		$sql = "SELECT * FROM TournamentParticipant TP "
			."INNER JOIN Member M ON TP.MemberID = M.MemberID";

		$pdo = createDB();
		$stmt = $pdo->prepare($sql);
		$stmt->execute();

		return $stmt->fetchAll();
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
