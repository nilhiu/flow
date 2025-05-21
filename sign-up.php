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
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($first_name) || empty($last_name) || empty($password)) {
        $message = 'All fields are required.';
    } elseif (strlen($password) < 8) {
        $message = 'Password must be at least 8 characters long.';
    } else {
        try {
            $stmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE email = :email');
            $stmt->execute([':email' => $email]);
            if ($stmt->fetchColumn()) {
                $message = 'Account with same email already exists.';
            } else {
                $password_hash = password_hash($password, PASSWORD_BCRYPT);
                $stmt = $pdo->prepare('INSERT INTO users (email, first_name, last_name, password_hash) VALUES (:email, :first_name, :last_name, :password_hash)');
                $result = $stmt->execute(
                    [
                    ':email' => $email,
                    ':first_name' => $first_name,
                    ':last_name' => $last_name,
                    ':password_hash' => $password_hash,
                    ]
                );

                if (!$result) {
                    $message = 'Error creating account. Please try again.';
                }
            }
        } catch (PDOException $e) {
            $message = 'An unexpected database error occurred.';
            error_log('PDO Error on signup: ' . $e->getMessage());
        }
    }
}
?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>FLOW: Sign-Up</title>
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
        <?php if (!empty($message)) : ?>
            <p id="sign-up-error"><?php echo $message; ?></p>
        <?php endif; ?>
        <form method="post" id="sign-up-form">
            <label for="email">Email</label>
            <input type="email" id="signup-email" name="email" value="<?php echo htmlspecialchars($email ?? ''); ?>" required>

            <label for="first_name">First Name</label>
            <input type="text" id="signup-first-name" name="first_name" value="<?php echo htmlspecialchars($first_name ?? ''); ?>" required>

            <label for="last_name">Last Name</label>
            <input type="text" id="signup-last-name" name="last_name" value="<?php echo htmlspecialchars($last_name ?? ''); ?>" required>

            <label for="password">Password</label>
            <input type="password" id="signup-password" name="password" required>

            <input type="submit" value="Sign Up">
            <p>If you already have an account, <a href="login.php">log in</a></p>
        </form>
    </main>
    <div class="blue-box"></div>
</body>

</html>
