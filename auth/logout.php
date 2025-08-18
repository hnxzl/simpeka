<?php
require_once '../init.php';
session_destroy();
header('Location: ../public/index.php');
exit();
?>
