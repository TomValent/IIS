<?php

    function error(string $msg): void
    {
        echo $msg;
		echo "  <div class='right'>
                        <button><a href='/index.php/tournaments'>Go back to tournaments</a></button>
                </div>";
		exit;
    }

    if (!empty($_GET['name']) && !empty($_GET['start']) && !empty($_GET['type']) && !empty($_GET['participants'])) {
        error_log("i am in");

        error_log("timezone: " . date_default_timezone_get());
		error_log("time: ". date('m/d/Y h:i:s a', time()));
		error_log("start: ". date('m/d/Y h:i:s a', strtotime($_GET['start'])));

        if ($_GET['type'] === "team") {
			if (!empty($_GET['min']) && !empty($_GET['max'])) {
				if (intval($_GET["participants"]) <= 0 || intval($_GET["min"]) <= 0 || intval($_GET["max"]) <= 0) {
					error("Too little participants or team members");
				}
				if (intval($_GET["participants"]) < 2*intval($_GET["max"]) || intval($_GET["max"] < intval($_GET["min"]))) {
					error("Minimum is 2 teams and maximum must be bigger than minimum");
				}
            }
        }

		if (time() >= intval(strtotime($_GET['start'])) - 3600) {   //zle casove pasmo pri konvertovani
			error("Tournament must start in future");
		}

		$data = [
            'name' => $_GET['name'],
            'startTime' => $_GET['start'],
            'type' => $_GET['type'],
            'participantCount' => intval($_GET['participants']),
            'maxCountTeam' => intval($_GET['max']),
            'minCountTeam' => intval($_GET['min']),
            'approvalState' => "created",
            'progressState' => "unstarted",
        ];

        if (empty($_GET['description'])) {
            $data["description"] = "";
        } else {
            $data["description"] = $_GET['description'];
        }

        if (empty($_GET['price'])) {;
            $data["price"] = "None";
        } else {
            $data["price"] = $_GET['price'];
        }

		$data["creatorID"] = $_SESSION["id"];

        $pdo = createDB();

        $sql = "INSERT INTO Tournament VALUES (default, :name, :creatorID, :startTime, :description, :price, :type, :participantCount, :maxCountTeam, :minCountTeam, :approvalState, :progressState)";

		try {
			$stmt= $pdo->prepare($sql);
			$stmt->execute($data);
		} catch (Exception $e) {
            error("Error in creating tournament. Please try again");
        }
        echo "Tournament created!";
    }  else if (!empty($_GET["submitted"])) {
		error_log("i am in else");
		error("Tournament not edited. Missing required information.");
	} else {
		error_log("i am in despair");
    }
?>
<div class='right'>
    <button><a href='/index.php/tournaments'>Go back</a></button>
</div>
<p>If you choose type Member, min and max team members fields are not required.</p>
<form class="table" method="get">
    <table>
        <tr>
            <td>
                <label class="strong" for="name">Name</label>
            </td>
            <td>
                <input type="text" class="padd" name="name">
            </td>
        </tr>
        <tr>
            <td>
                <label class="strong" for="startTime">Start time</label>
            </td>
            <td>
                <input type="datetime-local" name="start">
            </td>
        </tr>
        <tr>
            <td>
                <label class="strong" for="price">Price</label>
            </td>
            <td>
                <input type="text" class="padd" name="price">
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
                <input type="number" class="padd" name="participants">
            </td>
        </tr>
        <tr>
            <td>
                <label class="strong" for="MaxCountTeam">Max team members</label>
            </td>
            <td>
                <input type="number" class="padd" name="max">
            </td>
        </tr>
        <tr>
            <td>
                <label class="strong" for="MaxCountTeam">Min team members</label>
            </td>
            <td>
                <input type="number" class="padd" name="min">
            </td>
        </tr>
        <tr>
            <td>
                <label class="strong" for="description">Description</label>
            </td>
            <td>
                <input type="text" class="padd" name="description">
            </td>
        </tr>
    </table>
    <div class="center">
        <input type="hidden" name="submitted" value="submitted">
        <input type="submit" value="Create" href="/index.php/tournaments" />
    </div>
</form>
