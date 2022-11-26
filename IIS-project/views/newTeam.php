<?php


define ('SITE_ROOT', realpath(dirname(dirname(dirname(__FILE__)))));
function error(string $msg): void
{
    echo $msg;
    echo "  <div class='right'>
                        <button><a href='index.php/teams'>Go back to teams</a></button>
                    </div>";
    exit;
}


if($_POST['submitTeam']) {
    if (!empty($_POST['name'])) {
        $teamName = $_POST['name'];
        $creatorID = $_SESSION["id"];

        if (!empty($_FILES['file'])) {
            $name = $_FILES['file']['name'];
            $target_dir = "upload/";
            $target_file = $target_dir . basename($_FILES["file"]["name"]);
            $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
            $extensions_arr = array("jpg", "jpeg", "png");
            if ($name == ""){
                $image_base64 = base64_encode(file_get_contents("https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQFiRsN1ktgi8BXl24BGH6JGtZmk6WsSs_Hhg&usqp=CAU"));
                $image = 'data:image/' . 'png' . ';base64,' . $image_base64;
            }
            else {
                if (in_array($imageFileType, $extensions_arr)) {
                    $image_base64 = base64_encode(file_get_contents($_FILES['file']['tmp_name']));
                    $image = 'data:image/' . $imageFileType . ';base64,' . $image_base64;
                }
            }
        }

        $pdo = createDB();
        $sql = "INSERT INTO Team(Name, LeaderID, Logo, Image) VALUES ('$teamName','$creatorID', '$name', '$image')";
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            header("Location: teams");

        } catch (Exception $e) {
            if ($e->getCode() == 23000) {
                error ("Team with this name already exists.");
            } else {
                error("Error in creating team. Please try again");
            }
        }
        echo "Team created!";
    }
}
?>
<div class='right'>
    <button><a href='index.php/teams'>Go back</a></button>
</div>
<form action = "newTeam" class="table" method="post" enctype="multipart/form-data">
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
                <label class="strong" for="file">Logo</label>
            </td>
            <td>
                <input type="file" class="padd"  name="file">
            </td>
        </tr>

    </table>
    <div class="center">
        <input type="submit" name="submitTeam" value="Create"/>
    </div>
</form>
