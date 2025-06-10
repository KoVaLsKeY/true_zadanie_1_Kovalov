<?php
session_start();
session_destroy();
header("Location: ../../stranky/index.php");
exit;
