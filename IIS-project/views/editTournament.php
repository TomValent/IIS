<?php
    function error(string $msg): void
    {
        echo $msg;
		echo "  <div class='right'>
                        <button><a href='/index.php/tournament?id=".$_GET['id']."'>Go back to tournament detail</a></button>
                </div>";
		exit;
    }
    $pdo = createDB();
    $sql = "SELECT * FROM Tournament WHERE TournamentID = :id";
    $info = [];

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute(["id" => $_GET["id"]]);
		$info = $stmt->fetch();
	} catch (Exception $e) {
		error("Error in loading old tournament information. Please try again");
	}

    if ($info["ProgressState"] === "unstarted") {
		if (!empty($_POST['name']) && !empty($_POST['start']) && !empty($_POST['type']) && !empty($_POST['participants'])) {
			error_log("i am in");

			error_log("timezone: " . date_default_timezone_get());
			error_log("time: ". date('m/d/Y h:i:s a', time()));
			error_log("start: ". date('m/d/Y h:i:s a', strtotime($_POST['start'])));

			if ($_POST['type'] === "team") {
				if (!empty($_POST['min']) && !empty($_POST['max'])) {
					if (intval($_POST["participants"]) <= 0 || intval($_POST["min"]) <= 0 || intval($_POST["max"]) <= 0) {
						error("Too little participants or team members");
					}
					if (intval($_POST["participants"]) < 2*intval($_POST["max"]) || intval($_POST["max"] < intval($_POST["min"]))) {
						error("Minimum is 2 teams and maximum must be bigger than minimum");
					}
				}
			}

			if (time() >= intval(strtotime($_POST['start'])) - 3600) {
				error("Tournament must start in future");
			}

			$data = [
				'id' => $_GET['id'],
				'name' => $_POST['name'],
				'startTime' => $_POST['start'],
				'type' => $_POST['type'],
				'participantCount' => intval($_POST['participants']),
				'maxCountTeam' => intval($_POST['max']),
				'minCountTeam' => intval($_POST['min']),
			];

			if (empty($_POST['description'])) {
				$data["description"] = "";
			} else {
				$data["description"] = $_POST['description'];
			}

			if (empty($_POST['price'])) {
				$data["price"] = "None";
			} else {
				$data["price"] = $_POST['price'];
			}

			$pdo = createDB();

			$sql = "UPDATE Tournament ".
				"SET Name = :name, StartTime = :startTime, Description = :description, Price = :price, ".
				"Type = :type, ParticipantCount = :participantCount, MaxCountTeam = :maxCountTeam, MinCountTeam = :minCountTeam ".
				"WHERE TournamentID = :id";

			try {
				$stmt= $pdo->prepare($sql);
				$stmt->execute($data);
			} catch (Exception $e) {
				error("Error in editing tournament. Please try again");
			}
			echo "Tournament edited!";
		} else if (!empty($_POST["submitted"])) {
			error("Tournament not edited. Missing required information.");
		}
	} else {
        echo "You can't edit started or finished tournament!";
    }
?>
<div class='right'>
    <?php
        echo "<button><a href='/index.php/tournament?id=".$_GET['id']."'>Go back to tournament detail</a></button>";
    ?>
</div>
<?php if ($info["ProgressState"] === "unstarted"): ?>
<p>If you choose type Member, min and max team members fields are not required.</p>
<form class="table" method="post">
    <table>
        <tr>
            <td>
                <label class="strong" for="name">Name</label>
            </td>
            <td>
                <?php
                    echo '<input type="text" class="padd" name="name" value="'. $info["Name"] .'">';
                ?>
            </td>
        </tr>
        <tr>
            <td>
                <label class="strong" for="startTime">Start time</label>
            </td>
            <td>
				<?php
				    echo '<input type="datetime-local" name="start" value="'. $info["StartTime"] .'">';
				?>
            </td>
        </tr>
        <tr>
            <td>
                <label class="strong" for="price">Price</label>
            </td>
            <td>
				<?php
				    echo '<input type="text" class="padd" name="price" value="'. $info["Price"] .'">';
				?>
            </td>
        </tr>
        <tr>
            <td>
                <label class="strong" for="type">Type</label>
            </td>
            <td>
                <select class="select" name="type">
                    <option value="team">Team</option>
                    <option value="member">Member</option>
                </select>
            </td>
        </tr>
        <tr>
            <td>
                <label class="strong" for="ParticipantCount">Participant count</label>
            </td>
            <td>
				<?php
				    echo '<input type="number" class="padd" name="participants" value="'. $info["ParticipantCount"] .'">';
				?>
            </td>
        </tr>
        <tr>
            <td>
                <label class="strong" for="MaxCountTeam">Max team members</label>
            </td>
            <td>
				<?php
				    echo '<input type="number" class="padd" name="max" value="'. $info["MaxCountTeam"] .'">';
				?>
            </td>
        </tr>
        <tr>
            <td>
                <label class="strong" for="MaxCountTeam">Min team members</label>
            </td>
            <td>
				<?php
				    echo '<input type="number" class="padd" name="min" value="'. $info["MinCountTeam"] .'">';
				?>
            </td>
        </tr>
        <tr>
            <td>
                <label class="strong" for="description">Description</label>
            </td>
            <td>
				<?php
				    echo '<input type="text" class="padd" name="description" value="'. $info["Description"] .'">';
				?>
            </td>
        </tr>
    </table>
    <div class="center">
        <input type="hidden" name="submitted" value="submitted">
        <input type="submit" value="Submit" href="/index.php/tournaments" />
    </div>
</form>
<?php endif ?>