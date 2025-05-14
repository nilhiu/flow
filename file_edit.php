<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if (!isset($_GET['project'])) {
    header('Location: dashboard.php');
    exit;
}

try {
    $pdo = new PDO('sqlite:' . __DIR__ . '/db/sqlite.db');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare('SELECT project_id FROM userProjects WHERE user_id = :user_id');
    $stmt->execute([':user_id' => $_SESSION['user_id']]);
    $user_projects = $stmt->fetchAll();

    $is_users_project = false;
    foreach ($user_projects as $user_project) {
        if ($user_project['project_id'] == $_GET['project']) {
            $is_users_project = true;
            break;
        }
    }

    if (!$is_users_project) {
        header('Location: dashboard.php');
        exit;
    }
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

$data_dir = __DIR__ . '/data/';
$project_file = $data_dir . $_GET['project'] . '.txt';
$docs_dir = __DIR__ . '/data/' . $_GET['project'] . '/';
$docs = scandir($docs_dir);
if ($docs) {
    $docs = array_slice($docs, 2);
}

$page_title = 'Project Plan';

if (isset($_GET['file'])) {
    $filepath = realpath($docs_dir . basename($_GET['file']));
    if (!$filepath && !is_file($filepath)) {
        header('Location: file_edit.php?project=' . $_GET['project']);
        exit;
    }

    if (isset($_GET['download'])) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $filepath);
        finfo_close($finfo);

        if (!$mime_type) {
            $mime_type = 'application/octet-stream';
        }

        header('Content-Description: File Transfer');
        header('Content-Type: ' . $mime_type);
        header('Content-Disposition: attachment; filename="' . $_GET['file'] . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filepath));

        if (ob_get_level()) {
            ob_end_clean();
        }

        readfile($filepath);
        exit;
    }

    $data_dir = $data_dir . $_GET['project'] . '/';
    $project_file = $data_dir . $_GET['file'];
    $page_title = $_GET['file'];
    $docs = [];
}

if (!is_dir($data_dir)) {
    if (!mkdir($data_dir, 0770, true)) {
        error_log("Failed to create data directory");
        header('Location: dashboard.php?project=' . $_GET['project']);
        exit;
    }
}

if (!is_file($project_file)) {
    $file = fopen($project_file, 'w');
    if (!$file) {
        error_log("Failed to create project plan file");
        header('Location: dashboard.php?project=' . $_GET['project']);
        exit;
    }
    fclose($file);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['document_upload'])) {
        header('Location: file_edit.php?project=' . $_GET['project']);
        if ($_FILES['document_upload']['size'] > 500000) {
            exit;
        }

        $file = $docs_dir . basename($_FILES['document_upload']['name']);
        if (file_exists($file)) {
            exit;
        }

        move_uploaded_file($_FILES['document_upload']['tmp_name'], $file);
        exit;
    }

    if (!isset($_POST['plan_content'])) {
        unlink($project_file);
        header('Location: dashboard.php?project=' . $_GET['project']);
        exit;
    }

    if (!file_put_contents($project_file, $_POST['plan_content'], LOCK_EX)) {
        header('Location: dashboard.php?project=' . $_GET['project']);
        exit;
    }
}
?>

<!doctype html>

<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>FLOW: Project Plan</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
        href="https://fonts.googleapis.com/css2?family=Lexend:wght@100..900&family=Monomaniac+One&display=swap"
        rel="stylesheet" />
    <link href="css/style.css" rel="stylesheet" />
</head>

<body>
    <h2><?php echo htmlspecialchars($page_title); ?></h2>
    <a href="dashboard.php?project=<?php echo $_GET['project']; ?>">Go to Dashboard</a>

    <?php if (!isset($_GET['file'])): ?>
        <?php if ($docs): ?>
            <h3>Additional Documents</h3>
            <ul>
                <?php foreach ($docs as $doc): ?>
                    <?php if (str_ends_with($doc, ".txt")): ?>
                        <li>
                            <a href="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']) . '&file=' . $doc; ?>">
                                <?php echo htmlspecialchars($doc); ?>
                            </a>
                        </li>
                    <?php else: ?>
                        <li>
                            <a href="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']) . '&file=' . $doc . "&download=1"; ?>">
                                <?php echo htmlspecialchars($doc); ?>
                            </a>
                        </li>
                    <?php endif; ?>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    <?php else: ?>
        <a href="file_edit.php?project=<?php echo $_GET['project'] ?>">Go to Project Plan</a>
    <?php endif; ?>

    <form method="post">
        <textarea id="plan-content" name="plan_content" rows="10" cols="66"><?php echo file_get_contents($project_file); ?></textarea>
        <input type="submit" value="Write Plan">
    </form>
    <form method="post">
        <input type="submit" value="Delete Plan">
    </form>
    <form method="post" enctype="multipart/form-data">
        <input id="plan-document-upload" name="document_upload" type="file">
        <input type="submit" value="Upload Document">
    </form>
</body>

</html>
