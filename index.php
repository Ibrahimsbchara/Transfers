<?php
declare(strict_types=1);

session_start();
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/excel.php';

$db     = get_db();
$action = $_GET['action'] ?? 'home';

// ══════════════════════════════════════════════════════════════════════
//  POST routes  (all mutations)
// ══════════════════════════════════════════════════════════════════════
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    switch ($action) {

        // ── Create a new transfer batch ────────────────────────────────
        case 'create_date':
            $name = strtoupper(trim(post('name')));
            if ($name === '') {
                set_flash('Please enter a batch name.', 'danger');
                redirect('index.php');
            }
            try {
                $stmt = $db->prepare('INSERT INTO transfer_dates (name) VALUES (?)');
                $stmt->execute([$name]);
                $id = (int)$db->lastInsertId();
                set_flash("Batch \"$name\" created.", 'success');
                redirect("index.php?action=view&id=$id");
            } catch (\PDOException $e) {
                set_flash("A batch named \"$name\" already exists.", 'danger');
                redirect('index.php');
            }

        // ── Delete a batch (cascades to its transfers) ─────────────────
        case 'delete_date':
            $id   = post_int('id');
            $stmt = $db->prepare('SELECT name FROM transfer_dates WHERE id = ?');
            $stmt->execute([$id]);
            $date = $stmt->fetch();
            if ($date) {
                $db->prepare('DELETE FROM transfer_dates WHERE id = ?')->execute([$id]);
                set_flash("Batch \"{$date['name']}\" deleted.", 'warning');
            }
            redirect('index.php');

        // ── Add a transfer record ──────────────────────────────────────
        case 'add_transfer':
            $dateId  = post_int('date_id');
            $ordStmt = $db->prepare('SELECT COALESCE(MAX(sort_order),0) FROM transfers WHERE date_id=?');
            $ordStmt->execute([$dateId]);
            $maxOrd  = (int)$ordStmt->fetchColumn();

            $stmt = $db->prepare('
                INSERT INTO transfers
                    (date_id, vessel, sender_name, receiver_name, amount,
                     bank_name, account_number, iban, swift_code, branch,
                     mobile_number, cid, place_of_delivery, sort_order)
                VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)
            ');
            $stmt->execute([
                $dateId,
                post_upper('vessel'),
                post_upper('sender_name'),
                post_upper('receiver_name'),
                post_float('amount'),
                post('bank_name'),
                post('account_number'),
                strtoupper(post('iban')),
                strtoupper(post('swift_code')),
                post('branch'),
                post('mobile_number'),
                post('cid'),
                post_upper('place_of_delivery'),
                (int)$maxOrd + 1,
            ]);
            set_flash('Transfer added successfully.', 'success');
            redirect("index.php?action=view&id=$dateId");

        // ── Update an existing transfer record ─────────────────────────
        case 'edit_transfer':
            $id     = post_int('id');
            $dateId = post_int('date_id');
            $stmt   = $db->prepare('
                UPDATE transfers SET
                    vessel=?, sender_name=?, receiver_name=?, amount=?,
                    bank_name=?, account_number=?, iban=?, swift_code=?,
                    branch=?, mobile_number=?, cid=?, place_of_delivery=?
                WHERE id=? AND date_id=?
            ');
            $stmt->execute([
                post_upper('vessel'),
                post_upper('sender_name'),
                post_upper('receiver_name'),
                post_float('amount'),
                post('bank_name'),
                post('account_number'),
                strtoupper(post('iban')),
                strtoupper(post('swift_code')),
                post('branch'),
                post('mobile_number'),
                post('cid'),
                post_upper('place_of_delivery'),
                $id, $dateId,
            ]);
            set_flash('Transfer updated successfully.', 'success');
            redirect("index.php?action=view&id=$dateId");

        // ── Delete a single transfer record ────────────────────────────
        case 'delete_transfer':
            $id     = post_int('id');
            $dateId = post_int('date_id');
            $db->prepare('DELETE FROM transfers WHERE id=? AND date_id=?')->execute([$id, $dateId]);
            set_flash('Transfer deleted.', 'warning');
            redirect("index.php?action=view&id=$dateId");
    }

    redirect('index.php');
}

// ══════════════════════════════════════════════════════════════════════
//  GET: Excel export  (streams file, no HTML)
// ══════════════════════════════════════════════════════════════════════
if ($action === 'export') {
    $id   = get_int('id');
    $stmt = $db->prepare('SELECT * FROM transfer_dates WHERE id=?');
    $stmt->execute([$id]);
    $date = $stmt->fetch();
    if (!$date) redirect('index.php');

    $stmt = $db->prepare('SELECT * FROM transfers WHERE date_id=? ORDER BY sort_order, id');
    $stmt->execute([$id]);
    $transfers = $stmt->fetchAll();

    $xlsx     = generate_excel($date['name'], $transfers);
    $filename = str_replace([' ', '/'], '_', $date['name']) . '.xlsx';

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Length: ' . strlen($xlsx));
    header('Cache-Control: no-cache, no-store');
    header('Pragma: no-cache');
    echo $xlsx;
    exit;
}

// ══════════════════════════════════════════════════════════════════════
//  GET: Render HTML views
// ══════════════════════════════════════════════════════════════════════
$flash = get_flash();
ob_start();

if ($action === 'view') {
    $id   = get_int('id');
    $stmt = $db->prepare('SELECT * FROM transfer_dates WHERE id=?');
    $stmt->execute([$id]);
    $date = $stmt->fetch();
    if (!$date) {
        set_flash('Batch not found.', 'danger');
        redirect('index.php');
    }

    $stmt = $db->prepare('SELECT * FROM transfers WHERE date_id=? ORDER BY sort_order, id');
    $stmt->execute([$id]);
    $transfers = $stmt->fetchAll();
    $total     = array_sum(array_column($transfers, 'amount'));
    $page_title = $date['name'];

    require __DIR__ . '/views/date.php';

} else {
    // Home — list all batches with counts and totals
    $dates = $db->query('
        SELECT td.*,
               COUNT(t.id)           AS transfer_count,
               COALESCE(SUM(t.amount),0) AS total_amount
        FROM transfer_dates td
        LEFT JOIN transfers t ON t.date_id = td.id
        GROUP BY td.id
        ORDER BY td.created_at DESC
    ')->fetchAll();

    $page_title = 'Transfer Batches';
    require __DIR__ . '/views/home.php';
}

$content = ob_get_clean();
require __DIR__ . '/views/layout.php';
