<?php
    function error(string $msg): void
    {
		unset($_GET["edit"]);
		echo "  <div class='right'>
                            <button><a href='player?id=".$_GET['id']."'>Go back to profile</a></button>
                </div>";
		echo $msg;
		exit;
	}

    if ($_GET["id"] == NULL) {
        error("You have no profile");
        exit;
    }

    $pdo = createDB();
    $stmt = $pdo->prepare("SELECT Password FROM Member WHERE MemberID = :id");
    $stmt->execute(["id" => $_GET["id"]]);
    $pwd = $stmt->fetch(PDO::FETCH_NAMED);

    if (!empty($_POST["newUsername"]) && !empty($_POST["pass"])) {
		if (!password_verify($_POST["pass"], $pwd["Password"])) {
			error('Wrong password');
		}

        try {
            $stmt = $pdo->prepare("UPDATE Member SET Name = :username WHERE MemberID = :id");
            $stmt->execute(["username" => $_POST['newUsername'], "id" => $_GET["id"]]);
        } catch (PDOException|Exception $e) {
            error("Changing username failed. Please try again");
        }

        echo "Username changed!";
    }

    if (!empty($_POST["oldPass"]) && !empty($_POST["newPass"])) {
        if (strlen($_POST["newPass"]) < 8) {
            error("Password must have at least 8 characters");
        }
        if ($_POST["oldPass"] === $_POST["newPass"]) {
			error("New password cannot be same as old password");
        }
		if (!password_verify($_POST["oldPass"], $pwd["Password"])) {
			error('Wrong password');
		}

        try {
			$stmt = $pdo->prepare("UPDATE Member SET Password = :pass WHERE MemberID = :id");
			$stmt->execute(["pass" => password_hash($_POST['newPass'], PASSWORD_DEFAULT), "id" => $_GET["id"]]);
		} catch (PDOException|Exception $e) {
            error("Changing password failed. Please try again");
        }

		echo "Password changed!";
    }
?>
<div class='right'>
	<?php echo "<button><a href='player?id=". $_SESSION["id"] ."'>Go back to your profile</a></button>";?>
</div>
<?php if ($_SESSION["id"] === intval($_GET["id"])): ?>
<div class="row center">
    <div class="column">
        <div class="center"><p>Here you can change your username</p></div>
        <form class="center twoForms" method="post">
            <span class="red">*</span><label for="username">Insert new username</label></br>
            <input class="input" type="text" id="pass" name="newUsername"></br></br>
            <span class="red">*</span><label for="pass">Insert your password</label></br>
            <input class="input" type="password" id="pass" name="pass"></br></br>
            <input type="submit" value="Submit" />
        </form>
    </div>
    <div class="column">
        <div class="center"><p>Here you can change your password</p></div>
        <form class="center twoForms" method="post">
            <span class="red">*</span><label for="oldPass">Insert old password</label></br>
            <input class="input" type="password" id="pass" name="oldPass"></br></br>
            <span class="red">*</span><label for="newPass">Insert new password</label></br>
            <input class="input" type="password" id="pass" name="newPass"></br></br>
            <input type="submit" value="Submit" />
        </form>
    </div>
</div>
<?php endif ?>