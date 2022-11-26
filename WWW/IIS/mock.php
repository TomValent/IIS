<?php
require_once "../../IIS-project/inc/bootstrap.php";

$result = array();
if (!isset($_POST['func'])) {
	$result['error'] = 'error';
}
else {

	try {
		switch($_POST['func']) {
			case 'add_user':
				$pdo = createDB();
				$data = [
					'username' => getArg('username'),
					'login' => getArg('login'),
					'password' => getArg('pass')
				];
				$sql = "INSERT INTO Member VALUES (default, :username, :login, :password, 0);";
				$stmt= $pdo->prepare($sql);
				$stmt->execute($data);
				Database::getInstance()->notify();
				break;
			case 'del_user':
				$pdo = createDB();
				$login = getArg('login');
				$sql = "DELETE FROM Member WHERE Login = '".$login."';";
				$stmt= $pdo->prepare($sql);
				$stmt->execute();
				break;
			case 'add_team':
				$name = $_POST['name'];
				$user_id = $_SESSION["id"];
				$pdo = createDB();
				$sql = "INSERT INTO Team VALUES (default, :name, :id, '');";
				$stmt= $pdo->prepare($sql);
				$stmt->execute(['name' => $name, 'id' => $user_id]);
				break;
			default:
				$result['error'] = 'Not found function '.$_POST['func'].'!';
				break;
		}
	} catch(ArgumentException $e) {
		$result['error'] = 'Input error!';
	} catch(PDOException $e) {
		$result['error'] = 'Database error! '. $e->getMessage();
	}  catch(Exception $e) {
		$result['error'] = 'Error! '. $e->getMessage();
	}

}

echo json_encode($result);
