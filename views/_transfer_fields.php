<!-- Shared form fields — included in both Add and Edit modals.
     Uses only `name` attributes (no IDs) so duplicate inclusion is safe.
     The Edit modal's JS populates values via form.elements[name].        -->
<div class="row g-3">

  <!-- Vessel + Amount on same row -->
  <div class="col-md-7">
    <label class="form-label fw-semibold">
      Vessel <span class="text-danger">*</span>
    </label>
    <input type="text" name="vessel" class="form-control"
           placeholder="e.g.  AK HAMZA">
  </div>
  <div class="col-md-5">
    <label class="form-label fw-semibold">Amount</label>
    <div class="input-group">
      <input type="number" name="amount" class="form-control"
             placeholder="0.00" step="0.01" min="0" value="0">
    </div>
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

  <!-- Bank section divider -->
  <div class="col-12 mt-2">
    <p class="form-section-title">
      <i class="bi bi-bank me-1"></i>Bank Details
    </p>
  </div>

  <!-- Bank Name -->
  <div class="col-12">
    <label class="form-label fw-semibold">Bank Name</label>
    <input type="text" name="bank_name" class="form-control"
           placeholder="e.g.  Banque Misr  /  البنك الأهلي المصري">
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
           placeholder="e.g.  EG580037…">
  </div>

  <!-- SWIFT + Branch -->
  <div class="col-md-6">
    <label class="form-label fw-semibold">SWIFT Code</label>
    <input type="text" name="swift_code" class="form-control"
           placeholder="e.g.  NBEGEGCX">
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
           placeholder="e.g.  00201023333241">
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
           placeholder="e.g.  CAIRO">
  </div>

</div>
