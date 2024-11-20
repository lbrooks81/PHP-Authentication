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
$loginSuccess = false;

if($_SERVER['REQUEST_METHOD'] === 'POST')
{
// / Get submitted form data
// * Trim removes trailing and leading whitespaces
    $email = trim($_POST['email']);
    $password = $_POST['password'];

// / Server-Side Validation
    if (empty($email))
    {
        $errors[] = 'Email is required.';
    }
    elseif (filter_var($email, FILTER_VALIDATE_EMAIL) === false)
    {
        $errors[] = 'Invalid email format.';
    }
    if (empty($password))
    {
        $errors[] = 'Password is required.';
    }

    if(empty($errors))
    {
        $stmt = $conn->prepare(
        "SELECT USER_ID, USER_PASSWORD 
        FROM USER
        WHERE USER_EMAIL = ?");

        if($stmt === false)
        {
            $errors[] = "Database error: Failed to prepare the SELECT statement.";
        }
        else
        {
            $stmt->bind_param("s", $email);
            $stmt->execute();

            // * Results are stored in an internal buffer
            // * This is needed to bind the results
            $stmt->store_result();

            if($stmt->num_rows === 1)
            {
                // * Binds the columns to the variables, no values are stored from this instruction
                // * Order should match that of the query
                $stmt->bind_result($userId, $hashedPassword);

                // * Fetch gets the values from the query and puts them into the variables
                $stmt->fetch();

                /// Password matched
                if(password_verify($password, $hashedPassword))
                {
                    $_SESSION['user_id'] = $userId;
                    $_SESSION['user_email'] = $email;
                    $_SESSION['logged_in'] = true;

                    header("Location: index.php");
                    exit;
                }
                else
                {
                    $errors[] = "Invalid password.";
                }
            }
            else
            {
                $errors[] = "Email does not have a matching account.";
            }

            $stmt->close();
        }
    }

}

$conn->close();

require_once "shared/header.inc";
?>

<div class="flex-container">
    <main class="form-signin w-100 m-auto">
        <form action="login.php" method="post">
            <h1 class="h3 mb-3 fw-normal">Log In</h1>
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

            <div class="form-floating">
                <input class="mb-3 form-control" id="email"
                       name="email" type="email"
                       placeholder="name@example.com" required>
                <label for="email">Email Address</label>
            </div>

            <div class="form-floating">
                <input class="form-control" id="password"
                       name="password" type="password"
                       placeholder="Password" required>
                <label for="password">Password</label>
            </div>

            <div class="my-3">
                Don't have an account? <a href="register.php">Register Here</a>
            </div>

            <button class="btn btn-primary w-100 py-2" type="submit">
                Log In
            </button>
            <p class="mt-5 mb-3 text-body-secondary">&copy; <?= date("Y") ?></p>
        </form>
    </main>
</div>

<?php
require_once 'shared/footer.inc';
?>
