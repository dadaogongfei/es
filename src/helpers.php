<?php
function merge($a,$b) {
   $args = func_get_args();
   $res = array_shift($args);
   while (!empty($args)) {
       $next =array_shift($args);
       foreach ($next as $k => $v) {
           if ($v instanceof \Zyw\Es\UnsetArrayValue) {
               unset($res[$k]);
           } elseif ($v instanceof \Zyw\Es\ReplaceArrayValue) {
               $res[$k] = $v->value;
           } elseif (is_int($k)) {
               if (isset($res[$k])) {
                   $res[] = $v;
               } else {
                   $res[$k] = $v;
               }
           } elseif (is_array($v) && isset($res[$k]) && is_array($res[$k])) {
               $res[$k] = merge($res[$k], $v);
           } else {
               $res[$k] = $v;
           }
       }
   }
   return $res;
}
function fields_to_key($keys,$value) {
    if (count($keys)==0) {
        return $value;
    }
    return [$keys[0]=>fields_to_key(array_slice($keys,1),$value)];
}
