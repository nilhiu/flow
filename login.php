<?php
session_start();

$db_path = __DIR__ . '/db/sqlite.db';

try {
    $pdo = new PDO("sqlite:" . $db_path);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $message = '<p id="login-error">All fields are required.</p>';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT id, email, first_name, last_name, password_hash FROM users WHERE email = :email");
            $stmt->execute([':email' => $email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password_hash'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['first_name'] = $user['first_name'];
                $_SESSION['last_name'] = $user['last_name'];

                session_regenerate_id(true);

                header('Location: dashboard.php');
                exit;
            } else {
                $message = '<p id="login-error">Invalid username or password.</p>';
            }
        } catch (PDOException $e) {
            $message = '<p id="login-error">An unexpected database error occurred.</p>';
            error_log('PDO Error on login: ' . $e->getMessage());
        }
    }
}
?>

<!doctype html>

<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>FLOW: Login</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
        href="https://fonts.googleapis.com/css2?family=Lexend:wght@100..900&family=Monomaniac+One&display=swap"
        rel="stylesheet" />
    <link href="css/style.css" rel="stylesheet" />
</head>

<body>
    <h2>Login</h2>
    <?php if (!empty($message)): ?>
        <div>
            <?php echo $message; ?>
        </div>
    <?php endif; ?>
    <form method="post">
        <label for="email">Email:</label>
        <input type="email" id="login-email" name="email" value="<?php echo htmlspecialchars($email ?? ''); ?>" required>

        <label for="password">Password:</label>
        <input type="password" id="login-password" name="password" required>

        <input type="submit" value="Login">
    </form>
</body>

</html>
