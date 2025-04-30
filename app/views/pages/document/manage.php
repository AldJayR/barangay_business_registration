<?php
// View file for document management (admin/treasurer)
?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mb-0"><i class="bi bi-file-earmark-check me-2"></i>Document Verification</h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="<?= URLROOT ?>/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Document Verification</li>
                </ol>
            </nav>
        </div>
    </div>

    <?php SessionHelper::displayFlashMessages(); ?>

    <!-- Pending Documents Card -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <h5 class="card-title mb-0"><i class="bi bi-hourglass-split me-2"></i>Pending Document Verifications</h5>
        </div>
        <div class="card-body p-0">
            <?php if (empty($documents)): ?>
                <div class="p-4 text-center">
                    <i class="bi bi-check-circle text-success fs-1"></i>
                    <p class="mt-3 mb-0">No pending documents to verify</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">Document</th>
                                <th scope="col">Business</th>
                                <th scope="col">Uploaded By</th>
                                <th scope="col">Upload Date</th>
                                <th scope="col" class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $count = 1; ?>
                            <?php foreach ($documents as $document): ?>
                                <tr>
                                    <td><?= $count++ ?></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <?php
                                            $fileExt = pathinfo($document->file_path, PATHINFO_EXTENSION);
                                            $fileIcon = 'file-earmark';
                                            
                                            switch (strtolower($fileExt)) {
                                                case 'pdf':
                                                    $fileIcon = 'file-earmark-pdf';
                                                    break;
                                                case 'jpg':
                                                case 'jpeg':
                                                case 'png':
                                                case 'gif':
                                                    $fileIcon = 'file-earmark-image';
                                                    break;
                                            }
                                            ?>
                                            <div class="bg-light rounded p-2 me-2">
                                                <i class="bi bi-<?= $fileIcon ?>"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-0"><?= htmlspecialchars($document->document_name) ?></h6>
                                                <small class="text-muted"><?= basename($document->file_path) ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <h6 class="mb-0"><?= htmlspecialchars($document->business_name) ?></h6>
                                            <small class="text-muted"><?= htmlspecialchars($document->business_type) ?></small>
                                        </div>
                                    </td>
                                    <td><?= htmlspecialchars($document->owner_name) ?></td>
                                    <td><?= date('M d, Y', strtotime($document->uploaded_at)) ?></td>
                                    <td class="text-end">
                                        <div class="btn-group">
                                            <a href="<?= URLROOT ?>/document/viewDocument/<?= $document->id ?>" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-eye me-1"></i> View
                                            </a>
                                            <button type="button" class="btn btn-sm btn-outline-success approve-btn" data-id="<?= $document->id ?>" data-name="<?= htmlspecialchars($document->document_name) ?>">
                                                <i class="bi bi-check-circle me-1"></i> Approve
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-danger reject-btn" data-id="<?= $document->id ?>" data-name="<?= htmlspecialchars($document->document_name) ?>">
                                                <i class="bi bi-x-circle me-1"></i> Reject
                                            </button>
                                        </div>
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

<!-- Approve Modal -->
<div class="modal fade" id="approveModal" tabindex="-1" aria-labelledby="approveModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?= URLROOT ?>/document/verify/" method="POST" id="approveForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="approveModalLabel">Approve Document</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="status" value="Approved">
                    <p>Are you sure you want to approve this <span id="approveDocName"></span>?</p>
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        Approving this document confirms that it meets all requirements and is valid.
                    </div>
                    <div class="mb-3">
                        <label for="approve-notes" class="form-label">Notes (Optional)</label>
                        <textarea class="form-control" id="approve-notes" name="notes" rows="3" placeholder="Add any verification notes or comments"></textarea>
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
            <form action="<?= URLROOT ?>/document/verify/" method="POST" id="rejectForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="rejectModalLabel">Reject Document</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="status" value="Rejected">
                    <p>Are you sure you want to reject this <span id="rejectDocName"></span>?</p>
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        Please provide a reason for rejection to help the business owner understand what needs to be corrected.
                    </div>
                    <div class="mb-3">
                        <label for="reject-notes" class="form-label">Reason for Rejection <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="reject-notes" name="notes" rows="3" placeholder="Explain why this document is being rejected" required></textarea>
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

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Handle approve button clicks
        const approveButtons = document.querySelectorAll('.approve-btn');
        const approveModal = new bootstrap.Modal(document.getElementById('approveModal'));
        const approveForm = document.getElementById('approveForm');
        const approveDocName = document.getElementById('approveDocName');
        
        approveButtons.forEach(button => {
            button.addEventListener('click', function() {
                const docId = this.getAttribute('data-id');
                const docName = this.getAttribute('data-name');
                
                approveForm.action = '<?= URLROOT ?>/document/verify/' + docId;
                approveDocName.textContent = docName.toLowerCase();
                
                approveModal.show();
            });
        });
        
        // Handle reject button clicks
        const rejectButtons = document.querySelectorAll('.reject-btn');
        const rejectModal = new bootstrap.Modal(document.getElementById('rejectModal'));
        const rejectForm = document.getElementById('rejectForm');
        const rejectDocName = document.getElementById('rejectDocName');
        
        rejectButtons.forEach(button => {
            button.addEventListener('click', function() {
                const docId = this.getAttribute('data-id');
                const docName = this.getAttribute('data-name');
                
                rejectForm.action = '<?= URLROOT ?>/document/verify/' + docId;
                rejectDocName.textContent = docName.toLowerCase();
                
                rejectModal.show();
            });
        });
    });
</script>