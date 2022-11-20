<?php

class TournamentController extends BaseController {

    private function getState($pdo, $tournament_id, $user_id): ?string
	{
        if (!$user_id) {
            return NULL;
        }

        $stmt= $pdo->prepare('SELECT AcceptanceState FROM TournamentParticipant WHERE TournamentID=:t_id AND MemberID=:m_id');
        $stmt->execute(['t_id' => $tournament_id, "m_id" => $user_id]);
        $data = $stmt->fetch();
        if (!$data) {
            return 'none';
        }
        return $data['AcceptanceState'];
    }

	/**
	 * @throws MethodException
	 */
	public function listAction(): array
	{
		$this->checkRequestMethod('GET');

		$pdo = createDB();

		$q = $pdo->query('SELECT TournamentID, Name, type FROM Tournament ORDER BY StartTime');
		$data = $q->fetchAll(PDO::FETCH_ASSOC);

		$result = array();
		$body = '';

		foreach ($data as $row) {
			$id = $row['TournamentID'];

			$body .= '<div class="tournament_list_row"> '
				.'<div class="button_container" style="display: inline">'
				. '<a href="tournament?id=' . $id . '">'.$row['Name'].'</a>'
				. '</div></div><br>';
		}


		$result['body'] = $body;
		return $result;
    }

	/**
	 * @throws MethodException
	 */
    public function startAction(): void
	{
		$this->checkRequestMethod('POST');

    }

	/**
	 * @throws MethodException
	 */
	public function joinAction(): void
	{
		$this->checkRequestMethod('POST');

		$this->checkLoggedIn();

		if (!isset($_POST["id"])) {
			throw new MethodException("Missing tournament id");
		}
		$id = $_POST["id"];
		$user_id = $_SESSION["id"];

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

		$this->checkLoggedIn();

		if (!isset($_POST["id"])) {
			throw new MethodException("Missing tournament id");
		}
		$id = $_POST["id"];
		$user_id = $_SESSION["id"];

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

		$this->checkLoggedIn();
		$user_id = $_SESSION["id"];
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

}