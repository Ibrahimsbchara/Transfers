<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

session_start();
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/excel.php';

$db     = get_db();
$action = $_GET['action'] ?? 'home';

// ══════════════════════════════════════════════════════════════════════
//  POST routes
// ══════════════════════════════════════════════════════════════════════
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    switch ($action) {

        // ── Batches ───────────────────────────────────────────────────
        case 'create_date':
            $name = strtoupper(trim(post('name')));
            if ($name === '') { set_flash('Please enter a batch name.', 'danger'); redirect('index.php'); }
            try {
                $db->prepare('INSERT INTO transfer_dates (name) VALUES (?)')->execute([$name]);
                $id = (int)$db->lastInsertId();
                set_flash("Batch \"$name\" created.", 'success');
                redirect("index.php?action=view&id=$id");
            } catch (\PDOException $e) {
                set_flash("A batch named \"$name\" already exists.", 'danger');
                redirect('index.php');
            }

        case 'delete_date':
            $id   = post_int('id');
            $stmt = $db->prepare('SELECT name FROM transfer_dates WHERE id=?');
            $stmt->execute([$id]);
            $date = $stmt->fetch();
            if ($date) {
                $db->prepare('DELETE FROM transfer_dates WHERE id=?')->execute([$id]);
                set_flash("Batch \"{$date['name']}\" deleted.", 'warning');
            }
            redirect('index.php');

        case 'clone_batch':
            $srcId   = post_int('source_id');
            $newName = strtoupper(trim(post('new_name')));
            if ($newName === '') { set_flash('Please enter a name for the cloned batch.', 'danger'); redirect('index.php'); }
            try {
                $db->prepare('INSERT INTO transfer_dates (name) VALUES (?)')->execute([$newName]);
                $newId = (int)$db->lastInsertId();
            } catch (\PDOException $e) {
                set_flash("A batch named \"$newName\" already exists.", 'danger');
                redirect('index.php');
            }
            $rows = $db->prepare('SELECT * FROM transfers WHERE date_id=? ORDER BY sort_order,id');
            $rows->execute([$srcId]);
            $ins = $db->prepare('
                INSERT INTO transfers (date_id,vessel,sender_name,receiver_name,amount,
                    bank_name,account_number,iban,swift_code,branch,
                    mobile_number,cid,place_of_delivery,sort_order)
                VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)
            ');
            foreach ($rows as $t) {
                $ins->execute([
                    $newId, $t['vessel'], $t['sender_name'], $t['receiver_name'], $t['amount'],
                    $t['bank_name'], $t['account_number'], $t['iban'], $t['swift_code'],
                    $t['branch'], $t['mobile_number'], $t['cid'], $t['place_of_delivery'],
                    $t['sort_order'],
                ]);
            }
            set_flash("Batch cloned as \"$newName\" — make your adjustments.", 'success');
            redirect("index.php?action=view&id=$newId");

        // ── Transfers ─────────────────────────────────────────────────
        case 'add_transfer':
            $dateId  = post_int('date_id');
            $ordStmt = $db->prepare('SELECT COALESCE(MAX(sort_order),0) FROM transfers WHERE date_id=?');
            $ordStmt->execute([$dateId]);
            $maxOrd  = (int)$ordStmt->fetchColumn();
            $db->prepare('
                INSERT INTO transfers
                    (date_id,vessel,sender_name,receiver_name,amount,
                     bank_name,account_number,iban,swift_code,branch,
                     mobile_number,cid,place_of_delivery,sort_order)
                VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)
            ')->execute([
                $dateId,
                post_upper('vessel'), post_upper('sender_name'), post_upper('receiver_name'),
                post_float('amount'),
                post('bank_name'), post('account_number'),
                strtoupper(post('iban')), strtoupper(post('swift_code')),
                post('branch'), post('mobile_number'), post('cid'),
                post_upper('place_of_delivery'), $maxOrd + 1,
            ]);
            set_flash('Transfer added.', 'success');
            redirect("index.php?action=view&id=$dateId");

        case 'edit_transfer':
            $id     = post_int('id');
            $dateId = post_int('date_id');
            $db->prepare('
                UPDATE transfers SET
                    vessel=?,sender_name=?,receiver_name=?,amount=?,
                    bank_name=?,account_number=?,iban=?,swift_code=?,
                    branch=?,mobile_number=?,cid=?,place_of_delivery=?
                WHERE id=? AND date_id=?
            ')->execute([
                post_upper('vessel'), post_upper('sender_name'), post_upper('receiver_name'),
                post_float('amount'),
                post('bank_name'), post('account_number'),
                strtoupper(post('iban')), strtoupper(post('swift_code')),
                post('branch'), post('mobile_number'), post('cid'),
                post_upper('place_of_delivery'), $id, $dateId,
            ]);
            set_flash('Transfer updated.', 'success');
            redirect("index.php?action=view&id=$dateId");

        case 'delete_transfer':
            $id     = post_int('id');
            $dateId = post_int('date_id');
            $db->prepare('DELETE FROM transfers WHERE id=? AND date_id=?')->execute([$id, $dateId]);
            set_flash('Transfer deleted.', 'warning');
            redirect("index.php?action=view&id=$dateId");

        // ── Vessels (settings) ────────────────────────────────────────
        case 'add_vessel':
            $name = strtoupper(trim(post('name')));
            if ($name !== '') {
                try { $db->prepare('INSERT INTO vessels (name) VALUES (?)')->execute([$name]); }
                catch (\PDOException $e) { /* duplicate — ignore */ }
            }
            redirect('index.php?action=settings');

        case 'delete_vessel':
            $db->prepare('DELETE FROM vessels WHERE id=?')->execute([post_int('id')]);
            redirect('index.php?action=settings');

        // ── Banks (settings) ──────────────────────────────────────────
        case 'add_bank':
            $name  = trim(post('name'));
            $brnch = trim(post('branch'));
            $swift = strtoupper(trim(post('swift_code')));
            if ($name !== '') {
                $db->prepare('INSERT INTO banks (name,branch,swift_code) VALUES (?,?,?)')->execute([$name, $brnch, $swift]);
            }
            redirect('index.php?action=settings');

        case 'edit_bank':
            $db->prepare('UPDATE banks SET name=?,branch=?,swift_code=? WHERE id=?')->execute([
                trim(post('name')), trim(post('branch')),
                strtoupper(trim(post('swift_code'))), post_int('id'),
            ]);
            redirect('index.php?action=settings');

        case 'delete_bank':
            $db->prepare('DELETE FROM banks WHERE id=?')->execute([post_int('id')]);
            redirect('index.php?action=settings');
    }

    redirect('index.php');
}

// ══════════════════════════════════════════════════════════════════════
//  GET: Excel export
// ══════════════════════════════════════════════════════════════════════
if ($action === 'export') {
    $id   = get_int('id');
    $stmt = $db->prepare('SELECT * FROM transfer_dates WHERE id=?');
    $stmt->execute([$id]);
    $date = $stmt->fetch();
    if (!$date) redirect('index.php');

    $stmt = $db->prepare('SELECT * FROM transfers WHERE date_id=? ORDER BY sort_order,id');
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
    if (!$date) { set_flash('Batch not found.', 'danger'); redirect('index.php'); }

    $stmt = $db->prepare('SELECT * FROM transfers WHERE date_id=? ORDER BY sort_order,id');
    $stmt->execute([$id]);
    $transfers  = $stmt->fetchAll();
    $total      = array_sum(array_column($transfers, 'amount'));
    $page_title = $date['name'];

    // Vessels + banks for live-search autocomplete
    $vessels = $db->query('SELECT name FROM vessels ORDER BY name')->fetchAll(\PDO::FETCH_COLUMN);
    $banks   = $db->query('SELECT * FROM banks ORDER BY name')->fetchAll();

    require __DIR__ . '/views/date.php';

} elseif ($action === 'settings') {
    $page_title = 'Settings';
    $vessels    = $db->query('SELECT * FROM vessels ORDER BY name')->fetchAll();
    $banks      = $db->query('SELECT * FROM banks ORDER BY name')->fetchAll();
    require __DIR__ . '/views/settings.php';

} else {
    $dates = $db->query('
        SELECT td.*, COUNT(t.id) AS transfer_count,
               COALESCE(SUM(t.amount),0) AS total_amount
        FROM transfer_dates td
        LEFT JOIN transfers t ON t.date_id = td.id
        GROUP BY td.id ORDER BY td.created_at DESC
    ')->fetchAll();
    $page_title = 'Transfer Batches';
    require __DIR__ . '/views/home.php';
}

$content = ob_get_clean();
require __DIR__ . '/views/layout.php';
