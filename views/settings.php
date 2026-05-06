<!-- ══ Settings page ════════════════════════════════════════════════ -->
<div class="d-flex align-items-center mb-4 gap-3">
  <a href="index.php" class="btn btn-light btn-sm">
    <i class="bi bi-arrow-left me-1"></i>Back
  </a>
  <h2 class="fw-bold mb-0" style="color:var(--primary)">
    <i class="bi bi-gear me-2"></i>Settings
  </h2>
</div>

<div class="row g-4">

  <!-- ══ Vessels ══════════════════════════════════════════════════════ -->
  <div class="col-lg-4">
    <div class="card h-100">
      <div class="card-header-primary d-flex justify-content-between align-items-center">
        <span><i class="bi bi-ship me-2"></i>Vessels</span>
        <span class="badge bg-light text-dark"><?= count($vessels) ?></span>
      </div>

      <!-- Add vessel -->
      <div class="p-3 border-bottom">
        <form method="post" action="index.php?action=add_vessel" class="d-flex gap-2">
          <input type="text" name="name" class="form-control form-control-sm"
                 placeholder="New vessel name…" required>
          <button type="submit" class="btn btn-sm btn-primary text-nowrap">
            <i class="bi bi-plus-lg"></i>
          </button>
        </form>
      </div>

      <!-- Vessel list -->
      <?php if (empty($vessels)): ?>
      <div class="p-4 text-center text-muted small">
        <i class="bi bi-inbox d-block fs-4 mb-2"></i>No vessels yet
      </div>
      <?php else: ?>
      <ul class="list-group list-group-flush">
        <?php foreach ($vessels as $v): ?>
        <li class="list-group-item d-flex justify-content-between align-items-center py-2">
          <span class="fw-semibold small"><?= h($v['name']) ?></span>
          <form method="post" action="index.php?action=delete_vessel" class="d-inline"
                onsubmit="return confirm('Delete vessel «<?= h(addslashes($v['name'])) ?>»?')">
            <input type="hidden" name="id" value="<?= (int)$v['id'] ?>">
            <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-2">
              <i class="bi bi-trash"></i>
            </button>
          </form>
        </li>
        <?php endforeach; ?>
      </ul>
      <?php endif; ?>
    </div>
  </div>

  <!-- ══ Banks ════════════════════════════════════════════════════════ -->
  <div class="col-lg-8">
    <div class="card">
      <div class="card-header-primary d-flex justify-content-between align-items-center">
        <span><i class="bi bi-bank me-2"></i>Banks</span>
        <span class="badge bg-light text-dark"><?= count($banks) ?></span>
      </div>

      <!-- Add bank -->
      <div class="p-3 border-bottom">
        <form method="post" action="index.php?action=add_bank">
          <div class="row g-2">
            <div class="col-md-4">
              <input type="text" name="name" class="form-control form-control-sm"
                     placeholder="Bank name *" required>
            </div>
            <div class="col-md-4">
              <input type="text" name="branch" class="form-control form-control-sm"
                     placeholder="Branch (optional)">
            </div>
            <div class="col-md-3">
              <input type="text" name="swift_code" class="form-control form-control-sm"
                     placeholder="SWIFT code">
            </div>
            <div class="col-md-1">
              <button type="submit" class="btn btn-sm btn-primary w-100">
                <i class="bi bi-plus-lg"></i>
              </button>
            </div>
          </div>
        </form>
      </div>

      <!-- Bank table -->
      <?php if (empty($banks)): ?>
      <div class="p-4 text-center text-muted small">
        <i class="bi bi-inbox d-block fs-4 mb-2"></i>No banks yet
      </div>
      <?php else: ?>
      <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle small">
          <thead>
            <tr>
              <th>Bank Name</th>
              <th>Branch</th>
              <th>SWIFT Code</th>
              <th class="text-center" style="width:90px">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($banks as $b): ?>
            <tr>
              <td class="fw-semibold"><?= h($b['name']) ?></td>
              <td class="text-muted"><?= h($b['branch']) ?></td>
              <td><code><?= h($b['swift_code']) ?></code></td>
              <td class="text-center">
                <button class="btn btn-sm btn-outline-primary me-1"
                        onclick='openEditBank(<?= json_encode($b, JSON_HEX_APOS|JSON_HEX_QUOT) ?>)'>
                  <i class="bi bi-pencil"></i>
                </button>
                <form method="post" action="index.php?action=delete_bank" class="d-inline"
                      onsubmit="return confirm('Delete bank «<?= h(addslashes($b['name'])) ?>»?')">
                  <input type="hidden" name="id" value="<?= (int)$b['id'] ?>">
                  <button type="submit" class="btn btn-sm btn-outline-danger">
                    <i class="bi bi-trash"></i>
                  </button>
                </form>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php endif; ?>
    </div>
  </div>

</div>

<!-- Edit Bank Modal -->
<div class="modal fade" id="editBankModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header modal-header-danger">
        <h5 class="modal-title"><i class="bi bi-pencil-square me-2"></i>Edit Bank</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="post" id="editBankForm" action="index.php?action=edit_bank">
        <input type="hidden" name="id" id="editBankId">
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label fw-semibold">Bank Name</label>
            <input type="text" name="name" id="editBankName" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Branch</label>
            <input type="text" name="branch" id="editBankBranch" class="form-control">
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">SWIFT Code</label>
            <input type="text" name="swift_code" id="editBankSwift" class="form-control">
          </div>
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

<?php ob_start(); ?>
<script>
function openEditBank(b) {
  document.getElementById('editBankId').value    = b.id;
  document.getElementById('editBankName').value  = b.name   || '';
  document.getElementById('editBankBranch').value = b.branch || '';
  document.getElementById('editBankSwift').value  = b.swift_code || '';
  new bootstrap.Modal(document.getElementById('editBankModal')).show();
}
</script>
<?php $scripts = ob_get_clean(); ?>
