<?php

// * Sessions cannot be unset or destroy without starting a session
session_start();
session_unset();
session_destroy();

header("Location: /login.php");

// * Makes sure nothing else can happen after the redirection
exit;
?>