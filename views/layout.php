<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= isset($page_title) ? h($page_title) . ' — ' : '' ?>Egyptian Transfers</title>

  <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

  <style>
    :root {
      --primary  : #1B3A6B;
      --primary-d: #142d52;
      --accent   : #C0392B;
      --accent-d : #992d22;
    }

    body {
      background: #f0f4f8;
      font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
      min-height: 100vh;
    }

    /* ── Navbar ── */
    .navbar-main {
      background: var(--primary);
      box-shadow: 0 2px 12px rgba(0,0,0,.25);
    }
    .navbar-brand-text {
      font-weight: 700;
      font-size: 1.15rem;
      letter-spacing: .4px;
      color: #fff;
      text-decoration: none;
    }
    .navbar-brand-text:hover { color: #cde; }

    /* ── Cards ── */
    .card {
      border: none;
      border-radius: 12px;
      box-shadow: 0 2px 14px rgba(0,0,0,.08);
    }
    .card-header-primary {
      background: var(--primary);
      color: #fff;
      border-radius: 12px 12px 0 0;
      padding: .85rem 1.25rem;
      font-weight: 600;
    }

    /* ── Tables ── */
    .table thead th {
      background: var(--primary);
      color: #fff;
      border-color: var(--primary-d);
      font-size: .8rem;
      text-transform: uppercase;
      letter-spacing: .5px;
      font-weight: 600;
      vertical-align: middle;
    }
    .table-hover tbody tr:hover { background: rgba(27,58,107,.06); }

    /* ── Stat cards ── */
    .stat-card {
      background: #fff;
      border-radius: 12px;
      padding: 1.1rem 1.25rem;
      text-align: center;
      box-shadow: 0 2px 14px rgba(0,0,0,.08);
    }
    .stat-label {
      font-size: .72rem;
      color: #6c757d;
      text-transform: uppercase;
      letter-spacing: .6px;
      margin-bottom: .25rem;
    }
    .stat-value {
      font-size: 1.9rem;
      font-weight: 700;
      line-height: 1;
    }

    /* ── Total row ── */
    .total-row td {
      font-weight: 700;
      color: var(--accent) !important;
      background: #fff4f4 !important;
    }

    /* ── Buttons ── */
    .btn-primary   { background: var(--primary);  border-color: var(--primary);  }
    .btn-primary:hover { background: var(--primary-d); border-color: var(--primary-d); }
    .btn-danger    { background: var(--accent);   border-color: var(--accent);   }
    .btn-danger:hover  { background: var(--accent-d);  border-color: var(--accent-d);  }

    /* ── Modal headers ── */
    .modal-header-primary { background: var(--primary); color: #fff; }
    .modal-header-danger  { background: var(--accent);  color: #fff; }
    .modal-header-primary .btn-close,
    .modal-header-danger  .btn-close { filter: invert(1) grayscale(1); }

    /* ── Flash messages ── */
    .flash-wrap {
      position: fixed;
      top: 70px;
      right: 18px;
      z-index: 9999;
      min-width: 300px;
      max-width: 420px;
    }

    /* ── Vessel badge ── */
    .badge-vessel {
      background: var(--primary);
      font-size: .78rem;
      font-weight: 600;
      letter-spacing: .3px;
    }

    /* ── Section divider in form ── */
    .form-section-title {
      font-size: .78rem;
      text-transform: uppercase;
      letter-spacing: .8px;
      color: #6c757d;
      font-weight: 600;
      border-bottom: 1px solid #dee2e6;
      padding-bottom: .4rem;
      margin-bottom: .75rem;
    }
  </style>
</head>
<body>

<nav class="navbar navbar-main py-2">
  <div class="container-xl">
    <a href="index.php" class="navbar-brand-text d-flex align-items-center gap-2">
      <i class="bi bi-send-fill"></i> Egyptian Transfers Management
    </a>
  </div>
</nav>

<?php if (!empty($flash)): ?>
<div class="flash-wrap">
  <?php
    $t = $flash['type'];
    if ($t === 'success')      $icon = 'check-circle-fill';
    elseif ($t === 'warning')  $icon = 'exclamation-triangle-fill';
    elseif ($t === 'danger')   $icon = 'x-circle-fill';
    else                       $icon = 'info-circle-fill';
  ?>
  <div class="alert alert-<?= h($flash['type']) ?> alert-dismissible fade show shadow-lg" role="alert">
    <i class="bi bi-<?= $icon ?> me-2"></i><?= h($flash['message']) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
</div>
<?php endif; ?>

<main class="container-xl py-4">
  <?= $content ?>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<?php if (!empty($scripts)) echo $scripts; ?>
</body>
</html>
