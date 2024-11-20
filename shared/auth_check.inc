<?php
if(session_status() !== PHP_SESSION_ACTIVE)
{
    session_start();
}

if(isset($_SESSION['user_id']) === false)
{
    header("Location: login.php");
    exit;
    // * This will be included on protected pages, which are only accessible by users who are logged in
}
