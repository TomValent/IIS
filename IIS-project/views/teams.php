<div class='right'>
	<button><a href='page'>Back to main page</a></button>
</div>

<?php

function deleteTeam(int $id):void
{
    $pdo = createDB();
    $stmt = $pdo->prepare("DELETE FROM Team WHERE TeamID=:id");
    $stmt->execute(['id' => $id]);
}

if (isset($_POST['deleteTeam'])) {
    $id = $_POST["TeamID"];
    deleteTeam($id);
}


?>

<script>

    function getTeams() {
        get(<?php echo url("/api.php/team/list") ?>, function(data) {
            $('#teams').html(data.body);
        });
    }

    function onLogout() {
        getTeams();
    }


    // initialize jQuery
    $(function() {
        getTeams();
    });

    /*
    function deleteTeam(tournament_id) {
        let data = {
            id: tournament_id
        }
        api.post({
            url: <?php echo url("/api.php/team/delete") ?>,
            data: data,
        })
    }
    */

</script>




<?php if (isset($_SESSION["login"])): ?>
    <div class="button_container">
        <button><a href="/index.php/newTeam">Create Team</a></button>
    </div></br>
<?php endif ?>
<div id="teams"></div>
