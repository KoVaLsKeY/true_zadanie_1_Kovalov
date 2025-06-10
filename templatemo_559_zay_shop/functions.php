<?php
function greet(){
    $hour = date("H");
    if ($hour < 12 && !isset($_SESSION['user']['meno'])) {
        return "Good morning!";
    } elseif ($hour < 18 && !isset($_SESSION['user']['meno'])) {
        return "Good afternoon!";
    } elseif ($hour < 24 && !isset($_SESSION['user']['meno'])) {
        return "Good evening!";
    } elseif ($hour < 12) {
        return "Good morning, " . $_SESSION['user']['meno'] . "!";
    } elseif ($hour < 18) {
        return "Good afternoon, " . $_SESSION['user']['meno'] . "!";
    } else {
        return "Good evening, " . $_SESSION['user']['meno'] . "!";
    }
}
?>