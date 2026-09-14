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
        body { margin: 0; min-height: 100vh; overflow-x: hidden; background: #090914; }
        body::before { content: ''; position: fixed; inset: 0; z-index: -2; background: radial-gradient(circle at 8% 12%, rgba(139,92,246,.38), transparent 30%), radial-gradient(circle at 92% 85%, rgba(34,211,238,.25), transparent 32%), linear-gradient(145deg, #090914 20%, #121126 100%); }
        body::after { content: ''; position: fixed; inset: 0; z-index: -1; opacity: .2; background-image: linear-gradient(rgba(255,255,255,.08) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,.08) 1px, transparent 1px); background-size: 44px 44px; mask-image: linear-gradient(to bottom, #000, transparent 75%); }
        .shell { width: min(1180px, calc(100% - 40px)); margin: 0 auto; padding: 30px 0 54px; }
        .nav { display: flex; justify-content: space-between; align-items: center; gap: 24px; margin-bottom: 68px; }
        .brand { display: flex; align-items: center; gap: 12px; color: #fff; font-family: 'Space Grotesk', sans-serif; font-size: .9rem; font-weight: 700; letter-spacing: .13em; text-decoration: none; text-transform: uppercase; }
        .brand::before { content: 'AF'; display: grid; width: 40px; height: 40px; place-items: center; border-radius: 12px; background: linear-gradient(135deg, #8b5cf6, #22d3ee); color: #080812; letter-spacing: 0; box-shadow: 0 0 30px rgba(139,92,246,.45); }
        .links { display: flex; gap: 7px; padding: 5px; border: 1px solid rgba(255,255,255,.1); border-radius: 14px; background: rgba(255,255,255,.05); backdrop-filter: blur(16px); }
        .links a { color: #a9a9bb; border-radius: 10px; padding: 10px 16px; text-decoration: none; font-size: .88rem; font-weight: 700; transition: .2s ease; }
        .links a:hover, .links a.active { background: #fff; color: #10101c; }
        .hero { display: grid; grid-template-columns: 1.08fr .92fr; gap: 24px; align-items: stretch; }
        .intro, .card { position: relative; overflow: hidden; border: 1px solid rgba(255,255,255,.11); border-radius: 28px; background: rgba(18,17,38,.72); box-shadow: 0 30px 80px rgba(0,0,0,.34); backdrop-filter: blur(20px); }
        .intro { min-height: 560px; padding: clamp(34px, 6vw, 72px); }
        .intro::after { content: '03'; position: absolute; right: -16px; bottom: -75px; color: rgba(255,255,255,.045); font-family: 'Space Grotesk', sans-serif; font-size: 18rem; font-weight: 700; line-height: 1; }
        .eyebrow { display: inline-flex; align-items: center; gap: 9px; color: #67e8f9; font-size: .78rem; font-weight: 700; letter-spacing: .16em; text-transform: uppercase; }
        .eyebrow::before { content: ''; width: 28px; height: 2px; background: #67e8f9; }
        h1 { max-width: 650px; margin: 26px 0 22px; font-family: 'Space Grotesk', sans-serif; font-size: clamp(3.2rem, 7vw, 6.7rem); letter-spacing: -.07em; line-height: .86; }
        h1 span { color: transparent; -webkit-text-stroke: 1px rgba(255,255,255,.68); }
        .intro p { max-width: 500px; color: #aaaabd; font-size: 1.02rem; line-height: 1.75; }
        .stack { display: flex; gap: 9px; margin-top: 44px; flex-wrap: wrap; }
        .stack span { padding: 8px 12px; border: 1px solid rgba(103,232,249,.2); border-radius: 999px; color: #c4f8ff; background: rgba(34,211,238,.08); font-size: .76rem; font-weight: 700; letter-spacing: .06em; }
        .card { padding: 34px; }
        .card-header { display: flex; justify-content: space-between; align-items: center; gap: 16px; margin-bottom: 24px; }
        .card h2 { margin: 0; font-family: 'Space Grotesk', sans-serif; font-size: 1.35rem; }
        .status { display: inline-flex; align-items: center; gap: 7px; color: #86efac; font-size: .75rem; font-weight: 700; text-transform: uppercase; }
        .status::before { content: ''; width: 8px; height: 8px; border-radius: 50%; background: #4ade80; box-shadow: 0 0 14px #4ade80; }
        .details { display: grid; gap: 11px; margin: 0; }
        .details div { padding: 17px 18px; border: 1px solid rgba(255,255,255,.08); border-radius: 15px; background: rgba(255,255,255,.035); transition: transform .2s, border-color .2s; }
        .details div:hover { transform: translateX(4px); border-color: rgba(103,232,249,.35); }
        dt { color: #77778e; font-size: .68rem; font-weight: 700; letter-spacing: .13em; text-transform: uppercase; }
        dd { margin: 6px 0 0; color: #f7f7ff; font-weight: 700; }
        .notice { margin: 0 0 20px; padding: 12px 14px; border: 1px solid rgba(34,211,238,.28); border-radius: 12px; background: rgba(34,211,238,.08); color: #a5f3fc; line-height: 1.5; }
        @media (max-width: 780px) { .nav { align-items: flex-start; flex-direction: column; margin-bottom: 36px; } .hero { grid-template-columns: 1fr; } .intro { min-height: 440px; } h1 { font-size: clamp(3rem, 16vw, 5.5rem); } }
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
            <h1>Student<br><span>Information</span></h1>
            <p>A personalized student page built with LavaLust routing, controllers, views, and middleware.</p>
            <div class="stack"><span>ROUTING</span><span>CONTROLLER</span><span>VIEWS</span><span>MIDDLEWARE</span></div>
        </div>

        <div class="card">
            <?php if (!empty($notice)): ?>
                <p class="notice"><?= $escape($notice) ?></p>
            <?php endif; ?>
            <div class="card-header"><h2>Student Details</h2><span class="status">Active</span></div>
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
