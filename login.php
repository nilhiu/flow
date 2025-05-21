<?php
session_start();

if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

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
        $message = 'All fields are required.';
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
                $message = 'Invalid username or password.';
            }
        } catch (PDOException $e) {
            $message = 'An unexpected database error occurred.';
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
    <link href="css/root.css" rel="stylesheet" />
    <link href="css/auth.css" rel="stylesheet" />
</head>

<body>
    <div class="blue-box"></div>
    <main>
        <h1 class="logo-medium">FLOW</h1>
        <?php if (!empty($message)): ?>
            <p id="sign-up-error"><?php echo $message; ?></p>
        <?php endif; ?>
        <form method="post" id="login-form">
            <label for="email">Email:</label>
            <input type="email" id="login-email" name="email" value="<?php echo htmlspecialchars($email ?? ''); ?>" required>

            <label for="password">Password:</label>
            <input type="password" id="login-password" name="password" required>

            <input type="submit" value="Login">
            <p>If you don’t have an account, <a href="sign-up.php">sign up</a></p>
        </form>
    </main>
    <div class="blue-box"></div>
</body>

</html>
