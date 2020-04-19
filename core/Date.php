<?php 

namespace Core;

class Date {

  public static function getMonth($id){
      
    $months = [
        'Enero',
        'Febrero',
        'Marzo',
        'Abril',
        'Mayo',
        'Junio',
        'Julio',
        'Agosto',
        'Septiembre',
        'Octubre',
        'Noviembre',
        'Diciembre'
      ];
      $position = intval($id) - 1;
      return !empty($months[$position]) ? $months[$position] : 'Enero';

  }

  public static function format($date, $type = 'short'){
    
    
    if($type == 'short') {
      
      $todayDatetime = new \DateTime('now');
      $datetime = new \DateTime($date);  
      
      $isToday = $todayDatetime->format( 'Y-m-d' ) === $datetime->format( 'Y-m-d' );
      $isThisYear = $todayDatetime->format( 'Y' ) === $datetime->format( 'Y' );
      if($isToday){
          return $datetime->format('h:i A');
      } else if($isThisYear){
          return $datetime->format('d') . ' de ' . Date::getMonth($datetime->format('m'));
      } else {
          return Date::getMonth($datetime->format('m')) . ' de ' . $datetime->format('Y');
      } 
    }

    return $date;
  }

}