<div class='right'>
    <button><a href='/index.php/teams'>Back to teams</a></button>
</div>
<?php


try{
    $id = $_GET['id'];
    $user_id = $_SESSION["id"];
    $pdo = createDB();
    $stmt = $pdo->prepare("SELECT * FROM Team WHERE TeamID=:id");
    $stmt->execute(['id' => $id]);
    $team = $stmt->fetch();
    $tean_owned = $user_id == $team["LeaderID"];
}catch (PDOException $e) {
    echo $e->getMessage();
    die();
}

if (isset($_POST['addMember'])){
    if(!empty($_POST['login'])){
        $newMemberLogin = $_POST['login'];
        $pdo = createDB();
        $stmt = $pdo->prepare("SELECT * FROM Member WHERE Login=:login");
        $stmt->execute(['login' => $newMemberLogin]);
        $member = $stmt->fetch();
        if (!$member) {
            error ('Member does not exist');
            exit();
        }
        else {
            $memberID = $member['MemberID'];
            $sql = "INSERT INTO MemberTeam(MemberID,TeamID) VALUES ('$memberID ','$id')";
            $stmt= $pdo->prepare($sql);
            $stmt->execute();
            $member = $stmt->fetch();
        }
    }
}

if (isset($_POST['deleteMember'])){
    if(!empty($_POST['login'])){
        $newMemberLogin = $_POST['login'];
        $pdo = createDB();
        $stmt = $pdo->prepare("SELECT * FROM Member WHERE Login=:login");
        $stmt->execute(['login' => $newMemberLogin]);
        $member = $stmt->fetch();
        if (!$member) {
            error ('Member does not exist');
            exit();
        }
        else {
            $memberID = $member['MemberID'];
            $stmt= $pdo->prepare("DELETE FROM MemberTeam WHERE MemberID=:id");
            $stmt->execute(['id' => $memberID]);
        }
    }
}

try {
    $pdo = createDB();

    $stmt = $pdo->prepare("SELECT * FROM Team WHERE TeamID=:id");
    $stmt->execute(['id' => $id]);
    $team = $stmt->fetch();
    if (!$team) {
        error('Team does not exist');
        exit();
    }
    echo $team["Name"];
    echo '<br>';
    echo '<img src="'.$team['Image'].'" />';
    echo '<br>';

    $stmt = $pdo->prepare("SELECT DISTINCT TeamID, MemberID FROM MemberTeam WHERE TeamID=:id");
    $stmt->execute(['id' => $id]);
    echo 'Members:<br>';
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $stmt2 = $pdo->prepare("SELECT Login FROM Member WHERE MemberID=:id");
        $stmt2->execute(['id' => $row['MemberID']]);
        $member = $stmt2->fetch();
        echo $member['Login'].'<br>';
    }
    echo '<br>';


} catch (PDOException $e) {
    $result['error'] = "Connection error: " . $e->getMessage();
    die();
}
?>

<?php if (isset($_SESSION["login"])  &&  $tean_owned ): ?>
<form method="post" >
    <label class="strong" for="login">Login</label>
    <input type="text" name="login">
    <input type="submit" name="addMember" value="Add"/>
    <input type="submit" name="deleteMember" value="Delete"/>
</form>
<?php endif ?>
