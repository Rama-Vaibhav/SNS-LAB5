<?php
// Vulnerable App – Dashboard / Post-Login Page
// Lab 5 | Group 6
session_start();

if (!isset($_SESSION['login_status']) || $_SESSION['login_status'] !== 'success') {
  header("Location: index.php");
  exit();
}

$all_rows = $_SESSION['all_rows'] ?? [];
$last_query = $_SESSION['last_query'] ?? '';
$login_user = $_SESSION['login_user'] ?? 'Unknown';

// Collect which attack flags are active (makes template logic cleaner)
$is_auth_bypass = !empty($_SESSION['auth_bypass']);
$is_union = !empty($_SESSION['union_injection']);
$is_blind_true = !empty($_SESSION['blind_true']);
$is_blind_false = !empty($_SESSION['blind_false']);
$is_db_modification = !empty($_SESSION['db_modification']);

$any_attack = $is_auth_bypass || $is_union || $is_blind_true || $is_blind_false || $is_db_modification;
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <title>Dashboard | Vulnerable App</title>
  <link rel="stylesheet" href="style.css" />
  <style>
    /* ── Data table ──────────────────────────────────────────────────── */
    table {
      width: 100%;
      border-collapse: collapse;
      font-family: var(--mono);
      font-size: 0.8rem;
      margin-top: 14px;
    }

    th,
    td {
      border: 1px solid var(--border);
      padding: 8px 12px;
      text-align: left;
    }

    th {
      background: rgba(231, 76, 60, 0.15);
      color: var(--accent);
      letter-spacing: .07em;
    }

    td {
      color: var(--text);
    }

    /* ── Executed-query box ──────────────────────────────────────────── */
    .query-box {
      background: var(--input-bg);
      border: 1px solid var(--accent-dim);
      border-radius: 4px;
      padding: 12px;
      font-family: var(--mono);
      font-size: 0.78rem;
      color: #f0a500;
      word-break: break-all;
      margin-top: 14px;
    }

    /* ── Shared attack banner base ───────────────────────────────────── */
    .attack-banner {
      margin: 18px 0 0 0;
      font-size: 1.05em;
      padding: 16px;
      border-radius: 7px;
      border-width: 2px;
      border-style: solid;
    }

    .attack-banner .banner-icon {
      font-size: 1.5em;
    }

    .attack-banner .banner-title {
      font-weight: bold;
      font-size: 1.05em;
    }

    .attack-banner .banner-body {
      font-size: 1em;
      margin-top: 4px;
    }

    /* ── Per-attack colour themes ────────────────────────────────────── */
    .banner-auth {
      color: #00695c;
      background: #e0f2f1;
      border-color: #26a69a;
    }

    .banner-union {
      color: #0d47a1;
      background: #e3f2fd;
      border-color: #64b5f6;
    }

    .banner-blind-t {
      color: #6d4c41;
      background: #efebe9;
      border-color: #a1887f;
    }

    .banner-blind-f {
      color: #b71c1c;
      background: #ffebee;
      border-color: #ef9a9a;
    }

    .banner-dbmod {
      color: #4a148c;
      background: #f3e5f5;
      border-color: #ab47bc;
    }

    /* ── Union row-count notice ──────────────────────────────────────── */
    .union-notice {
      margin-top: 18px;
      font-size: .8rem;
      color: #f0a500;
      font-family: var(--mono);
    }

    /* ── Logout button ───────────────────────────────────────────────── */
    .logout-btn {
      display: inline-block;
      margin-top: 20px;
      background: transparent;
      border: 1px solid var(--accent);
      color: var(--accent);
      padding: 8px 20px;
      border-radius: 4px;
      text-decoration: none;
      font-family: var(--mono);
      font-size: 0.8rem;
    }
  </style>
</head>

<body>
  <div class="warning-banner">⚠ VULNERABLE APPLICATION — EDUCATIONAL USE ONLY ⚠</div>

  <div class="container" style="max-width:700px; padding-top:60px;">
    <span class="badge">&#x26A0; Insecure App — Dashboard</span>

    <h1>Welcome, <?= htmlspecialchars($login_user) ?></h1>
    <p class="subtitle">// Login succeeded — attack details and DB rows shown below</p>

    <!-- ================================================================
       ATTACK BANNERS
       Each banner appears only when its corresponding session flag is set.
       Flags are set by authentication.php based on input pattern matching.
       ================================================================ -->

    <?php if ($is_auth_bypass): ?>
      <!-- 6.1 Authentication Bypass -->
      <div class="attack-banner banner-auth">
        <span class="banner-icon">🔓</span>
        <span class="banner-title" style="color:#00897b;"> Authentication Bypass Detected!</span>
        <div class="banner-body" style="color:#00897b;">
          You bypassed the login form using a <b>tautology-based SQL Injection</b>
          (e.g. <code>' OR '1'='1</code>).<br>
          The injected condition made the WHERE clause always evaluate to <b>TRUE</b>,
          returning the first row in the table without a valid password.
          <br><br>
          <b>Risk:</b> Any account — including admin — can be accessed without credentials.
        </div>
      </div>
    <?php endif; ?>

    <?php if ($is_union): ?>
      <!-- 6.2 Union-Based Injection -->
      <div class="attack-banner banner-union">
        <span class="banner-icon">🧩</span>
        <span class="banner-title" style="color:#1565c0;"> Union-Based SQL Injection Detected!</span>
        <div class="banner-body" style="color:#1976d2;">
          A <b>UNION SELECT</b> payload was appended to the query.<br>
          This technique lets an attacker bolt a second <code>SELECT</code> onto the
          original query to extract data from <em>any table in the database</em> —
          including tables the login form was never meant to touch.<br><br>
          <b>Risk:</b> Full database enumeration — usernames, passwords, emails, etc.
        </div>
      </div>
    <?php endif; ?>

    <?php if ($is_blind_true): ?>
      <!-- 6.3 Blind SQLi – True condition -->
      <div class="attack-banner banner-blind-t">
        <span class="banner-icon">👁️</span>
        <span class="banner-title" style="color:#4e342e;"> Blind SQL Injection — True Condition</span>
        <div class="banner-body" style="color:#4e342e;">
          The injected condition evaluated to <b>TRUE</b> (e.g. <code>AND 1=1</code>).<br>
          In blind SQLi the attacker sends boolean payloads and infers database
          structure from whether login succeeds or fails — no data is returned directly.<br><br>
          <b>Result:</b> Login succeeded ✔ — consistent with a TRUE condition.
        </div>
      </div>
    <?php endif; ?>

    <?php if ($is_blind_false): ?>
      <!-- 6.3 Blind SQLi – False condition -->
      <div class="attack-banner banner-blind-f">
        <span class="banner-icon">🚫</span>
        <span class="banner-title" style="color:#c62828;"> Blind SQL Injection — False Condition</span>
        <div class="banner-body" style="color:#c62828;">
          The injected condition evaluated to <b>FALSE</b> (e.g. <code>AND 1=2</code>).<br>
          Normally this would cause login to fail — showing the dashboard here is
          intentional so you can compare the TRUE vs FALSE response side-by-side.<br><br>
          <b>Takeaway:</b> By toggling conditions an attacker maps out database structure
          one bit at a time, entirely from login pass/fail signals.
        </div>
      </div>
    <?php endif; ?>

    <?php if ($is_db_modification): ?>
      <!-- 6.4 Database Modification Attack -->
      <div class="attack-banner banner-dbmod">
        <span class="banner-icon">💣</span>
        <span class="banner-title" style="color:#4a148c;"> Database Modification Attack Detected!</span>
        <div class="banner-body" style="color:#4a148c;">
          A data-manipulation payload was injected
          (<code>INSERT</code> / <code>UPDATE</code> / <code>DELETE</code> / <code>DROP</code>).<br>
          Although PHP's <code>mysqli_query()</code> does <em>not</em> support stacked queries
          natively, this demonstrates the intent of the attack.<br><br>
          <b>In a vulnerable stack that allows stacked queries</b> (e.g. PDO with
          <code>exec()</code> or some ORMs), the injected statement would execute and
          permanently alter or destroy data.<br><br>
          <b>Risk:</b> New admin accounts created, passwords changed, entire tables dropped.
        </div>
      </div>
    <?php endif; ?>

    <?php if (!$any_attack && !empty($last_query)): ?>
      <!-- Fallback: catch-all for suspicious input that didn't match a named pattern -->
      <?php
      $q_lower = strtolower($last_query);
      $looks_suspicious = strpos($q_lower, "'") !== false && (
        strpos($q_lower, "--") !== false ||
        strpos($q_lower, "or ") !== false ||
        strpos($q_lower, "and ") !== false
      );
      ?>
      <?php if ($looks_suspicious): ?>
        <div class="attack-banner" style="color:#7b1fa2; background:#f3e5f5; border-color:#ce93d8;">
          <span class="banner-icon">⚠️</span>
          <span class="banner-title" style="color:#6a1b9a;"> SQL Injection Attempt Detected!</span>
          <div class="banner-body" style="color:#6a1b9a;">
            Suspicious characters were found in the input (quotes, logical operators, or comment
            sequences). This may be an unclassified injection attempt.
          </div>
        </div>
      <?php endif; ?>
    <?php endif; ?>

    <!-- ================================================================
       QUERY & DATA PANEL
       ================================================================ -->
    <div class="card">
      <p style="font-size:.82rem; color:var(--text-dim); margin-bottom:8px;">
        <b style="color:var(--accent);">Executed SQL Query:</b>
      </p>
      <div class="query-box"><?= htmlspecialchars($last_query) ?></div>

      <?php if (count($all_rows) > 1): ?>
        <p class="union-notice">
          ⚡ UNION-based injection: <?= count($all_rows) ?> rows returned from database!
        </p>
      <?php endif; ?>

      <?php if (!empty($all_rows)): ?>
        <!-- Table of rows returned by the (possibly injected) query -->
        <table>
          <tr>
            <?php foreach (array_keys($all_rows[0]) as $col): ?>
              <th><?= htmlspecialchars($col) ?></th>
            <?php endforeach; ?>
          </tr>
          <?php foreach ($all_rows as $row): ?>
            <tr>
              <?php foreach ($row as $val): ?>
                <td><?= htmlspecialchars($val ?? '') ?></td>
              <?php endforeach; ?>
            </tr>
          <?php endforeach; ?>
        </table>
      <?php elseif ($any_attack): ?>
        <!-- Explain why no rows are shown for attack paths -->
        <p style="margin-top:14px; font-size:.8rem; color:var(--text-dim); font-family:var(--mono);">
          ℹ️ No rows returned — the injected query did not produce a result set
          (expected for UNION column-mismatch, blind, and modification attacks).
        </p>
      <?php endif; ?>

      <a class="logout-btn" href="logout.php">← Logout</a>
    </div>
  </div>
</body>

</html>