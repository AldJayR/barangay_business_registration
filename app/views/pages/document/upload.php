<?php
// View file for document upload
?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mb-0"><i class="bi bi-file-earmark-arrow-up me-2"></i>Upload Required Documents</h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="<?= URLROOT ?>/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= URLROOT ?>/business/view/<?= $business->id ?>">Business Details</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Upload Documents</li>
                </ol>
            </nav>
        </div>
        <a href="<?= URLROOT ?>/business/view/<?= $business->id ?>" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to Business Details
        </a>
    </div>

    <!-- Business Information Card -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="bg-light rounded-circle p-3 me-3">
                            <i class="bi bi-shop fs-4"></i>
                        </div>
                        <div>
                            <h4 class="mb-1"><?= htmlspecialchars($business->name) ?></h4>
                            <p class="text-muted mb-0">
                                <i class="bi bi-geo-alt me-1"></i> <?= htmlspecialchars($business->address) ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php SessionHelper::displayFlashMessages(); ?>

    <!-- Instructions Card -->
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="card-title mb-0"><i class="bi bi-info-circle me-2"></i>Document Requirements</h5>
        </div>
        <div class="card-body">
            <p>Please upload the following documents to complete your business registration:</p>
            <ul class="list-group list-group-flush mb-3">
                <?php 
                // Use the correct variable name passed from controller
                $document_types = $required_documents ?? [];
                
                foreach ($document_types as $type_key => $type_info): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <span><?= htmlspecialchars($type_info['name']) ?></span>
                            <?php if ($type_info['required']): ?>
                                <span class="badge bg-danger ms-2">Required</span>
                            <?php else: ?>
                                <span class="badge bg-secondary ms-2">Optional</span>
                            <?php endif; ?>
                            <br>
                            <small class="text-muted"><?= htmlspecialchars($type_info['description']) ?></small>
                        </div>
                        <?php if (isset($uploaded_documents[$type_key])): ?>
                            <?php 
                                $status_badge = 'bg-secondary';
                                $status_text = 'Unknown';
                                
                                switch ($uploaded_documents[$type_key]->status) {
                                    case 'Pending':
                                        $status_badge = 'bg-info text-dark';
                                        $status_text = 'Pending Verification';
                                        break;
                                    case 'Approved':
                                        $status_badge = 'bg-success';
                                        $status_text = 'Approved';
                                        break;
                                    case 'Rejected':
                                        $status_badge = 'bg-danger';
                                        $status_text = 'Rejected';
                                        break;
                                }
                            ?>
                            <div class="d-flex align-items-center">
                                <span class="badge <?= $status_badge ?> me-2"><?= $status_text ?></span>
                                <a href="<?= URLROOT ?>/document/viewDocument/<?= $uploaded_documents[$type_key]->id ?>" class="btn btn-sm btn-outline-primary me-2">
                                    <i class="bi bi-eye me-1"></i> View
                                </a>
                                <?php if ($uploaded_documents[$type_key]->status === 'Pending'): ?>
                                    <a href="<?= URLROOT ?>/document/delete/<?= $uploaded_documents[$type_key]->id ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this document?')">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <button class="btn btn-sm btn-primary upload-doc-btn" data-type="<?= $type_key ?>" data-name="<?= htmlspecialchars($type_info['name']) ?>">
                                <i class="bi bi-upload me-1"></i> Upload
                            </button>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
            <div class="alert alert-info">
                <i class="bi bi-info-circle-fill me-2"></i>
                <span>Supported file formats: PDF, JPEG, PNG, GIF. Maximum file size: 5MB</span>
            </div>
        </div>
    </div>

    <!-- Upload Document Form Modal -->
    <div class="modal fade" id="uploadDocumentModal" tabindex="-1" aria-labelledby="uploadDocumentModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="<?= URLROOT ?>/document/upload/<?= $business->id ?>" method="POST" enctype="multipart/form-data">
                    <div class="modal-header">
                        <h5 class="modal-title" id="uploadDocumentModalLabel">Upload Document</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="document_type" class="form-label">Document Type</label>
                            <input type="text" class="form-control" id="document_type_name" readonly>
                            <input type="hidden" id="document_type" name="document_type">
                        </div>
                        <div class="mb-3">
                            <label for="document_file" class="form-label">Document File <span class="text-danger">*</span></label>
                            <input type="file" class="form-control" id="document_file" name="document_file" required>
                            <div class="form-text">Select a PDF, JPEG, PNG, or GIF file (max 5MB)</div>
                        </div>
                        <div class="mb-3">
                            <label for="notes" class="form-label">Notes (Optional)</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="Add any notes or comments about this document"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Upload Document</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Upload document button click event
        const uploadButtons = document.querySelectorAll('.upload-doc-btn');
        const modal = new bootstrap.Modal(document.getElementById('uploadDocumentModal'));
        
        uploadButtons.forEach(button => {
            button.addEventListener('click', function() {
                const docType = this.getAttribute('data-type');
                const docName = this.getAttribute('data-name');
                
                document.getElementById('document_type').value = docType;
                document.getElementById('document_type_name').value = docName;
                
                modal.show();
            });
        });
    });
</script>