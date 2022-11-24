<div class='right'>
    <button><a href='players'>Back to list of players</a></button>
</div>
<?php
    $pdo = createDB();
    $q = $pdo->prepare("SELECT * FROM Member m WHERE m.MemberID = :id");
    $q->execute(["id" => $_GET["id"]]);
    $user = $q->fetch(PDO::FETCH_NAMED);
?>

<span>Profile of <?php echo $user["Name"]; ?></span></br>
<div class="center margin" style="margin-top:100px;">Matches stats</div>
<table style="margin-top:5px;">
    <td>
        <!--insert vysledok hnusneho selectu-->
        Tournament
    </td>
    <td>
        Team 1
    </td>
    <td>
        Team 2
    </td>
    <td>
        Score 1
    </td>
    <td>
        Score 2
    </td>
    <tr>
        <td>
            todo
        </td>
        <td>
            todo
        </td>
        <td>
            todo
        </td>
        <td>
            todo
        </td>
        <td>
            todo
        </td>
    </tr>
</table>
