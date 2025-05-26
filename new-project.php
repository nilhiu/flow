<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
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

$project_name = trim($_POST['project_name'] ?? '');
if (empty($project_name)) {
    error_log('<p id="project-create-error">All fields are required.</p>');
} else {
    try {
        $stmt = $pdo->prepare('INSERT INTO projects (name) VALUES (:name)');
        $result_p = $stmt->execute([':name' => $project_name]);
        $project = $stmt->fetch();

        $stmt = $pdo->prepare('INSERT INTO userProjects (user_id, project_id, role) VALUES (:user_id, :project_id, :role)');
        $result_up = $stmt->execute(
            [
                ':user_id' => $_SESSION['user_id'],
                ':project_id' => $pdo->lastInsertId(),
                ':role' => 'admin',
            ]
        );

        if ($result_p && $result_up) {
            error_log('<p id="project-create-error">Project created successfully.</p>');
        } else {
            error_log('<p id="project-create-error">Error creating project. Please try again.</p>');
        }
    } catch (PDOException $e) {
        error_log('PDO Error on project creation: ' . $e->getMessage());
    }
}

header('Location: dashboard.php');
exit;
