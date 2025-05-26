<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: dashboard.php');
    exit;
}

if (!isset($_POST['member_email']) || !isset($_POST['project_id'])) {
    header('Location: dashboard.php');
    exit;
}

$db_path = __DIR__ . '/db/sqlite.db';

try {
    $pdo = new PDO('sqlite:' . $db_path);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die('Database connection failed: ' . $e->getMessage());
}

try {
    $stmt = $pdo->prepare('SELECT COUNT(project_id) FROM userProjects WHERE user_id = :user_id AND project_id = :project_id');
    $stmt->execute(
        [
            ':user_id' => $_SESSION['user_id'],
            ':project_id' => $_POST['project_id'],
        ]
    );
    if ($stmt->fetchColumn() == 0) {
        header('Location: dashboard.php');
        exit;
    }

    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
    $stmt->execute([':email' => $_POST['member_email']]);
    $user = $stmt->fetch();
    if (!$user) {
        header('Location: dashboard.php');
        exit;
    }

    $stmt = $pdo->prepare('INSERT INTO userProjects (user_id, project_id, role) VALUES (:user_id, :project_id, "member")');
    $stmt->execute(
        [
            ':user_id' => $user['id'],
            'project_id' => $_POST['project_id'],
        ]
    );
} catch (PDOException $e) {
    error_log('add-member.php: ' . $e->getMessage());
    header('Location: dashboard.php');
    exit;
}

header('Location: dashboard.php');
exit;
