<?php

namespace Core;

class Date
{

    public static function getMonth($id) {

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

    public static function format($date, $type = 'short') {


        if ($type == 'short') {

            $todayDatetime = new \DateTime('now');
            $datetime = new \DateTime($date);

            $isToday = $todayDatetime->format('Y-m-d') === $datetime->format('Y-m-d');
            $isThisYear = $todayDatetime->format('Y') === $datetime->format('Y');
            if ($isToday) {
                return $datetime->format('h:i A');
            }
            else if ($isThisYear) {
                return $datetime->format('d') . ' de ' . Date::getMonth($datetime->format('m'));
            }
            else {
                return Date::getMonth($datetime->format('m')) . ' de ' . $datetime->format('Y');
            }
        } else if($type == 'human') {
            return self::formatForHuman($date);
        }

        return $date;
    }
    

    private static function formatForHuman($datetime, $full = false) {
        
        
        $now = new \DateTime;
        $ago = new \DateTime($datetime);
        $prefix = $now > $ago ? 'hace ' : 'en ';
        $diff = $now->diff($ago);
    
        $diff->w = floor($diff->d / 7);
        $diff->d -= $diff->w * 7;
    
        $string = array(
            'y' => 'año',
            'm' => 'mes',
            'w' => 'semana',
            'd' => 'dia',
            'h' => 'hora',
            'i' => 'minuto',
            's' => 'segundo',
        );
        foreach ($string as $k => &$v) {
            if ($diff->$k) {
                $postfix = '';
                if($diff->$k > 1){
                    $postfix = ($k == 'm' ? 'es' : 's');
                }                
                
                $v = $diff->$k . ' ' . $v . $postfix;
            } else {
                unset($string[$k]);
            }
        }
        
        
        if (!$full) $string = array_slice($string, 0, 1);
        return $string ? $prefix . implode(', ', $string) : 'ahora';
    }
}
