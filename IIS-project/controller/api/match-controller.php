<?php

class MatchController extends BaseController
{


	/**
	 * @throws MethodException
	 */
	public function set_resultAction(): void
	{
		$this->checkRequestMethod('POST');
		$user_id = $this->getLoggedUserID();

		$id = $this->get($_POST, "id");
		$t_id = $this->get($_POST, "t_id");

		$tournament = Database::getInstance()->getTournamentByID($t_id);
		if (!$tournament) {
			throw new MethodException("Tournament does not exist");
		}
		if ($tournament['CreatorID'] != $user_id) {
			throw new MethodException("Tournament is not owned by user");
		}
		if ($tournament['ProgressState'] != 'ongoing') {
			throw new MethodException("Tournament is not in progress");
		}

		$match = $this->getMatch($id, $t_id);
		if ($match['Round'] != $tournament['ActualRound']) {
			throw new MethodException("This match is in different round");
		}

		$winner = 0;
		$points1 = $this->get($_POST, "points1");
		$points2 = 0;
		if (!$match['isBye']) {
			$winner = $_POST['winner']?? NULL;
			$points2 = $this->get($_POST, "points2");
		}

		$sql = "UPDATE Matches SET Points1=:pts1, Points2=:pts2, ";
		$data = [
			"id" => $id,
			"t_id" => $t_id,
			"pts1" => $points1,
			"pts2" => $points2
		];
		if ($tournament['type'] == 'team') {
			$field = $winner == 0? 'Team1ID' : 'Team2ID';
			$sql .= "WinnerTeamID=:win_id";
		}
		else {
			$field = $winner == 0? 'Member1ID' : 'Member2ID';
			$sql .= "WinnerMemberID=:win_id";
		}
		$data["win_id"] = $winner == NULL? NULL : $match[$field];

		$sql .= " WHERE MatchID=:id AND TournamentID=:t_id ";

		$pdo = createDB();
		$stmt = $pdo->prepare($sql);
		$stmt->execute($data);
	}

	/**
	 * @throws MethodException
	 */
	public function getAction(): array
	{
		$this->checkRequestMethod('GET');

		$id = $this->get($_GET, "id");
		$t_id = $this->get($_GET, "t_id");

		$tournament = Database::getInstance()->getTournamentByID($t_id);
		if (!$tournament) {
			throw new MethodException("Tournament does not exist");
		}

		if ($tournament['type'] == 'team') {
			$match = Database::getInstance()->getTeamMatch($t_id, $id);
		}
		else {
			$match = Database::getInstance()->getMemberMatch($t_id, $id);
		}
		if (!$match) {
			throw new MethodException("Match does not exist");
		}
		if ($match['Round'] != $tournament['ActualRound']) {
			throw new MethodException("This match is in different round");
		}

		$result = array();
		$result['MatchID'] = $match['MatchID'];
		$result['Points1'] = $match['Points1'];
		$result['Points2'] = $match['Points2'];
		$result['isBye'] = $match['isBye'];
		$result['Name'] = $match['Name'];
		if ($tournament['type'] == 'team') {
			$result['ID'] = $match['TeamID'];
			$result['Winner'] = [$match['TeamID'][0] === $match['WinnerTeamID'],
							     $match['TeamID'][1] === $match['WinnerTeamID']];
		}
		else {
			$result['ID'] = $match['MemberID'];
			$result['Winner'] = [$match['MemberID'][0] === $match['WinnerMemberID'],
								 $match['MemberID'][1] === $match['WinnerMemberID']];
		}

		return $result;
	}

}