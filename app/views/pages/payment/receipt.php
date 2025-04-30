<?php require APPROOT . '/views/layouts/main.php'; ?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Payment Receipt</h6>
                    <button class="btn btn-sm btn-primary" onclick="window.print()">
                        <i class="fas fa-print me-2"></i> Print Receipt
                    </button>
                </div>
                
                <div class="card-body px-4 pt-4 pb-2">
                    <!-- Receipt content -->
                    <div class="receipt-container">
                        <div class="text-center mb-4">
                            <h4>BARANGAY BUSINESS REGISTRATION</h4>
                            <h5>OFFICIAL PAYMENT RECEIPT</h5>
                        </div>
                        
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <p><strong>Receipt #:</strong> <?php echo $data['payment']->id; ?></p>
                                <p><strong>Payment Date:</strong> <?php echo date('F d, Y', strtotime($data['payment']->payment_date)); ?></p>
                                <p><strong>Reference #:</strong> <?php echo $data['payment']->reference_number; ?></p>
                            </div>
                            <div class="col-md-6 text-md-end">
                                <p><strong>Status:</strong> 
                                    <span class="badge bg-<?php echo ($data['payment']->payment_status == 'Verified') ? 'success' : (($data['payment']->payment_status == 'Rejected') ? 'danger' : 'warning'); ?>">
                                        <?php echo $data['payment']->payment_status; ?>
                                    </span>
                                </p>
                                <p><strong>Method:</strong> <?php echo $data['payment']->payment_method; ?></p>
                                <?php if($data['payment']->verified_at): ?>
                                    <p><strong>Verified Date:</strong> <?php echo date('F d, Y', strtotime($data['payment']->verified_at)); ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6>Business Information</h6>
                                <hr>
                                <p><strong>Business Name:</strong> <?php echo $data['business']->name; ?></p>
                                <p><strong>Business Type:</strong> <?php echo $data['business']->type; ?></p>
                                <p><strong>Address:</strong> <?php echo $data['business']->address; ?></p>
                                <p><strong>Owner:</strong> <?php echo $data['owner']->first_name . ' ' . $data['owner']->last_name; ?></p>
                            </div>
                        </div>
                        
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6>Payment Details</h6>
                                <hr>
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Description</th>
                                                <th class="text-end">Amount</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>Business Registration Fee</td>
                                                <td class="text-end">₱ <?php echo number_format($data['payment']->amount, 2); ?></td>
                                            </tr>
                                            <tr>
                                                <th>Total</th>
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
        margin: 10mm;
    }
    
    body {
        background-color: #fff !important;
    }
    
    .navbar, .sidenav, .card-header button, .footer {
        display: none !important;
    }
    
    .main-content {
        margin-left: 0 !important;
        padding: 0 !important;
    }
    
    .card {
        box-shadow: none !important;
        border: none !important;
    }
</style>