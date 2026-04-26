<?php
declare(strict_types=1);

require_once __DIR__ . '/config/db.php';

$dbName = (string) $pdo->query('SELECT DATABASE()')->fetchColumn();

$counts = [
    'admin' => (int) $pdo->query('SELECT COUNT(*) FROM admin')->fetchColumn(),
    'teachers' => (int) $pdo->query('SELECT COUNT(*) FROM teachers')->fetchColumn(),
    'students' => (int) $pdo->query('SELECT COUNT(*) FROM students')->fetchColumn(),
];

$schemaOk = ($counts['admin'] >= 1 && $counts['teachers'] >= 1 && $counts['students'] >= 1);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Status</title>
    <style>
        :root {
            --bg: #f5f7fa;
            --card: #ffffff;
            --text: #111827;
            --muted: #4b5563;
            --ok: #0f766e;
            --warn: #b45309;
            --border: #d1d5db;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f8fafc 0%, #eef2ff 100%);
            color: var(--text);
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 24px;
        }

        .card {
            width: min(760px, 100%);
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 14px;
            box-shadow: 0 10px 25px rgba(17, 24, 39, 0.08);
            padding: 22px;
        }

        h1 {
            margin: 0 0 10px;
            font-size: 24px;
        }

        p {
            margin: 0 0 16px;
            color: var(--muted);
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px;
            margin-bottom: 16px;
        }

        .item {
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 12px;
            background: #fbfdff;
        }

        .k {
            font-size: 12px;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: 0.04em;
            margin-bottom: 6px;
        }

        .v {
            font-size: 20px;
            font-weight: 700;
        }

        .status {
            margin-top: 6px;
            border-radius: 10px;
            padding: 10px 12px;
            border: 1px solid var(--border);
            font-weight: 600;
        }

        .ok {
            color: var(--ok);
            background: #ecfeff;
            border-color: #99f6e4;
        }

        .warn {
            color: var(--warn);
            background: #fffbeb;
            border-color: #fde68a;
        }

        .links {
            margin-top: 14px;
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        a {
            text-decoration: none;
            color: #1d4ed8;
            border: 1px solid #bfdbfe;
            background: #eff6ff;
            padding: 8px 10px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 14px;
        }

        @media (max-width: 640px) {
            .grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <main class="card">
        <h1>System Status</h1>
        <p>Quick diagnostic page to validate database connection and login prerequisites.</p>

        <section class="grid">
            <div class="item">
                <div class="k">Connected Database</div>
                <div class="v"><?php echo htmlspecialchars($dbName, ENT_QUOTES, 'UTF-8'); ?></div>
            </div>
            <div class="item">
                <div class="k">Admin Accounts</div>
                <div class="v"><?php echo $counts['admin']; ?></div>
            </div>
            <div class="item">
                <div class="k">Teacher Accounts</div>
                <div class="v"><?php echo $counts['teachers']; ?></div>
            </div>
            <div class="item">
                <div class="k">Student Accounts</div>
                <div class="v"><?php echo $counts['students']; ?></div>
            </div>
        </section>

        <div class="status <?php echo $schemaOk ? 'ok' : 'warn'; ?>">
            <?php if ($schemaOk): ?>
                Ready: all required account types are present.
            <?php else: ?>
                Not ready: run create_user.php to populate missing account types.
            <?php endif; ?>
        </div>

        <div class="links">
            <a href="create_user.php">Seed Test Users</a>
            <a href="auth/login.php">Go To Login</a>
            <a href="index.php">Go To Home</a>
        </div>
    </main>
</body>
</html>
