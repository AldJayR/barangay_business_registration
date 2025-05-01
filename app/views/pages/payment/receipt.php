<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4 shadow-sm border-0">
                <div class="card-header pb-0 d-flex justify-content-between align-items-center bg-light rounded-top">
                    <div>
                        <h6 class="mb-0 fw-bold text-primary">Payment Receipt</h6>
                        <p class="text-muted small mb-0">Official transaction record</p>
                    </div>
                    <button class="btn btn-primary btn-sm shadow-sm" onclick="window.print()">
                        <i class="bi bi-printer me-2"></i> Print Receipt
                    </button>
                </div>
                
                <div class="card-body px-4 pt-4 pb-2">
                    <!-- Receipt content -->
                    <div class="receipt-container">
                        <div class="text-center mb-4">
                            <div class="mb-3"><i class="bi bi-check-circle-fill text-success" style="font-size: 3rem;"></i></div>
                            <h4 class="fw-bold text-dark">BARANGAY BUSINESS REGISTRATION</h4>
                            <h5 class="text-primary fw-bold">OFFICIAL PAYMENT RECEIPT</h5>
                            <div class="mt-2">
                                <span class="badge bg-<?php echo ($data['payment']->payment_status == 'Verified') ? 'success' : (($data['payment']->payment_status == 'Rejected') ? 'danger' : 'warning'); ?> p-2">
                                    <i class="bi bi-<?php echo ($data['payment']->payment_status == 'Verified') ? 'check-circle' : (($data['payment']->payment_status == 'Rejected') ? 'x-circle' : 'hourglass-split'); ?> me-1"></i>
                                    <?php echo $data['payment']->payment_status; ?>
                                </span>
                            </div>
                        </div>
                        
                        <div class="row mb-4 bg-light p-3 rounded-3">
                            <div class="col-md-6">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="bi bi-receipt text-primary me-2"></i>
                                    <p class="mb-0"><strong>Receipt #:</strong> <?php echo $data['payment']->id; ?></p>
                                </div>
                                <div class="d-flex align-items-center mb-2">
                                    <i class="bi bi-calendar-date text-primary me-2"></i>
                                    <p class="mb-0"><strong>Payment Date:</strong> <?php echo date('F d, Y', strtotime($data['payment']->payment_date)); ?></p>
                                </div>
                                <div class="d-flex align-items-center mb-2">
                                    <i class="bi bi-hash text-primary me-2"></i>
                                    <p class="mb-0"><strong>Reference #:</strong> <?php echo $data['payment']->reference_number; ?></p>
                                </div>
                            </div>
                            <div class="col-md-6 text-md-end">
                                <div class="d-flex align-items-center justify-content-md-end mb-2">
                                    <i class="bi bi-check-circle text-primary me-2"></i>
                                    <p class="mb-0"><strong>Status:</strong> 
                                        <span class="badge bg-<?php echo ($data['payment']->payment_status == 'Verified') ? 'success' : (($data['payment']->payment_status == 'Rejected') ? 'danger' : 'warning'); ?>">
                                            <?php echo $data['payment']->payment_status; ?>
                                        </span>
                                    </p>
                                </div>
                                <div class="d-flex align-items-center justify-content-md-end mb-2">
                                    <i class="bi bi-credit-card text-primary me-2"></i>
                                    <p class="mb-0"><strong>Method:</strong> <?php echo $data['payment']->payment_method; ?></p>
                                </div>
                                <?php if($data['payment']->verified_at): ?>
                                <div class="d-flex align-items-center justify-content-md-end mb-2">
                                    <i class="bi bi-calendar-check text-primary me-2"></i>
                                    <p class="mb-0"><strong>Verified Date:</strong> <?php echo date('F d, Y', strtotime($data['payment']->verified_at)); ?></p>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-building text-primary me-2"></i>
                                    <h6 class="fw-bold mb-0">Business Information</h6>
                                </div>
                                <hr>
                                <div class="business-info p-3 rounded-3 border-start border-primary border-3">
                                    <p><i class="bi bi-shop text-primary me-2"></i><strong>Business Name:</strong> <?php echo $data['business']->name; ?></p>
                                    <p><i class="bi bi-tags text-primary me-2"></i><strong>Business Type:</strong> <?php echo $data['business']->type; ?></p>
                                    <p><i class="bi bi-geo-alt text-primary me-2"></i><strong>Address:</strong> <?php echo $data['business']->address; ?></p>
                                    <p><i class="bi bi-person text-primary me-2"></i><strong>Owner:</strong> <?php echo $data['owner']->first_name . ' ' . $data['owner']->last_name; ?></p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-cash-coin text-primary me-2"></i>
                                    <h6 class="fw-bold mb-0">Payment Details</h6>
                                </div>
                                <hr>
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover">
                                        <thead class="bg-light">
                                            <tr>
                                                <th>Description</th>
                                                <th class="text-end">Amount</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td><i class="bi bi-file-earmark-text text-primary me-2"></i>Business Registration Fee</td>
                                                <td class="text-end">₱ <?php echo number_format($data['payment']->amount, 2); ?></td>
                                            </tr>
                                            <tr class="table-primary">
                                                <th><i class="bi bi-calculator text-primary me-2"></i>Total</th>
                                                <th class="text-end">₱ <?php echo number_format($data['payment']->amount, 2); ?></th>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        
                        <?php if($data['payment']->verified_by): ?>
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6>Verification Information</h6>
                                <hr>
                                <p><strong>Verified By:</strong> <?php echo $data['verifier']->first_name . ' ' . $data['verifier']->last_name; ?></p>
                                <p><strong>Position:</strong> <?php echo ucwords($data['verifier']->role); ?></p>
                                <p><strong>Date:</strong> <?php echo date('F d, Y', strtotime($data['payment']->verified_at)); ?></p>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if($data['payment']->notes): ?>
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6>Notes</h6>
                                <hr>
                                <p><?php echo $data['payment']->notes; ?></p>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <div class="row mt-5">
                            <div class="col-12 text-center">
                                <p>This is an electronically generated receipt.</p>
                                <p class="small">Generated on: <?php echo date('F d, Y h:i A'); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style type="text/css" media="print">
    @page {
        size: portrait;
        margin: 5mm;
    }
    
    body {
        background-color: #fff !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
    
    /* Hide all navigation elements */
    .navbar, .sidenav, .card-header button, .footer, #sidenav-main, .fixed-plugin, .nav, .nav-item,
    .sidebar, .bg-white.sidebar, .col-md-3.col-lg-2.d-md-block.bg-white.sidebar, 
    .col-md-3, .col-lg-2, header, footer, 
    [class*="sidebar"], [id*="sidebar"], .nav-item-text, .nav-link-text,
    nav, .nav-pills, .nav-link, .dropdown-menu, .dropdown {
        display: none !important;
        width: 0 !important;
        height: 0 !important;
        margin: 0 !important;
        padding: 0 !important;
        visibility: hidden !important;
        opacity: 0 !important;
        position: absolute !important;
        left: -9999px !important;
    }
    
    /* Ensure main content takes full width */
    .main-content, main, .col-md-9, .col-lg-10, .px-md-4, .py-4 {
        margin-left: 0 !important;
        padding: 0 !important;
        width: 100% !important;
        max-width: 100% !important;
        flex: 0 0 100% !important;
    }
    
    .container-fluid {
        padding: 0 !important;
        margin: 0 !important;
        width: 100% !important;
    }
    
    .card {
        box-shadow: none !important;
        border: none !important;
        margin: 0 !important;
    }
    
    .receipt-container {
        font-size: 0.9rem !important;
    }
    
    .table {
        font-size: 0.85rem !important;
    }
    
    .mb-4 {
        margin-bottom: 0.75rem !important;
    }
    
    .py-4 {
        padding-top: 0.75rem !important;
        padding-bottom: 0.75rem !important;
    }
    
    .card-body {
        padding: 0.75rem 1rem !important;
    }
    
    /* Reset row and column to ensure proper layout */
    .row {
        display: block !important;
        width: 100% !important;
    }
    
    .col-12, .col-md-6 {
        width: 100% !important;
        max-width: 100% !important;
        flex: 0 0 100% !important;
    }
    
    /* Ensure no page breaks inside these elements */
    .business-info, .table-responsive {
        page-break-inside: avoid;
    }
</style>