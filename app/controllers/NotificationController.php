<?php

namespace Controller;

class NotificationController extends \Core\Controller
{

    public function clearAll() {
        $strSQL = "UPDATE notifications SET deleted=1, updated_at=CURRENT_TIMESTAMP  WHERE user_id='{$_SESSION['USER_ID']}' AND NOT deleted";
        $this->db->query($strSQL)->execute();
        return $this->redirect('/');
    }

    public function goto($id = null) {
        $strSQL = "UPDATE notifications SET deleted=1, updated_at=CURRENT_TIMESTAMP  WHERE user_id='{$_SESSION['USER_ID']}' AND id='{$id}'";
        $this->db->query($strSQL)->execute();
        return $this->redirect($_POST['url']);
    }

}