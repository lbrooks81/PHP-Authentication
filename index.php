<?php
if(session_status() !== PHP_SESSION_ACTIVE)
{
    session_start();
}

// / For all protected resources, check if user is authorized
require_once "shared/auth_check.inc";

$userEmail = $_SESSION['user_email'];

require_once "shared/header.inc";
?>

<div class="flex-container">
    <main class="w-100 m-auto text-center">
        <h1>Welcome, <?= $userEmail ?></h1>
        <p>Glad to see you back.</p>

        <a href="misc/logout.php" class="btn btn-danger mt-3">
            Log Out
        </a>

        <p class="mt-5 mb-3 text-body-secondary">&copy; <?= date("Y") ?></p>

    </main>
</div>

<?php
require_once "shared/footer.inc";
?>
