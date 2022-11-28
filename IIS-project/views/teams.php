<div class='right'>
    <a href='page'><button>Back to main page</button></a>
</div>
<?php if (isset($_SESSION["login"])): ?>
    <div class="button_container">
        <a href='newTeam'><button>Create Team</button></a>
    </div></br>
<?php endif ?>

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
        getContent("../index.php/frags/tournament_list", "#content");
        api.get({ url: "../api.php/team/list",
            success: function(data) {
                $('#teams').html(data.body);
            }});
    }

    function onLogout() {
        getTeams();
    }


    // initialize jQuery
    $(function() {
        getTeams();
    });

</script>

<div id="teams"></div>