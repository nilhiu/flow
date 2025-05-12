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
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($first_name) || empty($last_name) || empty($password)) {
        $message = '<p id="signup-error">All fields are required.</p>';
    } elseif (strlen($password) < 8) {
        $message = '<p id="signup-error">Password must be at least 8 characters long.</p>';
    } else {
        try {
            $stmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE email = :email');
            $stmt->execute([':email' => $email]);
            if ($stmt->fetchColumn()) {
                $message = '<p id="signup-error">Account with same email already exists.</p>';
            } else {
                $password_hash = password_hash($password, PASSWORD_BCRYPT);
                $stmt = $pdo->prepare('INSERT INTO users (email, first_name, last_name, password_hash) VALUES (:email, :first_name, :last_name, :password_hash)');
                $result = $stmt->execute([
                    ':email' => $email,
                    ':first_name' => $first_name,
                    ':last_name' => $last_name,
                    ':password_hash' => $password_hash,
                ]);

                if ($result) {
                    $message = '<p id="signup-error">Account created successfully.</p>';
                } else {
                    $message = '<p id="signup-error">Error creating account. Please try again.</p>';
                }
            }
        } catch (PDOException $e) {
            $message = '<p id="signup-error">An unexpected database error occurred.</p>';
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
    <link href="css/style.css" rel="stylesheet" />
</head>

<body>
    <h2>Sign Up</h2>
    <?php if (!empty($message)): ?>
        <div>
            <?php echo $message; ?>
        </div>
    <?php endif; ?>
    <form method="post">
        <label for="email">Email:</label>
        <input type="email" id="signup-email" name="email" value="<?php echo htmlspecialchars($email ?? ''); ?>" required>

        <label for="first_name">First Name:</label>
        <input type="text" id="signup-first-name" name="first_name" value="<?php echo htmlspecialchars($first_name ?? ''); ?>" required>

        <label for="last_name">Last Name:</label>
        <input type="text" id="signup-last-name" name="last_name" value="<?php echo htmlspecialchars($last_name ?? ''); ?>" required>

        <label for="password">Password:</label>
        <input type="password" id="signup-password" name="password" required>

        <input type="submit" value="Sign Up">
    </form>
</body>

</html>
