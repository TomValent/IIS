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

	public function getUserID($login): mixed
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

	public function getTournamentByID($id): mixed
	{
		$pdo = createDB();
		$stmt = $pdo->prepare("SELECT * FROM Tournament WHERE TournamentID=:id;");
		$stmt->execute(["id" => $id]);
		return $stmt->fetch();
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

	public function getTeamParticipants($tournament_id, $acceptance_state = NULL, $leader_id = NULL): array|bool
	{
		$data = ['id' => $tournament_id];
		$sql = "SELECT * FROM TournamentParticipant TP "
			."INNER JOIN Team T ON TP.TeamID = T.TeamID "
			."WHERE TP.TournamentID = :id";
		if ($acceptance_state !== NULL) {
			$sql .= " AND TP.AcceptanceState = :state";
			$data['state'] = $acceptance_state;
		}
		if ($leader_id !== NULL) {
			$sql .= " AND T.LeaderID = :l_id";
			$data['l_id'] = $leader_id;
		}

		$pdo = createDB();
		$stmt = $pdo->prepare($sql);
		$stmt->execute($data);

		return $stmt->fetchAll();
	}

	public function getTeamParticipantsWithLeader($tournament_id, $acceptance_state = NULL, $leader_id = NULL): array|bool
	{
		return $this->getTeamParticipants($tournament_id, $acceptance_state, $leader_id);
	}

	public function getMemberParticipants($tournament_id, $acceptance_state = NULL): array|bool
	{
		$data = ['id' => $tournament_id];
		$sql = "SELECT * FROM TournamentParticipant TP "
			."INNER JOIN Member M ON TP.MemberID = M.MemberID "
			."WHERE TP.TournamentID = :id";
		if ($acceptance_state !== NULL) {
			$sql .= " AND TP.AcceptanceState = :state";
			$data['state'] = $acceptance_state;
		}
		$pdo = createDB();
		$stmt = $pdo->prepare($sql);
		$stmt->execute($data);

		return $stmt->fetchAll();
	}

	public function getMemberMatches($tournament_id, $round = NULL): array|bool
	{
		$data = ['id' => $tournament_id];
		$sql = "SELECT * FROM Matches M "
			."LEFT OUTER JOIN Member AS M1 ON M.Member1ID = M1.MemberID "
			."LEFT OUTER JOIN Member AS M2 ON M.Member2ID = M2.MemberID "
			." WHERE M.TournamentID = :id ";
		if ($round !== NULL) {
			$sql .= "AND M.Round = :round ";
			$data['round'] = $round;
		}

		$pdo = createDB();
		$stmt = $pdo->prepare($sql);
		$stmt->execute($data);

		return $stmt->fetchAll(PDO::FETCH_NAMED);
	}

	public function getTeamMatches($tournament_id, $round = NULL): array|bool
	{
		$data = ['id' => $tournament_id];
		$sql = "SELECT * FROM Matches M "
			."LEFT OUTER JOIN Team AS T1 ON M.Team1ID = T1.TeamID "
			."LEFT OUTER JOIN Team AS T2 ON M.Team2ID = T2.TeamID "
			." WHERE M.TournamentID = :id ";
		if ($round !== NULL) {
			$sql .= "AND M.Round = :round ";
			$data['round'] = $round;
		}

		$pdo = createDB();
		$stmt = $pdo->prepare($sql);
		$stmt->execute($data);

		return $stmt->fetchAll(PDO::FETCH_NAMED);
	}


	public function getMemberMatch($tournament_id, $id): mixed
	{
		$data = ['id' => $tournament_id];
		$sql = "SELECT * FROM Matches M "
			."LEFT OUTER JOIN Member AS M1 ON M.Member1ID = M1.MemberID "
			."LEFT OUTER JOIN Member AS M2 ON M.Member2ID = M2.MemberID "
			." WHERE (MatchID,TournamentID) = (:id, :t_id)";

		$pdo = createDB();
		$stmt = $pdo->prepare($sql);
		$stmt->execute(['id' => $id, 't_id' => $tournament_id]);

		return $stmt->fetch(PDO::FETCH_NAMED);
	}

	public function getTeamMatch($tournament_id, $id): mixed
	{
		$data = ['id' => $tournament_id];
		$sql = "SELECT * FROM Matches M "
			."LEFT OUTER JOIN Team AS T1 ON M.Team1ID = T1.TeamID "
			."LEFT OUTER JOIN Team AS T2 ON M.Team2ID = T2.TeamID "
			." WHERE (MatchID,TournamentID) = (:id, :t_id)";

		$pdo = createDB();
		$stmt = $pdo->prepare($sql);
		$stmt->execute(['id' => $id, 't_id' => $tournament_id]);

		return $stmt->fetch(PDO::FETCH_NAMED);
	}

	public function getMatchByID($id, $tournament_id): mixed
	{
		$sql = "SELECT * FROM Matches WHERE (MatchID,TournamentID) = (:id, :t_id)";

		$pdo = createDB();
		$stmt = $pdo->prepare($sql);
		$stmt->execute(['id' => $id, 't_id' => $tournament_id]);

		return $stmt->fetch(PDO::FETCH_ASSOC);
	}

	public function userHasTeams($id): bool
	{
		$sql = "SELECT COUNT(TeamID) FROM Team WHERE LeaderID=:id";

		$pdo = createDB();
		$stmt = $pdo->prepare($sql);
		$stmt->execute(['id' => $id]);

		$result = $stmt->fetch();
		if (!$result) {
			return false;
		}

		return $result['COUNT(TeamID)'] > 0;
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

function pdo_debug($stmt) {
	ob_start();
	$stmt->debugDumpParams();
	$r = ob_get_contents();
	ob_end_clean();
	error_log($r);
}
