<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

$escape = static function ($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};

$home_url = $escape(site_url('student'));
$profile_url = $escape(site_url('student/profile'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $escape($title) ?></title>
    <style>
        :root { font-family: Arial, sans-serif; color: #18243a; background: #f6f3ee; }
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; background: radial-gradient(circle at top right, #ffe5c7, transparent 34%), #f6f3ee; }
        .shell { width: min(900px, calc(100% - 32px)); margin: 0 auto; padding: 28px 0 44px; }
        .nav { display: flex; justify-content: space-between; align-items: center; gap: 16px; margin-bottom: 44px; }
        .brand { color: #18243a; font-size: 1.05rem; font-weight: 800; letter-spacing: .08em; text-decoration: none; text-transform: uppercase; }
        .links { display: flex; gap: 8px; flex-wrap: wrap; }
        .links a { color: #8e512d; border: 1px solid #ddb99a; border-radius: 999px; padding: 9px 14px; text-decoration: none; font-size: .9rem; }
        .links a:hover, .links a.active { background: #8e512d; color: #fff; }
        .profile { display: grid; grid-template-columns: 190px 1fr; overflow: hidden; border-radius: 24px; box-shadow: 0 20px 55px rgba(129, 83, 49, .15); background: #fffdf9; }
        .sidebar { display: flex; min-height: 480px; flex-direction: column; justify-content: space-between; padding: 28px; color: #fff; background: #8e512d; }
        .monogram { display: grid; width: 84px; height: 84px; place-items: center; border: 1px solid rgba(255,255,255,.55); border-radius: 50%; font-size: 2rem; font-weight: 800; }
        .sidebar small { color: #ffe2cc; line-height: 1.7; }
        .content { padding: clamp(28px, 6vw, 64px); }
        .eyebrow { color: #c16f3d; font-size: .78rem; font-weight: 800; letter-spacing: .16em; text-transform: uppercase; }
        h1 { margin: 14px 0 10px; color: #18243a; font-size: clamp(2rem, 5vw, 4rem); line-height: 1; }
        .lead { color: #667085; line-height: 1.7; }
        .details { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin: 30px 0 0; }
        .details div { padding: 16px; border: 1px solid #eadfd4; border-radius: 12px; background: #fff8f0; }
        dt { color: #9a755d; font-size: .75rem; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; }
        dd { margin: 7px 0 0; color: #18243a; font-weight: 700; line-height: 1.4; }
        @media (max-width: 680px) { .nav { align-items: flex-start; flex-direction: column; } .profile { grid-template-columns: 1fr; } .sidebar { min-height: 0; gap: 28px; } .details { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
<main class="shell">
    <nav class="nav" aria-label="Student navigation">
        <a class="brand" href="<?= $home_url ?>">Evora / Student Hub</a>
        <div class="links">
            <a href="<?= $home_url ?>">Home</a>
            <a class="active" href="<?= $profile_url ?>">Student Profile</a>
        </div>
    </nav>

    <section class="profile">
        <aside class="sidebar">
            <div class="monogram">JE</div>
            <small>Protected by<br>StudentMiddleware</small>
        </aside>

        <div class="content">
            <div class="eyebrow">Verified student profile</div>
            <h1>Student Profile</h1>
            <p class="lead">This page is available after the StudentMiddleware confirms the active student session.</p>
            <dl class="details">
                <div><dt>Student ID</dt><dd><?= $escape($student['student_id']) ?></dd></div>
                <div><dt>Name</dt><dd><?= $escape($student['name']) ?></dd></div>
                <div><dt>Course</dt><dd><?= $escape($student['course']) ?></dd></div>
                <div><dt>Year Level</dt><dd><?= $escape($student['year_level']) ?></dd></div>
                <div><dt>Section</dt><dd><?= $escape($student['section']) ?></dd></div>
                <div><dt>Email</dt><dd><?= $escape($student['email']) ?></dd></div>
            </dl>
        </div>
    </section>
</main>
</body>
</html>
