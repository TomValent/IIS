<?php


class TeamController extends BaseController
{

    /**
     * @throws MethodException
     */
    public function listAction(): array
    {

        $this->checkRequestMethod('GET');
        $user_id = NULL;
        if (isset($_SESSION["login"])) {
            $user = $_SESSION["login"];
            $user_id = Database::getInstance()->getUserID($user);
            error_log("user ID: ". $user_id);
        }
        $result = array();
        $body = "";

        $pdo = createDB();
        if($user_id!=NULL){
            $q = $pdo->query("SELECT TeamID, Name, Logo, Image FROM Team WHERE LeaderID = '$user_id'");
            $data = $q->fetchAll(PDO::FETCH_ASSOC);
/*
            $stmt = $pdo->prepaere('SELECT TeamID, Name, Logo, Image FROM Team WHERE CreatorID = :id');
            $stmt->execute(['id' => $user_id]);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
  */
            $body .= 'My teams:'. '<br>';
            foreach ($data as $row) {
                $id = $row['TeamID'];
                $body .= '<div class="teamsform"  > '
                    .'<div class="image">'
                    .'<a href="team?id='. $id .'">'
                    .'<img src="'.$row['Image'].'" />'
                    .'</a></div>'
                    .'<div>'
                    .'<a href="team?id='. $id .'">'
                    .$row['Name']
                    .'<br>'
                    .'<div style="display: inline">'
                    .'<form method="post" style="display: inline">'
                    .'<input type="hidden" id="TeamID" name="TeamID" value='. $id .'>'
                    .'<input type="submit" name="deleteTeam" value="Delete"/>'
                    .'</form>'
                    .'<div class="button_container" style="display: inline">'
                    .'<a href="editTeam?id='. $id .'">Edit</a>'
                    .'</div>'
                    .'</a></div></div></div>';
            }

            $q = $pdo->query("SELECT TeamID, Name, Logo, Image FROM Team WHERE LeaderID != '$user_id'");
            $data = $q->fetchAll(PDO::FETCH_ASSOC);
            $body .= 'Other teams:'. '<br>';
            foreach ($data as $row) {
                $id = $row['TeamID'];
                $body .= '<div class="teamsform"  > '
                    .'<div class="image">'
                    .'<a href="team?id='. $id .'">'
                    .'<img src="'.$row['Image'].'" />'
                    .'</a></div>'
                    .'<div>'
                    .'<a href="team?id='. $id .'">'
                    .$row['Name']
                    .'</a></div></div>';
            }
            $result['body'] = $body;
            return $result;

        }
        else{
            $q = $pdo->query('SELECT TeamID, Name, Logo, Image FROM Team');
            $data = $q->fetchAll(PDO::FETCH_ASSOC);
            $body .= 'Teams:'. '<br>';
            foreach ($data as $row) {
                $id = $row['TeamID'];
                $body .= '<div class="teamsform"  > '
                    .'<div class="image">'
                    .'<a href="team?id='. $id .'">'
                    .'<img src="'.$row['Image'].'" />'
                    .'</a></div>'
                    .'<div>'
                    .'<a href="team?id='. $id .'">'
                    .$row['Name']
                    .'</a></div></div>';
            }
            $result['body'] = $body;
            return $result;
        }


    }
}