<!-- ══ Date detail page — transfers for one batch ════════════════════ -->

<!-- ── Page header ──────────────────────────────────────────────────── -->
<div class="d-flex justify-content-between align-items-start mb-4 flex-wrap gap-3">
  <div>
    <a href="index.php" class="text-decoration-none text-muted small d-block mb-1">
      <i class="bi bi-arrow-left me-1"></i>All Batches
    </a>
    <h2 class="fw-bold mb-0" style="color:var(--primary)">
      <i class="bi bi-file-earmark-spreadsheet me-2"></i><?= h($date['name']) ?>
    </h2>
  </div>
  <div class="d-flex gap-2 flex-wrap">
    <button class="btn btn-primary"
            data-bs-toggle="modal" data-bs-target="#addModal">
      <i class="bi bi-plus-circle me-2"></i>Add Transfer
    </button>
    <a href="index.php?action=export&id=<?= (int)$date['id'] ?>"
       class="btn btn-success" id="exportBtn" onclick="startExport(this)">
      <i class="bi bi-file-earmark-excel me-2"></i>Export Excel
    </a>
  </div>
</div>

<!-- ── Summary stats ─────────────────────────────────────────────────── -->
<div class="row g-3 mb-4">
  <div class="col-sm-4">
    <div class="stat-card">
      <div class="stat-label">Total Records</div>
      <div class="stat-value" style="color:var(--primary)"><?= count($transfers) ?></div>
    </div>
  </div>
  <div class="col-sm-4">
    <div class="stat-card">
      <div class="stat-label">Total Amount</div>
      <div class="stat-value text-success"><?= number_format($total, 2) ?></div>
    </div>
  </div>
  <div class="col-sm-4">
    <div class="stat-card">
      <div class="stat-label">Unique Vessels</div>
      <div class="stat-value" style="color:var(--primary)">
        <?= count(array_unique(array_column($transfers, 'vessel'))) ?>
      </div>
    </div>
  </div>
</div>

<!-- ── Transfers table ────────────────────────────────────────────────── -->
<?php if (empty($transfers)): ?>
<div class="card py-5 text-center">
  <div class="card-body">
    <i class="bi bi-inbox fs-1 text-muted d-block mb-3"></i>
    <h5 class="text-muted">No transfers yet</h5>
    <button class="btn btn-primary mt-2"
            data-bs-toggle="modal" data-bs-target="#addModal">
      <i class="bi bi-plus-circle me-2"></i>Add First Transfer
    </button>
  </div>
</div>

<?php else: ?>
<div class="card">
  <div class="table-responsive">
    <table class="table table-hover mb-0 align-middle" id="transfersTable">
      <thead>
        <tr>
          <th style="width:40px">#</th>
          <th>Vessel</th>
          <th>From (Sender)</th>
          <th>To (Receiver)</th>
          <th class="text-end">Amount</th>
          <th>Bank</th>
          <th class="text-center" style="width:110px">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($transfers as $i => $t): ?>
        <tr>
          <td class="text-muted small"><?= $i + 1 ?></td>
          <td>
            <span class="badge badge-vessel"><?= h((string)($t['vessel'] ?: '—')) ?></span>
          </td>
          <td class="small"><?= h((string)($t['sender_name']   ?: '—')) ?></td>
          <td class="small"><?= h((string)($t['receiver_name'] ?: '—')) ?></td>
          <td class="text-end fw-semibold"><?= number_format((float)$t['amount'], 2) ?></td>
          <td class="text-muted small"><?= h((string)($t['bank_name'] ?: '—')) ?></td>
          <td class="text-center">
            <!-- Edit -->
            <button class="btn btn-sm btn-outline-primary me-1"
                    title="Edit"
                    onclick="openEdit(<?= htmlspecialchars(
                        json_encode($t, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE),
                        ENT_QUOTES, 'UTF-8'
                    ) ?>)">
              <i class="bi bi-pencil"></i>
            </button>
            <!-- Delete -->
            <form method="post" action="index.php?action=delete_transfer"
                  class="d-inline"
                  onsubmit="return confirm('Delete this transfer record?')">
              <input type="hidden" name="id"      value="<?= (int)$t['id'] ?>">
              <input type="hidden" name="date_id" value="<?= (int)$date['id'] ?>">
              <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                <i class="bi bi-trash"></i>
              </button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>

        <!-- Total row -->
        <tr class="total-row">
          <td colspan="3" class="text-end pe-3 fw-bold">TOTAL</td>
          <td></td>
          <td class="text-end fw-bold fs-5"><?= number_format($total, 2) ?></td>
          <td colspan="2"></td>
        </tr>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>


<!-- ══════════════════════════════════════════════════════════════════
     ADD TRANSFER MODAL
     ══════════════════════════════════════════════════════════════════ -->
<div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="addModalLabel">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header modal-header-primary">
        <h5 class="modal-title" id="addModalLabel">
          <i class="bi bi-plus-circle me-2"></i>Add Transfer Record
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="post" action="index.php?action=add_transfer">
        <input type="hidden" name="date_id" value="<?= (int)$date['id'] ?>">
        <div class="modal-body">
          <?php include __DIR__ . '/_transfer_fields.php'; ?>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">
            <i class="bi bi-check2-circle me-2"></i>Add Transfer
          </button>
        </div>
      </form>
    </div>
  </div>
</div>


<!-- ══════════════════════════════════════════════════════════════════
     EDIT TRANSFER MODAL
     ══════════════════════════════════════════════════════════════════ -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header modal-header-danger">
        <h5 class="modal-title" id="editModalLabel">
          <i class="bi bi-pencil-square me-2"></i>Edit Transfer Record
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="post" id="editForm" action="index.php?action=edit_transfer">
        <input type="hidden" name="date_id" value="<?= (int)$date['id'] ?>">
        <input type="hidden" name="id"      id="editId" value="">
        <div class="modal-body" id="editModalBody">
          <?php include __DIR__ . '/_transfer_fields.php'; ?>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-danger">
            <i class="bi bi-check2-circle me-2"></i>Save Changes
          </button>
        </div>
      </form>
    </div>
  </div>
</div>


<!-- ── Shared datalists (used by both Add and Edit modals) ──────────── -->
<datalist id="vessel-list">
  <?php foreach ($vessels as $v): ?>
  <option value="<?= h($v) ?>">
  <?php endforeach; ?>
</datalist>

<datalist id="bank-list">
  <?php foreach ($banks as $b): ?>
  <option value="<?= h($b['name']) ?>">
  <?php endforeach; ?>
</datalist>

<!-- Banks JSON for autofill -->
<script type="application/json" id="banks-json"><?= json_encode($banks, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?></script>

<!-- ── JavaScript ──────────────────────────────────────────────────── -->
<?php ob_start(); ?>
<script>
function startExport(btn) {
  btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Generating…';
  btn.classList.add('disabled');
  setTimeout(function() {
    btn.innerHTML = '<i class="bi bi-file-earmark-excel me-2"></i>Export Excel';
    btn.classList.remove('disabled');
  }, 6000);
}

function autofillBank(el) {
  var banks = JSON.parse(document.getElementById('banks-json').textContent);
  var val   = el.value.trim().toLowerCase();
  var match = banks.find(function(b) { return b.name.toLowerCase() === val; });
  if (match) {
    var form = el.closest('form');
    if (match.swift_code) form.elements['swift_code'].value = match.swift_code;
    if (match.branch)     form.elements['branch'].value     = match.branch;
  }
}

function openEdit(t) {
  document.getElementById('editId').value = t.id;

  var fields = [
    'vessel', 'sender_name', 'receiver_name', 'amount',
    'bank_name', 'account_number', 'iban', 'swift_code',
    'branch', 'mobile_number', 'cid', 'place_of_delivery'
  ];

  var form = document.getElementById('editForm');
  fields.forEach(function(name) {
    var el = form.elements[name];
    if (el) el.value = (t[name] !== null && t[name] !== undefined) ? t[name] : '';
  });

  new bootstrap.Modal(document.getElementById('editModal')).show();
}
</script>
<?php $scripts = ob_get_clean(); ?>
