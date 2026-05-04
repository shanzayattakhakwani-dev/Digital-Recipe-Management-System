<?php
// logout.php
session_start();
//here destroy the session to clear out all the values so that  we can logout....
session_destroy();
header('Location: index.php');
exit();
?>