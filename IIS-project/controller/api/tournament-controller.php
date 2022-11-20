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

		$user_id = NULL;
		if (isset($_SESSION["login"])) {
			$user = $_SESSION["login"];
			$user_id = Database::getInstance()->getUserID($user);
			error_log("user ID: ". $user_id);
		}

		$pdo = createDB();

		$q = $pdo->query('SELECT TournamentID, Name FROM Tournament');
		$data = $q->fetchAll(PDO::FETCH_ASSOC);

		$result = array();
		$body = "";

		foreach ($data as $row) {
			$id = $row['TournamentID'];
			$state = $this->getState($pdo, $id, $user_id);
			error_log("State: " . $state);

			$body .= '<div style="display: inline"> '.$row['Name'] . ' ' .
				'<div class="button_container" style="display: inline">'
				.'<a href="tournament?id='. $id .'">Detail</a>'
				.'</div></div>';
			if ($state) {
				if ($state == 'none') {
					$body .= '  <button onclick="joinTournament(' . $id . ')">Join</button>';
				}
				else {
					if ($state == 'pending') {
						$body .= '  (pending)  ';
					}
					$body .= '  <button onclick="leaveTournament(' . $id . ')">Leave</button>';
				}
				if ($_SESSION["isAdmin"]) {
					$body .= '  <button onclick="deleteTournament(' . $id . ')">Delete</button></br>';
				}
			}
			$body .= '<br>';
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

}