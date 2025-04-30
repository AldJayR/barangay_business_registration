<?php
// View file for document details
?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mb-0"><i class="bi bi-file-earmark-text me-2"></i>Document Details</h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="<?= URLROOT ?>/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= URLROOT ?>/business/view/<?= $business->id ?>">Business Details</a></li>
                    <li class="breadcrumb-item"><a href="<?= URLROOT ?>/document/upload/<?= $business->id ?>">Documents</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Document Details</li>
                </ol>
            </nav>
        </div>
        <a href="<?= URLROOT ?>/document/upload/<?= $business->id ?>" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to Documents
        </a>
    </div>

    <?php SessionHelper::displayFlashMessages(); ?>

    <div class="row">
        <!-- Document Information Card -->
        <div class="col-md-5 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0"><i class="bi bi-file-earmark-text me-2"></i>Document Information</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h6 class="text-muted mb-1">Document Type</h6>
                        <p class="fs-5 mb-0"><?= htmlspecialchars($document->document_name) ?></p>
                    </div>
                    
                    <div class="mb-3">
                        <h6 class="text-muted mb-1">Status</h6>
                        <?php 
                        $statusClass = 'bg-secondary';
                        $statusIcon = 'question-circle';
                        
                        switch(strtolower($document->status)) {
                            case 'pending':
                                $statusClass = 'bg-info text-dark';
                                $statusIcon = 'hourglass-split';
                                break;
                            case 'approved':
                                $statusClass = 'bg-success';
                                $statusIcon = 'check-circle';
                                break;
                            case 'rejected':
                                $statusClass = 'bg-danger';
                                $statusIcon = 'x-circle';
                                break;
                        }
                        ?>
                        <div class="d-flex align-items-center">
                            <span class="badge <?= $statusClass ?> p-2 me-2">
                                <i class="bi bi-<?= $statusIcon ?> me-1"></i>
                                <?= ucfirst(htmlspecialchars($document->status)) ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <h6 class="text-muted mb-1">Uploaded By</h6>
                        <p class="mb-0"><?= htmlspecialchars($uploader->first_name . ' ' . $uploader->last_name) ?></p>
                    </div>
                    
                    <div class="mb-3">
                        <h6 class="text-muted mb-1">Upload Date</h6>
                        <p class="mb-0"><?= date('M d, Y h:i A', strtotime($document->uploaded_at)) ?></p>
                    </div>
                    
                    <?php if($document->notes): ?>
                    <div class="mb-3">
                        <h6 class="text-muted mb-1">Notes</h6>
                        <p class="mb-0"><?= nl2br(htmlspecialchars($document->notes)) ?></p>
                    </div>
                    <?php endif; ?>
                    
                    <?php if($document->verified_by): ?>
                    <div class="mb-3">
                        <h6 class="text-muted mb-1">Verified By</h6>
                        <p class="mb-0"><?= htmlspecialchars($verifier->first_name . ' ' . $verifier->last_name) ?></p>
                    </div>
                    
                    <div class="mb-3">
                        <h6 class="text-muted mb-1">Verification Date</h6>
                        <p class="mb-0"><?= date('M d, Y h:i A', strtotime($document->verified_at)) ?></p>
                    </div>
                    <?php endif; ?>
                    
                    <div class="mb-3">
                        <h6 class="text-muted mb-1">Business</h6>
                        <p class="mb-0"><?= htmlspecialchars($business->name) ?></p>
                    </div>
                </div>
                
                <?php if($_SESSION['user_role'] === 'admin' || $_SESSION['user_role'] === 'treasurer'): ?>
                <?php if($document->status === 'Pending'): ?>
                <div class="card-footer bg-white py-3">
                    <div class="d-flex justify-content-between">
                        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#approveModal">
                            <i class="bi bi-check-circle me-1"></i> Approve
                        </button>
                        <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal">
                            <i class="bi bi-x-circle me-1"></i> Reject
                        </button>
                    </div>
                </div>
                <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Document Preview Card -->
        <div class="col-md-7 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0"><i class="bi bi-eye me-2"></i>Document Preview</h5>
                </div>
                <div class="card-body p-0">
                    <?php 
                    $filePath = $document->file_path;
                    $fileExt = pathinfo($filePath, PATHINFO_EXTENSION);
                    $filename = basename($filePath);
                    $fileUrl = URLROOT . '/document/serveFile/' . $filename;
                    
                    if (in_array(strtolower($fileExt), ['jpg', 'jpeg', 'png', 'gif'])):
                    ?>
                        <img src="<?= $fileUrl ?>" class="img-fluid w-100" alt="Document Preview">
                    <?php elseif (strtolower($fileExt) === 'pdf'): ?>
                        <div class="ratio ratio-16x9" style="min-height: 600px;">
                            <iframe src="<?= $fileUrl ?>" allowfullscreen></iframe>
                        </div>
                    <?php else: ?>
                        <div class="p-4 text-center">
                            <i class="bi bi-file-earmark fs-1 text-muted"></i>
                            <p class="mt-2">Preview not available for this file type</p>
                            <a href="<?= $fileUrl ?>" class="btn btn-primary" download>
                                <i class="bi bi-download me-1"></i> Download File
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Approve Modal -->
    <div class="modal fade" id="approveModal" tabindex="-1" aria-labelledby="approveModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="<?= URLROOT ?>/document/verify/<?= $document->id ?>" method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title" id="approveModalLabel">Approve Document</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="status" value="Approved">
                        <p>Are you sure you want to approve this <?= strtolower(htmlspecialchars($document->document_name)) ?>?</p>
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>
                            Approving this document confirms that it meets all requirements and is valid.
                        </div>
                        <div class="mb-3">
                            <label for="notes" class="form-label">Notes (Optional)</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="Add any verification notes or comments"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Approve Document</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Reject Modal -->
    <div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="<?= URLROOT ?>/document/verify/<?= $document->id ?>" method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title" id="rejectModalLabel">Reject Document</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="status" value="Rejected">
                        <p>Are you sure you want to reject this <?= strtolower(htmlspecialchars($document->document_name)) ?>?</p>
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            Please provide a reason for rejection to help the business owner understand what needs to be corrected.
                        </div>
                        <div class="mb-3">
                            <label for="notes" class="form-label">Reason for Rejection <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="Explain why this document is being rejected" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Reject Document</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>