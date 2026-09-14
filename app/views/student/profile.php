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
        @import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;600;700&family=Space+Grotesk:wght@600;700&display=swap');
        :root { font-family: 'DM Sans', sans-serif; color: #f7f7ff; background: #090914; }
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; background: radial-gradient(circle at 85% 10%, rgba(236,72,153,.25), transparent 28%), radial-gradient(circle at 10% 90%, rgba(139,92,246,.3), transparent 32%), #090914; }
        .shell { width: min(1100px, calc(100% - 40px)); margin: 0 auto; padding: 30px 0 54px; }
        .nav { display: flex; justify-content: space-between; align-items: center; gap: 24px; margin-bottom: 52px; }
        .brand { display: flex; align-items: center; gap: 12px; color: #fff; font-family: 'Space Grotesk', sans-serif; font-size: .9rem; font-weight: 700; letter-spacing: .13em; text-decoration: none; text-transform: uppercase; }
        .brand::before { content: 'AF'; display: grid; width: 40px; height: 40px; place-items: center; border-radius: 12px; background: linear-gradient(135deg, #ec4899, #8b5cf6); color: #fff; letter-spacing: 0; box-shadow: 0 0 30px rgba(236,72,153,.35); }
        .links { display: flex; gap: 7px; padding: 5px; border: 1px solid rgba(255,255,255,.1); border-radius: 14px; background: rgba(255,255,255,.05); }
        .links a { color: #aaaabd; border-radius: 10px; padding: 10px 16px; text-decoration: none; font-size: .88rem; font-weight: 700; }
        .links a:hover, .links a.active { background: #fff; color: #10101c; }
        .profile { position: relative; display: grid; grid-template-columns: 310px 1fr; overflow: hidden; border: 1px solid rgba(255,255,255,.12); border-radius: 30px; background: rgba(18,17,38,.78); box-shadow: 0 36px 100px rgba(0,0,0,.42); backdrop-filter: blur(22px); }
        .sidebar { position: relative; display: flex; min-height: 610px; flex-direction: column; justify-content: space-between; padding: 38px; overflow: hidden; background: linear-gradient(155deg, #ec4899, #7c3aed 58%, #312e81); }
        .sidebar::after { content: ''; position: absolute; width: 330px; height: 330px; right: -190px; bottom: 40px; border: 55px solid rgba(255,255,255,.1); border-radius: 50%; }
        .monogram { position: relative; z-index: 1; display: grid; width: 112px; height: 112px; place-items: center; border: 1px solid rgba(255,255,255,.35); border-radius: 30px; background: rgba(255,255,255,.12); font-family: 'Space Grotesk', sans-serif; font-size: 2.7rem; font-weight: 700; box-shadow: inset 0 1px 0 rgba(255,255,255,.25); }
        .identity { position: relative; z-index: 1; }
        .identity strong { display: block; font-family: 'Space Grotesk', sans-serif; font-size: 1.35rem; line-height: 1.15; }
        .identity small { display: block; margin-top: 12px; color: rgba(255,255,255,.72); line-height: 1.6; }
        .verified { position: relative; z-index: 1; display: inline-flex; align-items: center; gap: 8px; width: fit-content; margin-top: 22px; padding: 9px 12px; border: 1px solid rgba(255,255,255,.23); border-radius: 999px; background: rgba(0,0,0,.12); font-size: .72rem; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; }
        .verified::before { content: '✓'; display: grid; width: 18px; height: 18px; place-items: center; border-radius: 50%; background: #fff; color: #7c3aed; }
        .content { padding: clamp(36px, 6vw, 72px); }
        .eyebrow { color: #f9a8d4; font-size: .76rem; font-weight: 700; letter-spacing: .17em; text-transform: uppercase; }
        h1 { margin: 18px 0 14px; font-family: 'Space Grotesk', sans-serif; font-size: clamp(3rem, 6vw, 5.7rem); letter-spacing: -.065em; line-height: .9; }
        .lead { max-width: 610px; color: #aaaabd; line-height: 1.75; }
        .details { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin: 34px 0 0; }
        .details div { padding: 18px; border: 1px solid rgba(255,255,255,.08); border-radius: 16px; background: rgba(255,255,255,.035); }
        .details div:first-child, .details div:nth-child(2) { border-color: rgba(236,72,153,.22); background: linear-gradient(135deg, rgba(236,72,153,.11), rgba(139,92,246,.08)); }
        dt { color: #74748c; font-size: .68rem; font-weight: 700; letter-spacing: .13em; text-transform: uppercase; }
        dd { margin: 7px 0 0; color: #f7f7ff; font-weight: 700; line-height: 1.4; }
        @media (max-width: 760px) { .nav { align-items: flex-start; flex-direction: column; } .profile { grid-template-columns: 1fr; } .sidebar { min-height: 330px; } .details { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
<main class="shell">
    <nav class="nav" aria-label="Student navigation">
        <a class="brand" href="<?= $home_url ?>">Ferriol / Student Hub</a>
        <div class="links">
            <a href="<?= $home_url ?>">Home</a>
            <a class="active" href="<?= $profile_url ?>">Student Profile</a>
        </div>
    </nav>

    <section class="profile">
        <aside class="sidebar">
            <div class="monogram">AF</div>
            <div class="identity"><strong>Aljon Vincent<br>E. Ferriol</strong><small>Web Systems &amp; Technologies<br>Laboratory 03</small><span class="verified">Middleware verified</span></div>
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
