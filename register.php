<?php
require_once "shared/dbcreds.inc";

/**
 * @var mysqli $conn
 */

if($conn->connect_error)
{
    die("Connection Failed: " . $conn->connect_error);
}

if(session_status() !== PHP_SESSION_ACTIVE)
{
    session_start();
}

$errors = [];
$successMessage = '';

if($_SERVER['REQUEST_METHOD'] === 'POST')
{
    // / Get submitted form data
    // * Trim removes trailing and leading whitespaces
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // / Server-Side Validation
    if(empty($email))
    {
        $errors[] = 'Email is required.';
    }
    elseif(filter_var($email, FILTER_VALIDATE_EMAIL) === false)
    {
        $errors[] = 'Invalid email format.';
    }

    if(empty($password))
    {
        $errors[] = 'Password is required.';
    }
    elseif(strlen($password) < 8)
    {
        $errors[] = 'Password must contain at least 8 characters.';
    }

    if(empty($errors))
    {
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);

        try
        {
            // * Query preparation is done to prevent SQL injection attacks
            // * ?'s will be filled when they are bound to actual values
            $stmt = $conn->prepare("INSERT INTO USER (USER_EMAIL, USER_PASSWORD) VALUES(?, ?)");

            if($stmt === false)
            {
                $errors[] = "Database error: Failed to prepare the INSERT statement.";
            }
            else
            {
                $stmt->bind_param('ss', $email, $passwordHash);

                $stmt->execute();
                $successMessage = "Registration successful! You can now <a href='login.php'>log in</a>.";

                $stmt->close();
            }
        }
        catch(Exception $e)
        {
            // * code 1062 means duplicate entries
            if($e->getCode() === 1062)
            {
                $errors[] = htmlentities($email . " is already registered.");
            }
            else
            {
                $errors[] = "Error: " . $e->getMessage();
            }
        }
    }

}




$conn->close();

require_once "shared/header.inc";
?>

<div class="flex-container">
    <main class="form-signin w-100 m-auto">
        <form action="register.php" method="post">
            <h1 class="h3 mb-3 fw-normal">Register an Account</h1>
            <!-- h3 allows us to use the h3 style while still getting SEO benefits
            fw stands for font-width, fw-normal removes the bold style -->

            <?php if(empty($errors) === false): ?>
                <div class="alert alert-danger">
                    <ul>
                        <?php foreach($errors as $error):?>
                            <li><?= htmlentities($error) ?></li>
                        <?php endforeach;?>
                    </ul>
                </div>
            <?php endif;?>

            <?php if(empty($successMessage) === false): ?>
                <div class="alert alert-success">
                    <?= $successMessage ?>
                </div>
            <?php endif; ?>

            <div class="form-floating">
                <input class="form-control my-3" id="email"
                       name="email" type="email"
                       placeholder="name@example.com" required>
                <label for="email">Email Address</label>
            </div>

            <div class="form-floating">
                <input class="form-control" id="password"
                       name="password" type="password"
                       placeholder="Password" minlength="8"
                       required>
                <label for="password">Password</label>
            </div>

            <div class="my-3">
                Have an account? <a href="login.php">Log in Here</a>
            </div>

            <button class="btn btn-primary w-100 py-2" type="submit">
                Register
            </button>
            <p class="mt-5 mb-3 text-body-secondary">&copy; <?= date("Y") ?></p>
        </form>
    </main>
</div>

<?php
require_once 'shared/footer.inc';
?>
