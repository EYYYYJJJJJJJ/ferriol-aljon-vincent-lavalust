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
        :root { font-family: Arial, sans-serif; color: #18243a; background: #eef3f8; }
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; background: linear-gradient(135deg, #eef3f8, #dce8f2); }
        .shell { width: min(980px, calc(100% - 32px)); margin: 0 auto; padding: 28px 0 44px; }
        .nav { display: flex; justify-content: space-between; align-items: center; gap: 16px; margin-bottom: 42px; }
        .brand { color: #18243a; font-size: 1.05rem; font-weight: 800; letter-spacing: .08em; text-decoration: none; text-transform: uppercase; }
        .links { display: flex; gap: 8px; flex-wrap: wrap; }
        .links a { color: #315b83; border: 1px solid #b7cadc; border-radius: 999px; padding: 9px 14px; text-decoration: none; font-size: .9rem; }
        .links a:hover, .links a.active { background: #315b83; color: #fff; }
        .hero { display: grid; grid-template-columns: 1.1fr .9fr; gap: 28px; align-items: stretch; }
        .intro, .card { border-radius: 22px; box-shadow: 0 18px 45px rgba(43, 76, 108, .13); }
        .intro { padding: clamp(28px, 6vw, 64px); color: #fff; background: #18243a; }
        .eyebrow { color: #f6b26b; font-size: .78rem; font-weight: 800; letter-spacing: .16em; text-transform: uppercase; }
        h1 { margin: 16px 0 14px; font-size: clamp(2rem, 5vw, 4.4rem); line-height: .98; }
        .intro p { color: #d8e5f0; line-height: 1.75; }
        .card { padding: 28px; background: rgba(255, 255, 255, .92); }
        .card h2 { margin: 0 0 20px; font-size: 1.3rem; }
        .details { display: grid; gap: 0; margin: 0; }
        .details div { display: grid; grid-template-columns: 120px 1fr; gap: 12px; padding: 13px 0; border-bottom: 1px solid #dce5ed; }
        dt { color: #6b7d90; font-size: .78rem; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; }
        dd { margin: 0; color: #18243a; font-weight: 700; }
        .notice { margin: 0 0 20px; padding: 12px 14px; border-left: 4px solid #e07a3f; background: #fff3e8; color: #7a401f; line-height: 1.5; }
        @media (max-width: 720px) { .nav, .hero { grid-template-columns: 1fr; } .nav { align-items: flex-start; flex-direction: column; } .hero { display: grid; } .details div { grid-template-columns: 1fr; gap: 4px; } }
    </style>
</head>
<body>
<main class="shell">
    <nav class="nav" aria-label="Student navigation">
        <a class="brand" href="<?= $home_url ?>">Ferriol / Student Hub</a>
        <div class="links">
            <a class="active" href="<?= $home_url ?>">Home</a>
            <a href="<?= $profile_url ?>">Student Profile</a>
        </div>
    </nav>

    <section class="hero">
        <div class="intro">
            <div class="eyebrow">LavaLust Lab 3</div>
            <h1>Student Information</h1>
            <p>A personalized student page built with LavaLust routing, controllers, views, and middleware.</p>
        </div>

        <div class="card">
            <?php if (!empty($notice)): ?>
                <p class="notice"><?= $escape($notice) ?></p>
            <?php endif; ?>
            <h2>Student Details</h2>
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
