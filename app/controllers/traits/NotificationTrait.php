<?php

namespace Controller\Traits;
use \Core\Database;
use \Core\Date;

trait NotificationTrait {

  private function getNotificationColor($type){
    switch($type){
      case 'sent':
        return 'green';
      case 'received':
        return 'blue';
      case 'user':
        return 'yellow';
      default: 
        return 'blue';
    }
  }

  private function getNotifications() {
    $strSQL="SELECT * FROM notifications WHERE user_id='{$_SESSION['USER_ID']}' AND NOT deleted ORDER BY id DESC";
    $rsNotifications = Database::getInstance()->query($strSQL)->resultset();
    foreach($rsNotifications as $key => $value) {
      $rsNotifications[$key]['created_at_human'] = Date::format($value['created_at'], 'human');
      $rsNotifications[$key]['color'] = $this->getNotificationColor($value['type']);
    }
    
    return [
      'list' => $rsNotifications,
      'count' => count($rsNotifications)
    ];
  }

}
