<?php

class TournamentController extends BaseController {

	/**
	 * @throws MethodException
	 */
    public function startAction(): void
	{
		$this->checkRequestMethod('POST');
		$user_id = $this->getLoggedUserID();
		$t_id = $this->get($_POST, "id");
		$pairs = $this->get($_POST, "pairs");
		if (!is_array($pairs) || count($pairs) == 0) {
			throw new MethodException("API format error");
		}

		$tournament = Database::getInstance()->getTournamentByID($t_id);
		if (!$tournament) {
			throw new MethodException("Tournament does not exist");
		}
		if ($tournament['CreatorID'] != $user_id) {
			throw new MethodException("Tournament is not owned by user");
		}
		if ($tournament['ProgressState'] != 'unstarted') {
			throw new MethodException("Tournament already started");
		}

		if ($tournament['type'] == 'team') {
			$participants = Database::getInstance()->getTeamParticipants($t_id);
		}
		else {
			$participants = Database::getInstance()->getMemberParticipants($t_id);
		}

		$accepted = [];
		foreach ($participants as $p) {
			if ($p['AcceptanceState'] == 'approved') {
				if ($tournament['type'] == 'team') {
					$id = $p['TeamID'];
				}
				else {
					$id = $p['MemberID'];
				}
				$accepted[$id] = false;
			}
		}

		function checkID($id, &$array): void
		{
			if (!array_key_exists($id, $array)) {
				throw new MethodException("Invalid participants");
			}
			if ($array[$id]) {
				throw new MethodException("Same participants in multiple matches");
			}
			$array[$id] = true;
		}

		foreach ($pairs as $key => $p) {
			if (!array_key_exists('p1', $p) || !array_key_exists('p2', $p) || !array_key_exists('time', $p)) {
				throw new MethodException("API format error (missing data)");
			}
			try {
				$time = intval(strtotime($p['time']));
				$id1 = intval($p['p1']);
				$id2 = intval($p['p2']);
			}
			catch (Throwable $e) {
				throw new MethodException("API format error (invalid data value)");
			}
			if (time() >= $time) {
				throw new MethodException("Start time is in past");
			}
			if ($id1 == $id2) {
				throw new MethodException("Invalid combination");
			}
			if ($id1 == -2) {
				$tmp = $id1;
				$id1 = $id2;
				$id2 = $tmp;
				$pairs[$key]['p1'] = $id1;
				$pairs[$key]['p2'] = $id2;
			}
			checkID($id1, $accepted);
			if ($id2 != -2) {
				checkID($id2, $accepted);
			}
		}

		foreach($accepted as $a) {
			if (!$a) {
				throw new MethodException("Missing some participants");
			}
		}

		$sql = "INSERT INTO Matches (TournamentID, StartTime, Round, Points1, Points2, Member1ID, Team1ID, Member2ID, Team2ID, isBye) VALUES ";
		$i = 0;
		$data = ["id" => $t_id];
		foreach ($pairs as $p) {
			$id1 = "idA_" . $i;
			$id2 = "idB_" . $i;
			$tid = "time_" . $i;
			$i++;
			$data[$tid] = $p['time'];
			$data[$id1] = $p['p1'];
			if ($p['p2'] == -2) {
				$data[$id2] = NULL;
				$is_bye = "true";
			}
			else {
				$data[$id2] = $p['p2'];
				$is_bye = "false";
			}
			$sql .= "\n(:id, :" . $tid . ", 1, 0, 0, ";
			if ($tournament['type'] == 'team') {
				$sql .= "NULL, :" . $id1 . ", NULL, :" . $id2;
			}
			else {
				$sql .= ":" . $id1 . ", NULL, :" . $id2 . ", NULL";
			}
			$sql .= ", " . $is_bye . "),";
		}
		if (str_ends_with($sql, ",")) {
			$sql[strlen($sql) - 1] = ";";
		}

		$pdo = createDB();
		try {
			$pdo->beginTransaction();

			$stmt = $pdo->prepare("UPDATE Tournament SET ProgressState = 'ongoing', ActualRound = 1 WHERE TournamentID=:id AND ProgressState = 'unstarted';");
			$stmt->execute(['id' => $t_id]);

			if ($stmt->rowCount() == 0) {
				throw new PDOException("query failed");
			}

			$stmt2 = $pdo->prepare($sql);
			$stmt2->execute($data);

			$pdo->commit();
		}
		catch (PDOException $e){
			$pdo->rollback();
			throw $e;
		}
    }

		$stmt = $pdo->prepare("SELECT StartTime, ProgressState FROM Tournament WHERE TournamentID = :id");
		$stmt->execute(["id" => $id]);
		$time = $stmt->fetch(PDO::FETCH_NAMED);

		if ((time() <= intval(strtotime($time["StartTime"])) - 3600) || $time["ProgressState"] !== "unstarted") {
			return;
		}
		$stmt = $pdo->prepare("UPDATE Tournament SET ProgressState = 'ongoing' WHERE TournamentID = :id");
		$stmt->execute(["id" => $id]);

		error_log("tournament started");

	}

	/**
	 * @throws MethodException
	 */
	public function endAction(): void
	{
		$this->checkRequestMethod('POST');
		$id = $this->get($_POST, "id");

		$pdo = createDB();

		$stmt = $pdo->prepare("UPDATE Tournament SET ProgressState = 'finished' WHERE TournamentID = :id");
		$stmt->execute(["id" => $id]);

		error_log("tournament finished");
	}

	/**
	 * @throws MethodException
	 */
	public function deleteAction(): void
	{
		$this->checkRequestMethod('POST');
		$id = $this->get($_POST, "id");

		$pdo = createDB();

		$stmt = $pdo->prepare("DELETE FROM Tournament WHERE TournamentID = :id");
		$stmt->execute(["id" => $id]);

		error_log("tournament deleted");
	}

	/**
	 * @throws MethodException
	 */
	public function joinAction(): void
	{
		$this->checkRequestMethod('POST');
		$user_id = $this->getLoggedUserID();

		if (!isset($_POST["id"])) {
			throw new MethodException("Missing tournament id");
		}
		$id = $_POST["id"];

		$tournament = Database::getInstance()->getTournamentByID($id);
		if (!$tournament) {
			throw new MethodException("Tournament does not exist");
		}

		$pdo = createDB();
		if ($tournament['type'] == 'team') {
			if (!isset($_POST["team_name"])) {
				throw new MethodException("Missing team name");
			}
			$name = $_POST["team_name"];

			$stmt = $pdo->prepare("SELECT TeamID FROM Team WHERE Name=:name AND LeaderID=:l_id;");
			$stmt->execute(["name" => $name, "l_id" => $user_id]);
			$team = $stmt->fetch();
			if (!$team) {
				throw new MethodException("Team does not exist");
			}
			$team_id = $team['TeamID'];

			$stmt = $pdo->prepare("SELECT TournamentParticipantID FROM TournamentParticipant WHERE TournamentID=:id AND TeamID=:t_id;");
			$stmt->execute(["id" => $id, "t_id" => $team_id]);
			$participant = $stmt->fetch();
			if ($participant) {
				throw new MethodException("Already joined");
			}

			$stmt = $pdo->prepare("INSERT INTO TournamentParticipant VALUES (default, :id, NULL, :t_id, 'pending', 0);");
			$stmt->execute(["id" => $id, "t_id" => $team_id]);
		}
		else {

			$stmt = $pdo->prepare("SELECT TournamentParticipantID FROM TournamentParticipant WHERE TournamentID=:id AND MemberID=:m_id;");
			$stmt->execute(["id" => $id, "m_id" => $user_id]);
			$participant = $stmt->fetch();
			if ($participant) {
				throw new MethodException("Already joined");
			}

			$stmt = $pdo->prepare("INSERT INTO TournamentParticipant VALUES (default, :id, :m_id, NULL, 'pending', 0);");
			$stmt->execute(["id" => $id, "m_id" => $user_id]);

		}

	}

	/**
	 * @throws MethodException
	 */
	public function leaveAction(): void
	{
		$this->checkRequestMethod('POST');
		$user_id = $this->getLoggedUserID();

		if (!isset($_POST["id"])) {
			throw new MethodException("Missing tournament id");
		}
		$id = $_POST["id"];

		$tournament = Database::getInstance()->getTournamentByID($id);
		if (!$tournament) {
			throw new MethodException("Tournament does not exist");
		}

		if ($tournament['ProgressState'] != 'unstarted') {
			throw new MethodException("Tournament already started");
		}

		$pdo = createDB();
		if ($tournament['type'] == 'team') {
			if (!isset($_POST["team_id"])) {
				throw new MethodException("Missing team id");
			}
			$team_id = $_POST["team_id"];

			$stmt = $pdo->prepare("SELECT TeamID FROM Team WHERE TeamID=:id AND LeaderID=:l_id;");
			$stmt->execute(["id" => $team_id, "l_id" => $user_id]);
			$team = $stmt->fetch();
			if (!$team) {
				throw new MethodException("Team does not exist");
			}

			$stmt = $pdo->prepare("DELETE FROM TournamentParticipant WHERE TournamentID=:id AND TeamID=:t_id;");
			$stmt->execute(["id" => $id, "t_id" => $team_id]);
		}
		else {
			$stmt = $pdo->prepare("DELETE FROM TournamentParticipant WHERE TournamentID=:id AND MemberID=:m_id;");
			$stmt->execute(["id" => $id, "m_id" => $user_id]);
		}
	}

	/**
	 * @throws MethodException
	 */
	private function checkUpdateParticipant()
	{
		$this->checkRequestMethod('POST');
		$user_id = $this->getLoggedUserID();

		$id = $this->get($_POST, "t_id");
		$participant_id = $this->get($_POST, "p_id");

		$tournament = Database::getInstance()->getTournamentByID($id);
		if (!$tournament) {
			throw new MethodException("Tournament does not exist");
		}
		if ($tournament['CreatorID'] != $user_id) {
			throw new MethodException("Tournament is not owned by user");
		}
		if ($tournament['ProgressState'] != 'unstarted') {
			throw new MethodException("Tournament already started");
		}

		$pdo = createDB();
		$stmt = $pdo->prepare("SELECT TournamentParticipantID FROM TournamentParticipant WHERE TournamentParticipantID=:id;");
		$stmt->execute(["id" => $participant_id]);
		$participant = $stmt->fetch();
		if (!$participant) {
			throw new MethodException("Participant does not exist");
		}

		return $participant_id;
	}

	/**
	 * @throws MethodException
	 */
	public function acceptAction(): void
	{
		$participant_id = $this->checkUpdateParticipant();

		$pdo = createDB();
		$stmt = $pdo->prepare("UPDATE TournamentParticipant SET AcceptanceState='approved' WHERE TournamentParticipantID = ?;");
		$stmt->execute([$participant_id]);
	}

	/**
	 * @throws MethodException
	 */
	public function revokeAction(): void
	{
		$participant_id = $this->checkUpdateParticipant();

		$pdo = createDB();
		$stmt = $pdo->prepare("UPDATE TournamentParticipant SET AcceptanceState='pending' WHERE TournamentParticipantID = ?;");
		$stmt->execute([$participant_id]);
	}

	/**
	 * @throws MethodException
	 */
	public function kickAction(): void
	{
		$participant_id = $this->checkUpdateParticipant();

		$pdo = createDB();
		$stmt = $pdo->prepare("DELETE FROM TournamentParticipant WHERE TournamentParticipantID = ?;");
		$stmt->execute([$participant_id]);
	}

	/**
	 * @throws MethodException
	 */
	public function participantsAction(): array
	{
		$this->checkRequestMethod('GET');
		$id = $this->get($_GET, "id");

		$tournament = Database::getInstance()->getTournamentByID($id);
		if (!$tournament) {
			throw new MethodException("Tournament does not exist");
		}

		if ($tournament['type'] == 'team') {
			$participants = Database::getInstance()->getTeamParticipants($id);
		}
		else {
			$participants = Database::getInstance()->getMemberParticipants($id);
		}

		$output = array();
		foreach ($participants as $p) {
			if ($p['AcceptanceState'] == 'approved') {
				$obj['name'] = $p['Name'];
				if ($tournament['type'] == 'team') {
					$obj['id'] = $p['TeamID'];
				}
				else {
					$obj['id'] = $p['MemberID'];
				}
				$output[] = $obj;
			}
		}

		$result['participants'] = $output;
		return $result;
	}


	/**
	 * @throws MethodException
	 */
	public function round_resultsAction(): array
	{
		$this->checkRequestMethod('GET');
		$id = $this->get($_GET, "id");
		$tournament = Database::getInstance()->getTournamentByID($id);
		if (!$tournament) {
			throw new MethodException("Tournament does not exist");
		}

		$round = $_GET["round"]?? $tournament['ActualRound'];

		if ($tournament['type'] == 'team') {
			$matches = Database::getInstance()->getTeamMatches($id, $round);
		}
		else {
			$matches = Database::getInstance()->getMemberMatches($id, $round);
		}

		$results = [];
		// error_log(var_export($matches,true));
		$all_results = true;
		foreach ($matches as $m) {
			$winner = NULL;
			if ($tournament['type'] == 'team') {
				$winner_id = $m['WinnerTeamID'];
				if ($winner_id === $m['Team1ID']) {
					$winner = 0;
				} else if (!$m['isBye'] && $winner_id === $m['Team2ID']) {
					$winner = 1;
				}
			} else {
				$winner_id = $m['WinnerMemberID'];
				if ($winner_id === $m['Member1ID']) {
					$winner = 0;
				} else if (!$m['isBye'] && $winner_id === $m['Member2ID']) {
					$winner = 1;
				}
			}
			if ($winner === NULL) {
				// match has no winner yet
				$all_results = false;
			}
			else {
				$names = [Tournament::displayName($m, 0), Tournament::displayName($m, 1)];

				$r['id'] = $winner_id;
				$r['name'] = $names[$winner];
				$results[] = $r;
			}
			//$m['winner'] = $winner;
		}

		$result['results'] = $results;
		$result['round'] = $round;
		$result['complete'] = count($results) == 0? false : $all_results;
		return $result;
	}

	/**
	 * @throws MethodException
	 */
	public function start_roundAction(): void
	{
		$this->checkRequestMethod('POST');
		$user_id = $this->getLoggedUserID();
		$t_id = $this->get($_POST, "id");
		$round = intval($this->get($_POST, "round"));
		$pairs = $this->get($_POST, "pairs");
		if (!is_array($pairs) || count($pairs) == 0 || $round == 0) {
			throw new MethodException("API format error");
		}

		$tournament = new Tournament($t_id);
		if (!$tournament->exists()) {
			throw new MethodException("Tournament does not exist");
		}
		if ($tournament['CreatorID'] != $user_id) {
			throw new MethodException("Tournament is not owned by user");
		}
		if ($tournament['ActualRound'] + 1 !== $round) {
			throw new MethodException("Incorrect round");
		}
		$state = $round == 1 ? 'unstarted' : 'ongoing';
		if ($tournament['ProgressState'] != $state) {
			throw new MethodException("Incompatible action");
		}

		$prev_round = $round - 1;
		$accepted = [];
		if ($round == 1) {
			if ($tournament->isTeam()) {
				$participants = Database::getInstance()->getTeamParticipants($t_id);
			}
			else {
				$participants = Database::getInstance()->getMemberParticipants($t_id);
			}
			foreach ($participants as $p) {
				if ($p['AcceptanceState'] == 'approved') {
					$key = $tournament->isTeam()? 'TeamID' : 'MemberID';
					$id = $p[$key];
					$accepted[$id] = false;
				}
			}
		}
		else {
			if (!array_key_exists($prev_round, $tournament->rounds)) {
				throw new MethodException("Round does not exist");
			}
			$participants = $tournament->rounds[$prev_round];

			if (!$tournament->results[$prev_round]) {
				throw new MethodException("Previous round is not complete");
			}

			foreach ($participants as $p) {
				$key = $tournament->isTeam()? 'WinnerTeamID' : 'WinnerMemberID';
				$id = $p[$key];
				$accepted[$id] = false;
			}
		}

		if (count($accepted) <= 1) {
			throw new MethodException("Not enough participants to start next round");
		}

		function checkID($id, &$array): void
		{
			if (!array_key_exists($id, $array)) {
				throw new MethodException("Invalid participant");
			}
			if ($array[$id]) {
				throw new MethodException("Same participant in multiple matches");
			}
			$array[$id] = true;
		}

		foreach ($pairs as $key => $p) {
			if (!array_key_exists('p1', $p) || !array_key_exists('p2', $p) || !array_key_exists('time', $p)) {
				throw new MethodException("API format error (missing data)");
			}
			try {
				$time = intval(strtotime($p['time']));
				$id1 = intval($p['p1']);
				$id2 = intval($p['p2']);
			}
			catch (Throwable $e) {
				throw new MethodException("API format error (invalid data value)");
			}
			if (time() >= $time) {
				throw new MethodException("Start time is in past");
			}
			if ($id1 == $id2) {
				throw new MethodException("Invalid combination");
			}
			if ($id1 == -2) {
				$tmp = $id1;
				$id1 = $id2;
				$id2 = $tmp;
				$pairs[$key]['p1'] = $id1;
				$pairs[$key]['p2'] = $id2;
			}
			checkID($id1, $accepted);
			if ($id2 != -2) {
				checkID($id2, $accepted);
			}
		}

		foreach($accepted as $a) {
			if (!$a) {
				throw new MethodException("Missing some participants");
			}
		}

		$sql = "INSERT INTO Matches (TournamentID, StartTime, Round, Points1, Points2, Member1ID, Team1ID, Member2ID, Team2ID, isBye) VALUES ";
		$i = 0;
		$data = ["id" => $t_id, "round" => $round];
		foreach ($pairs as $p) {
			$id1 = "idA_" . $i;
			$id2 = "idB_" . $i;
			$tid = "time_" . $i;
			$i++;
			$data[$tid] = $p['time'];
			$data[$id1] = $p['p1'];
			if ($p['p2'] == -2) {
				$data[$id2] = NULL;
				$is_bye = "true";
			}
			else {
				$data[$id2] = $p['p2'];
				$is_bye = "false";
			}
			$sql .= "\n(:id, :" . $tid . ", :round, 0, 0, ";
			if ($tournament['type'] == 'team') {
				$sql .= "NULL, :" . $id1 . ", NULL, :" . $id2;
			}
			else {
				$sql .= ":" . $id1 . ", NULL, :" . $id2 . ", NULL";
			}
			$sql .= ", " . $is_bye . "),";
		}
		if (str_ends_with($sql, ",")) {
			$sql[strlen($sql) - 1] = ";";
		}

		$pdo = createDB();
		try {

			if ($round == 1) {
				$sql1 = "UPDATE Tournament SET ActualRound=?, ProgressState='ongoing' WHERE TournamentID=? AND ProgressState='unstarted'";
			}
			else {
				$sql1 = "UPDATE Tournament SET ActualRound=? WHERE TournamentID=? AND ActualRound=?";
			}

			$stmt1 = $pdo->prepare($sql1);
			$stmt1->bindParam(1, $round, PDO::PARAM_INT);
			$stmt1->bindParam(2, $t_id, PDO::PARAM_INT);
			if ($round != 1) {
				$stmt1->bindParam(3, $prev_round, PDO::PARAM_INT);
			}

			$stmt2 = $pdo->prepare($sql);

			$pdo->beginTransaction();
			$stmt1->execute();
			if ($stmt1->rowCount() == 0) {
				throw new PDOException("transaction failed");
			}
			$stmt2->execute($data);
			$pdo->commit();
		}
		catch (PDOException $e){
			$pdo->rollback();
			throw $e;
		}

	}


}