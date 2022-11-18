<?php

    if (!empty($_GET['name']) && !empty($_GET['start']) && !empty($_GET['type']) && !empty($_GET['participants']) && !empty($_GET['min']) && !empty($_GET['max'])) {
        error_log("i am in");

        $data = [
            'name' => $_GET['name'],
            'startTime' => $_GET["start"],
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

        if (empty($_GET['price'])) {
            $data["description"] = "None";
        } else {
            $data["price"] = "None";
        }

		$data["creatorID"] = $_SESSION["id"];

        $pdo = createDB();

        $sql = "INSERT INTO Tournament VALUES (default, :name, :creatorID, :startTime, :description, :price, :type, :participantCount, :maxCountTeam, :minCountTeam, :approvalState, :progressState)";
        error_log("cas " . $data["startTime"]);
        $stmt= $pdo->prepare($sql);
        $stmt->execute($data);
    }
    else {
        error_log("error " . time());
    }
?>
<div class='right'>
    <button><a href='/index.php/tournaments'>Go back</a></button>
</div>
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
                <input type="text" class="padd" name="type"><!-- select  -->
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
        <input type="submit" value="Create" />
    </div>
</form>
