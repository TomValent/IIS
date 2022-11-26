<?php

class UserController extends BaseController
{

	/**
	 * @throws MethodException
	 */
	public function logoutAction(): void
	{

		error_log("logout action");
		$this->checkRequestMethod('GET');

		unset($_SESSION);
		session_destroy();
		session_write_close();
	}

	/**
	 * @throws MethodException
	 */
	public function registerAction(): void
	{
		$this->checkRequestMethod('POST');

		if (!isset($_POST["username"]) || $_POST["username"] == "") {
			throw new MethodException("Please enter username");
		}

		if (!isset($_POST["login"]) || $_POST["login"] == "") {
			throw new MethodException("Please enter login");
		}

		if (!isset($_POST["pass"]) || $_POST["pass"] == "") {
			throw new MethodException("Please enter password");
		}

		if (strlen(strval($_POST["pass"])) < 8) {
			throw new MethodException("Password must have at least 8 characters");
		}

		$data = [
			'username' => $_POST['username'],
			'login' => $_POST['login'],
			'password' => password_hash($_POST['pass'], PASSWORD_DEFAULT)
		];

		$pdo = createDB();
		$sql = "INSERT INTO Member VALUES (default, :username, :login, :password, 0)";
		$stmt= $pdo->prepare($sql);
		$stmt->execute($data);
	}

	/**
	 * @throws MethodException
	 */
	public function loginAction(): void
	{
		$this->checkRequestMethod('POST');

		if (!isset($_POST["login"])) {
			throw new MethodException('Missing login field');
		}
		if (!isset($_POST["pass"])) {
			throw new MethodException('Missing password field');
		}

		$login = $_POST["login"];
		$pass = $_POST["pass"];

		$pdo = createDB();
		$stmt = $pdo->prepare("SELECT * FROM Member WHERE Login=:login");
		$stmt->execute(['login' => $login]);

		$user = $stmt->fetch();
		if (!$user) {
			throw new MethodException('User does not exist');
		}

		if (!password_verify($pass, $user['Password'])) {
			throw new MethodException('Wrong password');
		}

		$_SESSION["login"] = $user["Login"];
		$_SESSION["id"] = $user["MemberID"];
		$_SESSION["username"] = $user["Name"];
		$_SESSION["isAdmin"] = $user["IsAdmin"];
	}

	/**
	 * @throws MethodException
	 */
	public function owned_teamsAction(): array
	{
		$this->checkRequestMethod('GET');
		$this->checkLoggedIn();
		$user_id = $_SESSION["id"];

		$pdo = createDB();
		$stmt = $pdo->prepare("SELECT Name FROM Team WHERE LeaderID=:id");
		$stmt->execute(['id' => $user_id]);

		$teams = array();

		while ($row = $stmt->fetch()) {
			$teams[] = $row['Name'];
		}

		$result['teams'] = $teams;
		return $result;
	}

	/**
	 * @throws MethodException
	 */
	public function deleteAction(): void
	{
		$this->checkRequestMethod('POST');

		$id = $_SESSION["id"];
		$pdo = createDB();

		$q1 = $pdo->prepare("SELECT t.TournamentID FROM Tournament t LEFT JOIN TournamentParticipant tp ON t.TournamentID = tp.TournamentID ".
         							"WHERE t.type = 'member' AND t.ProgressState = 'ongoing' AND tp.MemberID = :id");
		$q1->execute(["id" => $id]);
		$var1 = $q1->fetch(PDO::FETCH_NAMED);

		$q2 = $pdo->prepare("SELECT * FROM Tournament t LEFT JOIN TournamentParticipant tp ON t.TournamentID = tp.TournamentID ".
    								"LEFT JOIN MemberTeam mt ON mt.TeamID=tp.TeamID ".
         							"WHERE t.type = 'team' AND t.ProgressState = 'ongoing' AND mt.MemberID = :id");
		$q2->execute(["id" => $id]);
		$var2 = $q2->fetch(PDO::FETCH_NAMED);

		if (!empty($var1) || !empty($var2)) {
			throw new MethodException("You are in ongoing tournament. You can delete your account after tournament is finished.");
		}

		$stmt = $pdo->prepare("DELETE FROM Member WHERE MemberID = :id");
		$stmt->execute(["id" => $id]);
		unset($_SESSION);
		unset($_GET);
		unset($_POST);
		session_destroy();
		session_write_close();

		error_log("Account deleted");
	}
}