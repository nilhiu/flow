<?php
session_start();

$return_url = 'file_edit.php?project=' . $_POST['project_id'];

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . $return_url);
    exit;
}

if (!isset($_FILES['document']) || !isset($_POST['project_id'])) {
    header('Location: ' . $return_url);
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
        header('Location: ' . $return_url);
        exit;
    }
} catch (PDOException $e) {
    error_log(__FILE__ . ': ' . $e->getMessage());
    header('Location: ' . $return_url);
    exit;
}

header('Location: ' . $return_url);

$file = __DIR__ . '/data/' . $_POST['project_id'] . '/' . basename($_FILES['document']['name']);
if ($_FILES['document']['size'] > 500000 || file_exists($file)) {
    exit;
}

move_uploaded_file($_FILES['document']['tmp_name'], $file);
exit;
