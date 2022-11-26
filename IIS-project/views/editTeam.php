<?php

define ('SITE_ROOT', realpath(dirname(dirname(dirname(__FILE__)))));

$id = $_GET["id"];
$pdo = createDB();
$stmt = $pdo->prepare("SELECT * FROM Team WHERE TeamID=:id");
$stmt->execute(['id' => $id]);
$team = $stmt->fetch();
$teamName = $team["Name"];

if($_POST['editTeam']) {
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
                $sql = "UPDATE Team SET Name='$teamName' WHERE TeamID='$id' ";
            }
            else {
                if (in_array($imageFileType, $extensions_arr)) {
                    if (move_uploaded_file($_FILES['file']['tmp_name'], SITE_ROOT . "/WWW/data/logo/" . $name)) {
                        $image_base64 = base64_encode(file_get_contents(SITE_ROOT . "/WWW/data/logo/" . $name));
                        $image = 'data:image/' . $imageFileType . ';base64,' . $image_base64;
                    }

                }
                $sql = "UPDATE Team SET Name='$teamName', Logo='$name', Image='$image' WHERE TeamID='$id' ";
            }
        }
        else{
            $sql = "UPDATE Team SET Name='$teamName' WHERE TeamID='$id' ";
        }
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            header("Location: teams");

        } catch (Exception $e) {
            if ($e->getCode() == 23000) {
                error( "Team with this name already exists.");
            } else {
                error("Error in creating team. Please try again");
            }
        }
        echo "Team edited!";
    }
}
?>
<div class='right'>
    <button><a href='/index.php/teams'>Go back</a></button>
</div>
<form class="table" method="post" enctype="multipart/form-data">
    <table>
        <tr>
            <td>
                <label class="strong" for="name">Name</label>
            </td>
            <td>
                <input type="text" class="padd" name="name" value="<?php echo $teamName; ?>">
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
        <input type="submit" name="editTeam" value="Edit"/>
    </div>
</form>