<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$db_path = __DIR__ . '/db/sqlite.db';

$project_list = [];

try {
    $pdo = new PDO('sqlite:' . $db_path);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare('SELECT project_id FROM userProjects WHERE user_id = :user_id');
    $stmt->execute([':user_id' => $_SESSION['user_id']]);
    $user_projects = $stmt->fetchAll();

    if (!isset($_GET['project'])) {
        header('Location: dashboard.php?project=' . $user_projects[0]['project_id']);
        exit;
    }

    foreach ($user_projects as $user_project) {
        $stmt = $pdo->prepare('SELECT id, name FROM projects WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $user_project['project_id']]);
        $project = $stmt->fetch();
        $project_list[] = $project;
    }
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $project_name = trim($_POST['project_name'] ?? '');
    if (empty($project_name)) {
        $message = '<p id="project-create-error">All fields are required.</p>';
    } else {
        try {
            $stmt = $pdo->prepare('INSERT INTO projects (name) VALUES (:name)');
            $result_p = $stmt->execute([':name' => $project_name]);
            $project = $stmt->fetch();

            $stmt = $pdo->prepare('INSERT INTO userProjects (user_id, project_id, role) VALUES (:user_id, :project_id, :role)');
            $result_up = $stmt->execute([
                ':user_id' => $_SESSION['user_id'],
                ':project_id' => $pdo->lastInsertId(),
                ':role' => 'admin',
            ]);

            if ($result_p && $result_up) {
                $message = '<p id="project-create-error">Project created successfully.</p>';
            } else {
                $message = '<p id="project-create-error">Error creating project. Please try again.</p>';
            }
        } catch (PDOException $e) {
            $message = '<p id="project-create-error">An unexpected database error occurred.</p>';
            error_log('PDO Error on project creation: ' . $e->getMessage());
        }
    }
}
?>

<!doctype html>

<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>FLOW: Dashboard</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
        href="https://fonts.googleapis.com/css2?family=Lexend:wght@100..900&family=Monomaniac+One&display=swap"
        rel="stylesheet" />
    <link href="css/style.css" rel="stylesheet" />
</head>

<body>
    <h2>Dashboard</h2>
    <?php if (!empty($message)): ?>
        <div>
            <?php echo $message; ?>
        </div>
    <?php endif; ?>
    <form method="post">
        <label for="project_name">Project Name:</label>
        <input type="text" id="create-project-name" name="project_name" required>

        <input type="submit" value="Create Project">
    </form>
    <?php foreach ($project_list as $project): ?>
        <p>
            <?php echo $project; ?>
        </p>
    <?php endforeach; ?>
</body>

</html>
