<?php

namespace Controller;

use \Core\Database;

class NotificationController extends \Core\Controller
{

    public function clearAll() {
        $date = date('Y-m-d H:i:s');
        $strSQL = "UPDATE notifications SET deleted=1, updated_at='{$date}'  WHERE user_id='{$_SESSION['USER_ID']}' AND NOT deleted";
        $this->db->query($strSQL)->execute();
        return $this->redirect('/');
    }

    public function goto($id = null) {
        $date = date('Y-m-d H:i:s');
        $strSQL = "UPDATE notifications SET deleted=1, updated_at='{$date}'  WHERE user_id='{$_SESSION['USER_ID']}' AND id='{$id}'";
        $this->db->query($strSQL)->execute();
        return $this->redirect($_POST['url']);
    }

    public static function send($subject, $message, $url='/', $type='sent'){
        $date = date('Y-m-d H:i:s');
        $strSQL="INSERT INTO notifications (user_id, subject, message, url, type, created_at) VALUES ('{$_SESSION['USER_ID']}', '{$subject}', '{$message}', '{$url}', '{$type}', '{$date}')";
        Database::getInstance()->query($strSQL)->execute();
        return true;
    }
}