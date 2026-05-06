<!-- Shared form fields — included in Add and Edit modals in date.php.
     Datalists (#vessel-list, #bank-list) and #banks-json are injected
     once by date.php and shared between both modal instances.          -->
<div class="row g-3">

  <!-- Vessel (live search) + Amount -->
  <div class="col-md-7">
    <label class="form-label fw-semibold">
      Vessel <span class="text-danger">*</span>
    </label>
    <input type="text" name="vessel" class="form-control"
           placeholder="Type to search vessels…"
           list="vessel-list" autocomplete="off">
  </div>
  <div class="col-md-5">
    <label class="form-label fw-semibold">Amount</label>
    <input type="number" name="amount" class="form-control"
           placeholder="0.00" step="0.01" min="0" value="0">
  </div>

  <!-- Sender -->
  <div class="col-12">
    <label class="form-label fw-semibold">From (Sender Name)</label>
    <input type="text" name="sender_name" class="form-control"
           placeholder="Full name of the sender">
  </div>

  <!-- Receiver -->
  <div class="col-12">
    <label class="form-label fw-semibold">To (Receiver Name)</label>
    <input type="text" name="receiver_name" class="form-control"
           placeholder="Full name of the receiver">
  </div>

  <!-- Bank section -->
  <div class="col-12 mt-2">
    <p class="form-section-title">
      <i class="bi bi-bank me-1"></i>Bank Details
    </p>
  </div>

  <!-- Bank name (live search — auto-fills SWIFT + Branch) -->
  <div class="col-12">
    <label class="form-label fw-semibold">Bank Name</label>
    <input type="text" name="bank_name" class="form-control"
           placeholder="Type to search banks…"
           list="bank-list" autocomplete="off"
           oninput="autofillBank(this)">
    <div class="form-text">
      <i class="bi bi-magic me-1"></i>Selecting a saved bank auto-fills SWIFT &amp; Branch below.
    </div>
  </div>

  <!-- Account Number + IBAN -->
  <div class="col-md-6">
    <label class="form-label fw-semibold">Account Number</label>
    <input type="text" name="account_number" class="form-control"
           placeholder="Account number">
  </div>
  <div class="col-md-6">
    <label class="form-label fw-semibold">IBAN</label>
    <input type="text" name="iban" class="form-control"
           placeholder="e.g. EG580037…">
  </div>

  <!-- SWIFT + Branch (auto-filled when bank selected) -->
  <div class="col-md-6">
    <label class="form-label fw-semibold">SWIFT Code</label>
    <input type="text" name="swift_code" class="form-control"
           placeholder="e.g. NBEGEGCX">
  </div>
  <div class="col-md-6">
    <label class="form-label fw-semibold">Branch</label>
    <input type="text" name="branch" class="form-control"
           placeholder="Branch name or address">
  </div>

  <!-- Mobile + CID -->
  <div class="col-md-6">
    <label class="form-label fw-semibold">Mobile Number</label>
    <input type="text" name="mobile_number" class="form-control"
           placeholder="e.g. 00201023333241">
  </div>
  <div class="col-md-6">
    <label class="form-label fw-semibold">CID</label>
    <input type="text" name="cid" class="form-control"
           placeholder="Customer ID (optional)">
  </div>

  <!-- Place of Delivery -->
  <div class="col-12">
    <label class="form-label fw-semibold">Place of Delivery</label>
    <input type="text" name="place_of_delivery" class="form-control"
           placeholder="e.g. CAIRO">
  </div>

</div>
