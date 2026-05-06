<!-- ══ Home page — list of all transfer batches ══════════════════════ -->
<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h2 class="fw-bold mb-1" style="color:var(--primary)">
      <i class="bi bi-collection me-2"></i>Transfer Batches
    </h2>
    <p class="text-muted mb-0 small">Create a batch per period, add transfers, then export to Excel.</p>
  </div>
  <button class="btn btn-primary"
          data-bs-toggle="modal" data-bs-target="#newBatchModal">
    <i class="bi bi-plus-circle me-2"></i>New Batch
  </button>
</div>

<?php if (empty($dates)): ?>
<div class="card py-5 text-center">
  <div class="card-body">
    <i class="bi bi-inbox fs-1 text-muted d-block mb-3"></i>
    <h5 class="text-muted">No transfer batches yet</h5>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newBatchModal">
      <i class="bi bi-plus-circle me-2"></i>Create First Batch
    </button>
  </div>
</div>

<?php else: ?>
<div class="card">
  <div class="table-responsive">
    <table class="table table-hover mb-0 align-middle">
      <thead>
        <tr>
          <th>Batch Name</th>
          <th class="text-center">Records</th>
          <th class="text-end">Total Amount</th>
          <th class="text-center">Created</th>
          <th class="text-center" style="width:190px">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($dates as $d): ?>
        <tr>
          <td>
            <a href="index.php?action=view&id=<?= (int)$d['id'] ?>"
               class="fw-semibold text-decoration-none d-flex align-items-center gap-2"
               style="color:var(--primary)">
              <i class="bi bi-file-earmark-spreadsheet text-muted"></i>
              <?= h($d['name']) ?>
            </a>
          </td>
          <td class="text-center">
            <span class="badge bg-secondary rounded-pill"><?= (int)$d['transfer_count'] ?></span>
          </td>
          <td class="text-end fw-semibold"><?= number_format((float)$d['total_amount'], 2) ?></td>
          <td class="text-center text-muted small"><?= h(substr((string)$d['created_at'], 0, 10)) ?></td>
          <td class="text-center">
            <a href="index.php?action=view&id=<?= (int)$d['id'] ?>"
               class="btn btn-sm btn-outline-primary me-1" title="View">
              <i class="bi bi-eye"></i>
            </a>
            <!-- Clone -->
            <button class="btn btn-sm btn-outline-secondary me-1" title="Clone batch"
                    onclick="openClone(<?= (int)$d['id'] ?>, '<?= h(addslashes($d['name'])) ?>')">
              <i class="bi bi-copy"></i>
            </button>
            <a href="index.php?action=export&id=<?= (int)$d['id'] ?>"
               class="btn btn-sm btn-outline-success me-1" title="Export Excel">
              <i class="bi bi-file-earmark-excel"></i>
            </a>
            <form method="post" action="index.php?action=delete_date" class="d-inline"
                  onsubmit="return confirm('Delete batch «<?= h(addslashes((string)$d['name'])) ?>»?\nAll its transfers will be permanently removed.')">
              <input type="hidden" name="id" value="<?= (int)$d['id'] ?>">
              <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                <i class="bi bi-trash"></i>
              </button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>

<!-- ── New Batch Modal ─────────────────────────────────────────────── -->
<div class="modal fade" id="newBatchModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header modal-header-primary">
        <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>New Transfer Batch</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="post" action="index.php?action=create_date">
        <div class="modal-body">
          <label class="form-label fw-semibold">Batch Name</label>
          <input type="text" class="form-control form-control-lg"
                 name="name" placeholder="e.g. MAY 2026" required autofocus>
          <div class="form-text mt-2">
            <i class="bi bi-info-circle me-1"></i>Used as the Excel sheet name. Auto-uppercased.
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">
            <i class="bi bi-check2-circle me-2"></i>Create
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- ── Clone Batch Modal ───────────────────────────────────────────── -->
<div class="modal fade" id="cloneModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header modal-header-primary">
        <h5 class="modal-title"><i class="bi bi-copy me-2"></i>Clone Batch</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="post" action="index.php?action=clone_batch">
        <input type="hidden" name="source_id" id="cloneSourceId">
        <div class="modal-body">
          <p class="text-muted small mb-3">
            Cloning: <strong id="cloneSourceName"></strong><br>
            All transfer records will be copied — you can then edit them.
          </p>
          <label class="form-label fw-semibold">New Batch Name</label>
          <input type="text" class="form-control form-control-lg"
                 name="new_name" id="cloneNewName"
                 placeholder="e.g. MAY 2026" required>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">
            <i class="bi bi-copy me-2"></i>Clone
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php ob_start(); ?>
<script>
function openClone(id, name) {
  document.getElementById('cloneSourceId').value   = id;
  document.getElementById('cloneSourceName').textContent = name;
  document.getElementById('cloneNewName').value    = '';
  new bootstrap.Modal(document.getElementById('cloneModal')).show();
}
</script>
<?php $scripts = ob_get_clean(); ?>
