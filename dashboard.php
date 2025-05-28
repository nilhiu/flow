<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$db_path = __DIR__ . '/db/sqlite.db';

$project_list = [];
$member_list = [];

try {
    $pdo = new PDO('sqlite:' . $db_path);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die('Database connection failed: ' . $e->getMessage());
}

try {
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

    $stmt = $pdo->prepare('SELECT user_id FROM userProjects WHERE project_id = :project_id');
    $stmt->execute([':project_id' => $_GET['project']]);
    $user_projects = $stmt->fetchAll();
    foreach ($user_projects as $user_project) {
        $stmt = $pdo->prepare('SELECT email, first_name, last_name FROM users WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $user_project['user_id']]);
        $user = $stmt->fetch();
        $member_list[] = $user;
    }
} catch (PDOException $e) {
    die('Database connection failed: ' . $e->getMessage());
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
    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@100..900&family=Monomaniac+One&display=swap" rel="stylesheet" />
    <link href="css/root.css" rel="stylesheet" />
    <link href="css/dashboard.css" rel="stylesheet" />
</head>

<body>
    <div class="sidebar">
        <div class="profile-box">
            <div class="avatar-circle"></div>
            <div class="profile-info">
                <p><?php echo $_SESSION['first_name']; ?></p>
                <p><?php echo $_SESSION['last_name']; ?></p>
            </div>
        </div>

        <nav>
            <div class="action-modals">
                <ul>
                    <li>
                        <button id="new-project-modal-btn" onclick="openNewProjectModal()">New Project</button>
                    </li>
                    <li>
                        <button id="add-member-modal-btn" onclick="openAddMemberModal()">Add Member</button>
                    </li>
                    <li>
                        <button id="logout-modal-btn" onclick="openLogoutModal()">Log Out</button>
                    </li>
                </ul>
            </div>
            <div class="action-links">
                <ul>
                    <li>
                        <a href="file_edit.php?project=<?php echo $_GET['project']; ?>">
                            Project Plan
                        </a>
                    </li>
                </ul>
            </div>
            <div class="project-list">
                <h3>Projects</h3>
                <ul>
                    <?php foreach ($project_list as $project): ?>
                        <li>
                            <a href="dashboard.php?project=<?php echo $project['id']; ?>">
                                <?php echo $project['name']; ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </nav>
        <div class="footer">
            <h4 class="logo-small">FLOW</h4>
            <p>&copy; 2025 Giorgi Matiashvili. This project is free software licensed under the GNU General Public License.</p>
        </div>
    </div>

    <div id="dashboard">
        <div id="member-list">
            <h2>Project Members</h2>
            <ul>
                <?php foreach ($member_list as $member): ?>
                    <li>
                        <div class="avatar-circle orange"></div>
                        <div class="member-info">
                            <p><?php echo $member['first_name'] . ' ' . $member['last_name'] ?></p>
                            <p><?php echo $member['email'] ?></p>
                        </div>
                    </li>
                <?php endforeach ?>
            </ul>
        </div>
    </div>

    <div class="modal" id="new-project-modal">
        <form action="new-project.php" method="post">
            <label for="project_name">Project Name:</label>
            <input type="text" id="create-project-name" name="project_name" required>
            <input type="submit" value="Create Project">
        </form>
    </div>

    <div class="modal" id="add-member-modal">
        <form action="add-member.php" method="post">
            <label for="member_email">Member Email:</label>
            <input type="email" id="add-member-email" name="member_email" required>
            <input type="hidden" value="<?php echo $_GET['project'] ?>" name="project_id">
            <input type="submit" value="Add Member">
        </form>
    </div>

    <div class="modal" id="logout-modal">
        <form action="logout.php" method="post">
            <p>Are you sure you want to log out?</p>
            <input type="submit" value="Log Out">
        </form>
    </div>

    <script src="js/modal.js"></script>
</body>

</html>
