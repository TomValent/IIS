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
		/*
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
*/
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
		$stmt = $pdo->prepare("SELECT Login, Password, IsAdmin, MemberID FROM Member WHERE Login=:login");
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
		$_SESSION["isAdmin"] = $user["IsAdmin"];
	}

}