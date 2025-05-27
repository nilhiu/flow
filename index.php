<?php
session_start();
?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>FLOW -- Your Tool for Project Management</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@100..900&family=Monomaniac+One&display=swap" rel="stylesheet"/>
    <link href="css/root.css" rel="stylesheet" />
    <link href="css/landing.css" rel="stylesheet" />
</head>

<body>
    <header>
        <nav>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a class="bigger-button" href="dashboard.php">Go to Dashboard</a>
            <?php else: ?>
                <a href="login.php">Log In</a>
                <a href="sign-up.php">Sign Up</a>
            <?php endif ?>
        </nav>
        <h1 data-text="FLOW">FLOW</h1>
        <p class="subheading">A Little Liquid Confidence for Your Projects</p>
        <img src="static/svg/arrow-down-circle.svg" alt="down arrow" width="48" />
    </header>

    <main>
        <section id="features-section">
            <h2>Features</h2>
            <p>Designed for Seemless <span>FLOW</span></p>
            <article>
                <h3>Effortless Project Creation &amp; Organization</h3>
                <p>Kickstart your projects in seconds. Our intuitive interface allows you to easily define project scopes, set deadlines, and categorize your work, ensuring everything is neatly organized and readily accessible from a centralized dashboard.</p>
            </article>
            <article>
                <h3>Seamless Team Collaboration &amp; Communication</h3>
                <p>Bring your team together like never before. Invite members with a click, assign roles, and foster real-time collaboration. Our integrated tools ensure everyone is on the same page, promoting clear communication and shared understanding across all project activities.</p>
            </article>
            <article>
                <h3>Central Document Management &amp; Sharing</h3>
                <p>Say goodbye to scattered files and version control headaches. Securely upload, store, and share all your critical project documents – from charters and plans to statements of work and meeting minutes. With robust access controls, you can ensure the right people have the right information, always.</p>
            </article>
        </section>
        <section id="get-started-section">
            <h2>Get Started</h2>
            <p>Get in the <span><span>FLOW</span> in Minutes</span></p>
            <article>
                <h3>Step 1. Sign Up &amp; Create Your Workspace</h3>
            </article>
            <article>
                <h3>Step 2. Invite Your Team &amp; Add Your First Project</h3>
            </article>
            <article>
                <h3>Step 3. Assign Tasks &amp; Start Collaborating</h3>
            </article>
            <article>
                <h3>Simple, right?</h3>
                <a href="sign-up.php">Join Today!</a>
            </article>
        </section>
    </main>

    <footer>
        <div>
            <div>
                <h2>FLOW</h2>
                <p>&copy; 2025 Giorgi Matiashvili. This project is free software licensed under the GNU General Public License.</p>
            </div>
            <div>
                <p>FLOW is a simple document sharing online service, created as a web project for TSU’s web development course</p>
                <p>View the source code <a href="https://github.com/nilhiu/flow" target="_blank">here</a></p>
                <p>View the license <a href="https://www.gnu.org/licenses/gpl-3.0.en.html" target="_blank">here</a></p>
                <p>Contact me at <a href="mailto:contact@nilhiu.live">contact@nilhiu.live</a></p>
            </div>
        </div>
    </footer>
</body>

</html>
