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
    die('Database connection failed: ' . $e->getMessage());
}

$data_dir = __DIR__ . '/data/';
$project_file = $data_dir . $_GET['project'] . '.txt';
$docs_dir = __DIR__ . '/data/' . $_GET['project'] . '/';
if (!is_dir($docs_dir)) {
    mkdir($docs_dir);
}

$docs = scandir($docs_dir);
if ($docs) {
    $docs = array_slice($docs, 2);
}

$page_title = 'Project Plan';

if (isset($_GET['file'])) {
    $filepath = realpath($docs_dir . basename($_GET['file']));
    if (!$filepath && !is_file($filepath)) {
        header('Location: document.php?project=' . $_GET['project']);
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
}

if (!is_dir($data_dir)) {
    if (!mkdir($data_dir, 0770, true)) {
        error_log('Failed to create data directory');
        header('Location: dashboard.php?project=' . $_GET['project']);
        exit;
    }
}

if (!is_file($project_file)) {
    $file = fopen($project_file, 'w');
    if (!$file) {
        error_log('Failed to create project plan file');
        header('Location: dashboard.php?project=' . $_GET['project']);
        exit;
    }
    fclose($file);
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
    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@100..900&family=Monomaniac+One&family=Roboto+Mono:ital,wght@0,100..700;1,100..700&display=swap" rel="stylesheet">
    <link href="assets/css/root.css" rel="stylesheet" />
    <link href="assets/css/dashboard.css" rel="stylesheet" />
</head>

<body>
    <aside>
        <header class="profile-box">
            <div class="avatar-circle"></div>
            <div class="profile-info">
                <p><?php echo $_SESSION['first_name']; ?></p>
                <p><?php echo $_SESSION['last_name']; ?></p>
            </div>
        </header>

        <nav>
            <div class="action-modals">
                <ul>
                    <li>
                        <button id="upload-document-modal-btn" onclick="openUploadDocumentModal()">Upload Document</button>
                    </li>
                </ul>
            </div>
            <div class="action-links">
                <ul>
                    <li>
                        <a href="document.php?project=<?php echo $_GET['project']; ?>">
                            Project Plan
                        </a>
                    </li>
                    <li>
                        <a href="dashboard.php?project=<?php echo $_GET['project']; ?>">
                            Dashboard
                        </a>
                    </li>
                </ul>
            </div>
            <div class="project-list">
                <h3>Documents</h3>
                <ul>
                    <?php foreach ($docs as $doc): ?>
                        <?php if (str_ends_with($doc, '.txt')): ?>
                            <li>
                                <a href="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']) . '&file=' . $doc; ?>">
                                    <?php echo htmlspecialchars($doc); ?>
                                </a>
                            </li>
                        <?php else: ?>
                            <li>
                                <a href="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']) . '&file=' . $doc . '&download=1'; ?>">
                                    <?php echo htmlspecialchars($doc); ?>
                                </a>
                            </li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </ul>
            </div>
        </nav>

        <footer>
            <h4 class="logo-small"><a href="/">FLOW</a></h4>
            <p>&copy; 2025 Giorgi Matiashvili. This project is free software licensed under the GNU General Public License.</p>
        </footer>
    </aside>

    <div id="burger-menu-wrapper">
        <button id="burger-menu">
            <div>
                <div class="burger-menu-line"></div>
                <div class="burger-menu-line"></div>
                <div class="burger-menu-line"></div>
            </div>
        </button>
    </div>

    <main id="document">
        <section>
            <h2><?php echo htmlspecialchars($page_title); ?></h2>
            <form action="save_document.php" method="post">
                <input type="hidden" value="<?php echo $_GET['project'] ?>" name="project_id">
                <?php if (isset($_GET['file'])): ?>
                    <input type="hidden" value="<?php echo $_GET['file'] ?>" name="file">
                <?php endif ?>
                <textarea id="plan-content" name="content"><?php echo file_get_contents($project_file); ?></textarea>
                <div>
                    <input class="submit-button" type="submit" value="Save">
                    <input formaction="delete_document.php" class="submit-button" type="submit" value="Delete">
                </div>
            </form>
        </section>
    </main>

    <div class="modal" id="upload-document-modal">
        <form action="upload_document.php" method="post" enctype="multipart/form-data">
            <input type="hidden" value="<?php echo $_GET['project'] ?>" name="project_id">
            <label for="document-upload">Upload Document:</label>
            <input id="document-upload" name="document" type="file">
            <input class="submit-button" type="submit" value="Upload">
        </form>
    </div>

    <script src="assets/js/modal.js"></script>
    <script src="assets/js/burger-menu.js"></script>
</body>

</html>
