<?php

if (!isset($_GET['id'])) {
    echo 'Tournament does not exist';
    exit();
}

try {
    $id = $_GET['id'];
    $pdo = createDB();

    $stmt = $pdo->prepare("SELECT * FROM Tournament WHERE TournamentID=:id");
    $stmt->execute(['id' => $id]);
    $tournament = $stmt->fetch();
    if (!$tournament) {
        echo 'Tournament does not exist';
        exit();
    }

    $stmt = $pdo->prepare("SELECT * FROM TournamentParticipant WHERE TournamentID=:id");
    $stmt->execute(['id' => $id]);
    echo 'Participants:<br>';
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo $row['MemberID'].'<br>';
    }
    echo '<br>';

    echo '<div id="content">';
?>
<script>
    function checkResult(obj) {
        if( !('error' in obj) ) {
            console.log('ok');
            return true;
        }
        else {
            console.log(obj.error);
            if (obj.debug) {
                console.log(obj.debug);
            }
            return false;
        }
    }

    function startTournament(id) {
        let url = 'api.php/tournament/start';
        jQuery.ajax({
            type: "POST",
            url: url,
            dataType: 'json',
            data: { id: id },
            success: (obj) => {
                if (checkResult(obj)) {
                    $('#content').html(obj.body);
                }
            }
        });

    }
</script>
<?php

    if ($tournament['ProgressState'] == 'unstarted') {
        echo '<button onclick="startTournament('.$id.')">start</button><br>';
    }
    echo '</div>';

    // echo count($data). '  ' . $data[0]['Name'];

} catch (PDOException $e) {
    $result['error'] = "Connection error: " . $e->getMessage();
    die();
}
?>

