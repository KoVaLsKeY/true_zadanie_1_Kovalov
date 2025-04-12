<?php
function greet($name) {
    $hour = date("H");
    if ($hour < 12) {
        return "Good morning, $name!";
    } elseif ($hour < 18) {
        return "Good afternoon, $name!";
    } else {
        return "Good evening, $name!";
    }
    
}
?>