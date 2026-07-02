<?php
session_start();

/* Admin sessions remove */
unset($_SESSION['admin_username']);
unset($_SESSION['LAST_ACTIVITY_ADMIN']);
unset($_SESSION['CREATED_ADMIN']);
unset($_SESSION['LOGIN_TIME_ADMIN']);

session_regenerate_id(true);

header("Location: index.php");
exit();
?>