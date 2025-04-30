<!-- Receipts Listing Page content -->
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4 shadow">
                <div class="card-header p-3 d-flex justify-content-between align-items-center bg-light">
                    <div>
                        <h5 class="mb-0 fw-bold text-primary">Official Receipts</h5>
                        <p class="text-sm text-muted mb-0">View and manage all generated receipts</p>
                    </div>
                    
                    <?php if(isset($_SESSION['user_role']) && ($_SESSION['user_role'] === 'admin' || $_SESSION['user_role'] === 'treasurer')): ?>
                    <div>
                        <a href="<?php echo URLROOT; ?>/treasurer/history" class="btn btn-primary">
                            <i class="bi bi-arrow-left"></i> Payment History
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
                
                <div class="card-body px-0 pt-0 pb-2">
                    <div class="px-4 pt-3">
                        <?php SessionHelper::displayFlashMessages(); ?>
                    </div>
                    
                    <!-- Search and Filter panel -->
                    <div class="px-4 pt-3 pb-3 mb-3 border-bottom bg-light bg-opacity-50">
                        <form id="filterForm" class="row g-3">
                            <div class="col-md-4 col-sm-6">
                                <label for="receiptNumberFilter" class="form-label text-xs text-uppercase text-muted fw-semibold">Receipt Number</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-white border-end-0">
                                        <i class="fas fa-hashtag text-primary"></i>
                                    </span>
                                    <input type="text" id="receiptNumberFilter" class="form-control form-control-sm border-start-0 ps-0 shadow-none" placeholder="Search receipt number...">
                                </div>
                            </div>
                            <div class="col-md-4 col-sm-6">
                                <label for="dateFilter" class="form-label text-xs text-uppercase text-muted fw-semibold">Date Range</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-white border-end-0">
                                        <i class="fas fa-calendar-alt text-primary"></i>
                                    </span>
                                    <select id="dateFilter" class="form-select form-select-sm border-start-0 ps-0 shadow-none">
                                        <option value="">All Time</option>
                                        <option value="today">Today</option>
                                        <option value="week">This Week</option>
                                        <option value="month">This Month</option>
                                        <option value="year">This Year</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4 col-sm-6">
                                <label for="businessFilter" class="form-label text-xs text-uppercase text-muted fw-semibold">Business Name</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-white border-end-0">
                                        <i class="fas fa-search text-primary"></i>
                                    </span>
                                    <input type="text" id="businessFilter" class="form-control form-control-sm border-start-0 ps-0 shadow-none" placeholder="Search business...">
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6 d-flex align-items-end">
                                <button type="button" id="clearFilters" class="btn btn-sm btn-outline-secondary w-50">
                                    <i class="bi bi-arrow-repeat me-1"></i> Clear Filters
                                </button>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Loading indicator -->
                    <div id="loadingIndicator" class="text-center py-5 d-none">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="text-muted mt-2">Loading receipt data...</p>
                    </div>
                    
                    <?php if(empty($data['receipts'])) : ?>
                        <div class="text-center p-5 my-4">
                            <div class="mb-3">
                                <i class="fas fa-receipt fa-4x text-secondary opacity-50"></i>
                            </div>
                            <h4 class="text-secondary mt-3">No receipts found</h4>
                            <p class="text-muted">No official receipts have been generated yet.</p>
                            <?php if(isset($_SESSION['user_role']) && ($_SESSION['user_role'] === 'admin' || $_SESSION['user_role'] === 'treasurer')): ?>
                            <a href="<?php echo URLROOT; ?>/treasurer/verify" class="btn btn-primary mt-2">
                                <i class="bi bi-check-circle"></i> Verify Payments
                            </a>
                            <?php endif; ?>
                        </div>
                    <?php else : ?>
                        <div class="table-responsive p-0">
                            <table class="table align-items-center mb-0 table-hover" id="receiptsTable">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-3">Receipt Number</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Business</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Amount</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Payment Method</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Issue Date</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2 text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($data['receipts'] as $receipt) : ?>
                                        <tr class="receipt-row" 
                                            data-receipt="<?php echo strtolower($receipt->receipt_number); ?>" 
                                            data-date="<?php echo date('Y-m-d', strtotime($receipt->receipt_generated_at)); ?>"
                                            data-business="<?php echo strtolower($receipt->business_name); ?>">
                                            <td>
                                                <div class="d-flex px-3 py-2">
                                                    <div class="icon-circle bg-gradient-success text-white me-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; border-radius: 50%;">
                                                        <i class="fas fa-receipt"></i>
                                                    </div>
                                                    <div class="d-flex flex-column justify-content-center">
                                                        <h6 class="mb-0 text-sm"><?php echo $receipt->receipt_number; ?></h6>
                                                        <p class="text-xs text-secondary mb-0">Ref: <?php echo $receipt->reference_number; ?></p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex flex-column">
                                                    <h6 class="mb-0 text-sm"><?php echo $receipt->business_name; ?></h6>
                                                    <p class="text-xs text-secondary mb-0"><?php echo $receipt->business_type; ?></p>
                                                </div>
                                            </td>
                                            <td>
                                                <p class="text-sm font-weight-bold mb-0 text-success">₱<?php echo number_format($receipt->amount, 2); ?></p>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <?php 
                                                        $methodIcon = 'fa-money-bill';
                                                        $methodClass = 'text-success';
                                                        
                                                        if(strtolower($receipt->payment_method) == 'gcash') {
                                                            $methodIcon = 'fa-mobile-alt';
                                                            $methodClass = 'text-info';
                                                        } elseif(strtolower($receipt->payment_method) == 'bank transfer') {
                                                            $methodIcon = 'fa-university';
                                                            $methodClass = 'text-primary';
                                                        }
                                                    ?>
                                                    <div class="icon-circle bg-light me-2 d-flex align-items-center justify-content-center" style="width: 28px; height: 28px; border-radius: 50%;">
                                                        <i class="fas <?php echo $methodIcon; ?> <?php echo $methodClass; ?> fa-sm"></i>
                                                    </div>
                                                    <p class="text-xs font-weight-bold mb-0"><?php echo $receipt->payment_method; ?></p>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex flex-column">
                                                    <p class="text-xs font-weight-bold mb-0"><?php echo date('M d, Y', strtotime($receipt->receipt_generated_at)); ?></p>
                                                    <p class="text-xs text-secondary mb-0"><?php echo date('h:i A', strtotime($receipt->receipt_generated_at)); ?></p>
                                                </div>
                                            </td>
                                            <td class="align-middle text-center">
                                                <div class="btn-group">
                                                    <a href="<?php echo URLROOT; ?>/treasurer/receipt/<?php echo $receipt->id; ?>" class="btn btn-sm btn-outline-primary" target="_blank" data-bs-toggle="tooltip" title="View Receipt">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="<?php echo URLROOT; ?>/treasurer/generateReceipt/<?php echo $receipt->id; ?>" class="btn btn-sm btn-outline-success" data-bs-toggle="tooltip" title="Download PDF">
                                                        <i class="fas fa-file-pdf"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-sm btn-outline-secondary print-receipt" data-id="<?php echo $receipt->id; ?>" data-bs-toggle="tooltip" title="Print Receipt">
                                                        <i class="fas fa-print"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- No results message (initially hidden) -->
                        <div id="noResultsMessage" class="text-center py-5 d-none">
                            <div class="mb-3">
                                <i class="fas fa-search fa-3x text-secondary opacity-50"></i>
                            </div>
                            <h5 class="text-secondary mt-3">No matching receipts found</h5>
                            <p class="text-muted">Try adjusting your filters to find what you're looking for.</p>
                            <button type="button" id="clearFiltersAlt" class="btn btn-sm btn-outline-primary mt-2">
                                <i class="fas fa-sync-alt me-1"></i> Clear Filters
                            </button>
                        </div>
                        
                        <!-- Pagination and counters -->
                        <div class="px-4 py-3 d-flex flex-wrap justify-content-between align-items-center border-top bg-light bg-opacity-50">
                            <div class="d-flex align-items-center mb-2 mb-md-0">
                                <select id="rowsPerPage" class="form-select form-select-sm me-2 shadow-none" style="width: auto;">
                                    <option value="10">10 rows</option>
                                    <option value="25">25 rows</option>
                                    <option value="50">50 rows</option>
                                    <option value="100">100 rows</option>
                                </select>
                                <p class="text-sm text-secondary mb-0">
                                    Showing <span id="visibleRows" class="fw-bold"><?php echo count($data['receipts']); ?></span> of 
                                    <span class="fw-bold total-count"><?php echo count($data['receipts']); ?></span> receipts
                                </p>
                            </div>
                            
                            <nav aria-label="Page navigation">
                                <ul class="pagination pagination-sm justify-content-end mb-0">
                                    <li class="page-item disabled" id="prevPage">
                                        <a class="page-link" href="#" tabindex="-1" aria-disabled="true">
                                            <i class="fas fa-chevron-left"></i> Previous
                                        </a>
                                    </li>
                                    <li class="page-item active"><a class="page-link" href="#">1</a></li>
                                    <li class="page-item disabled" id="nextPage">
                                        <a class="page-link" href="#">
                                            Next <i class="fas fa-chevron-right"></i>
                                        </a>
                                    </li>
                                </ul>
                            </nav>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript for filtering and interactions -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Elements
    const receiptNumberFilter = document.getElementById('receiptNumberFilter');
    const dateFilter = document.getElementById('dateFilter');
    const businessFilter = document.getElementById('businessFilter');
    const clearFiltersBtn = document.getElementById('clearFilters');
    const clearFiltersAltBtn = document.getElementById('clearFiltersAlt');
    const receiptRows = document.querySelectorAll('.receipt-row');
    const visibleRowsCount = document.getElementById('visibleRows');
    const totalCount = document.querySelector('.total-count');
    const noResultsMessage = document.getElementById('noResultsMessage');
    const loadingIndicator = document.getElementById('loadingIndicator');
    const receiptsTable = document.getElementById('receiptsTable');
    const rowsPerPageSelect = document.getElementById('rowsPerPage');
    
    // Pagination variables
    let rowsPerPage = rowsPerPageSelect ? parseInt(rowsPerPageSelect.value) : 10;
    let currentPage = 1;
    let filteredRows = [...receiptRows];
    
    function showLoading() {
        if (loadingIndicator) {
            loadingIndicator.classList.remove('d-none');
        }
        if (receiptsTable) {
            receiptsTable.closest('.table-responsive').classList.add('d-none');
        }
    }
    
    function hideLoading() {
        if (loadingIndicator) {
            loadingIndicator.classList.add('d-none');
        }
        if (receiptsTable) {
            receiptsTable.closest('.table-responsive').classList.remove('d-none');
        }
    }
    
    function applyFilters() {
        showLoading();
        
        // Small delay to show loading indicator (simulates processing time)
        setTimeout(() => {
            let visible = 0;
            filteredRows = [];
            
            receiptRows.forEach(row => {
                const receipt = row.getAttribute('data-receipt');
                const date = row.getAttribute('data-date');
                const business = row.getAttribute('data-business');
                
                let receiptMatch = true;
                let dateMatch = true;
                let businessMatch = true;
                
                // Receipt number filter
                if (receiptNumberFilter && receiptNumberFilter.value && 
                    !receipt.includes(receiptNumberFilter.value.toLowerCase())) {
                    receiptMatch = false;
                }
                
                // Date filter
                if (dateFilter && dateFilter.value) {
                    const today = new Date();
                    const receiptDate = new Date(date);
                    
                    if (dateFilter.value === 'today') {
                        if (receiptDate.toDateString() !== today.toDateString()) {
                            dateMatch = false;
                        }
                    } else if (dateFilter.value === 'week') {
                        const weekStart = new Date(today);
                        weekStart.setDate(today.getDate() - today.getDay());
                        if (receiptDate < weekStart) {
                            dateMatch = false;
                        }
                    } else if (dateFilter.value === 'month') {
                        if (receiptDate.getMonth() !== today.getMonth() || 
                            receiptDate.getFullYear() !== today.getFullYear()) {
                            dateMatch = false;
                        }
                    } else if (dateFilter.value === 'year') {
                        if (receiptDate.getFullYear() !== today.getFullYear()) {
                            dateMatch = false;
                        }
                    }
                }
                
                // Business filter
                if (businessFilter && businessFilter.value && 
                    !business.includes(businessFilter.value.toLowerCase())) {
                    businessMatch = false;
                }
                
                // Add to filtered rows if all filters match
                if (receiptMatch && dateMatch && businessMatch) {
                    filteredRows.push(row);
                    visible++;
                }
            });
            
            // Show/hide no results message
            if (noResultsMessage) {
                if (filteredRows.length === 0 && receiptRows.length > 0) {
                    noResultsMessage.classList.remove('d-none');
                    if (receiptsTable) {
                        receiptsTable.closest('.table-responsive').classList.add('d-none');
                    }
                } else {
                    noResultsMessage.classList.add('d-none');
                    if (receiptsTable && filteredRows.length > 0) {
                        receiptsTable.closest('.table-responsive').classList.remove('d-none');
                    }
                }
            }
            
            // Reset to first page after filtering
            currentPage = 1;
            
            // Apply pagination
            applyPagination();
            
            // Update visible count
            if (visibleRowsCount) {
                visibleRowsCount.textContent = visible;
            }
            
            hideLoading();
        }, 300); // 300ms delay to show loading state
    }
    
    function applyPagination() {
        // Calculate page bounds
        const startIndex = (currentPage - 1) * rowsPerPage;
        const endIndex = startIndex + rowsPerPage;
        
        // Hide all rows first
        receiptRows.forEach(row => {
            row.style.display = 'none';
        });
        
        // Show only rows for current page
        filteredRows.slice(startIndex, endIndex).forEach(row => {
            row.style.display = '';
        });
        
        // Update pagination controls
        updatePaginationControls();
    }
    
    function updatePaginationControls() {
        const totalPages = Math.ceil(filteredRows.length / rowsPerPage);
        const prevPageBtn = document.getElementById('prevPage');
        const nextPageBtn = document.getElementById('nextPage');
        const currentPageDisplay = document.querySelector('.page-item.active .page-link');
        
        // Enable/disable previous button
        if (currentPage === 1) {
            prevPageBtn.classList.add('disabled');
            prevPageBtn.querySelector('a').setAttribute('aria-disabled', 'true');
        } else {
            prevPageBtn.classList.remove('disabled');
            prevPageBtn.querySelector('a').setAttribute('aria-disabled', 'false');
        }
        
        // Enable/disable next button
        if (currentPage >= totalPages) {
            nextPageBtn.classList.add('disabled');
            nextPageBtn.querySelector('a').setAttribute('aria-disabled', 'true');
        } else {
            nextPageBtn.classList.remove('disabled');
            nextPageBtn.querySelector('a').setAttribute('aria-disabled', 'false');
        }
        
        // Update page indicator
        if (currentPageDisplay) {
            currentPageDisplay.textContent = currentPage;
        }
        
        // Update counts
        if (visibleRowsCount) {
            const displayedCount = Math.min(filteredRows.length - (currentPage - 1) * rowsPerPage, rowsPerPage);
            visibleRowsCount.textContent = filteredRows.length === 0 ? 0 : displayedCount;
        }
    }
    
    // Add event listeners to filters
    if (receiptNumberFilter) {
        receiptNumberFilter.addEventListener('input', function() {
            // Add debounce for search input
            clearTimeout(this.debounceTimer);
            this.debounceTimer = setTimeout(applyFilters, 300);
        });
    }
    
    if (dateFilter) {
        dateFilter.addEventListener('change', applyFilters);
    }
    
    if (businessFilter) {
        businessFilter.addEventListener('input', function() {
            // Add debounce for search input
            clearTimeout(this.debounceTimer);
            this.debounceTimer = setTimeout(applyFilters, 300);
        });
    }
    
    // Rows per page change
    if (rowsPerPageSelect) {
        rowsPerPageSelect.addEventListener('change', function() {
            rowsPerPage = parseInt(this.value);
            currentPage = 1;
            applyPagination();
        });
    }
    
    // Clear filters
    if (clearFiltersBtn) {
        clearFiltersBtn.addEventListener('click', function() {
            if (receiptNumberFilter) receiptNumberFilter.value = '';
            if (dateFilter) dateFilter.value = '';
            if (businessFilter) businessFilter.value = '';
            applyFilters();
        });
    }
    
    // Alternative clear filters button
    if (clearFiltersAltBtn) {
        clearFiltersAltBtn.addEventListener('click', function() {
            if (receiptNumberFilter) receiptNumberFilter.value = '';
            if (dateFilter) dateFilter.value = '';
            if (businessFilter) businessFilter.value = '';
            applyFilters();
        });
    }
    
    // Pagination navigation
    document.getElementById('prevPage').addEventListener('click', function(e) {
        e.preventDefault();
        if (currentPage > 1) {
            currentPage--;
            applyPagination();
        }
    });
    
    document.getElementById('nextPage').addEventListener('click', function(e) {
        e.preventDefault();
        const totalPages = Math.ceil(filteredRows.length / rowsPerPage);
        if (currentPage < totalPages) {
            currentPage++;
            applyPagination();
        }
    });
    
    // Print receipt functionality
    const printButtons = document.querySelectorAll('.print-receipt');
    if (printButtons.length > 0) {
        printButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const paymentId = this.getAttribute('data-id');
                // Open the receipt in a new window/tab for printing
                window.open(`<?php echo URLROOT; ?>/treasurer/receipt/${paymentId}`, '_blank');
            });
        });
    }
    
    // Initialize tooltips if Bootstrap is available
    if (typeof bootstrap !== 'undefined') {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }
    
    // Initialize with all records
    applyPagination();
});
</script>

<!-- Add some custom styles for enhanced UI -->
<style>
.transition-all {
    transition: all 0.2s ease;
}
.table-hover tr:hover {
    background-color: rgba(0, 123, 255, 0.03);
}
.icon-circle {
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.05);
}
.badge {
    font-weight: 500;
    letter-spacing: 0.3px;
}
.page-link {
    border-radius: 0.25rem;
    margin: 0 2px;
}
.btn-group .btn {
    border-radius: 0.25rem !important;
    margin: 0 2px;
}
.form-control:focus, .form-select:focus {
    border-color: #80bdff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.15);
}
.input-group-text {
    border-right: 0;
}
@media (max-width: 768px) {
    .pagination .page-link {
        padding: 0.25rem 0.5rem;
    }
    .btn-group .btn {
        padding: 0.25rem 0.5rem;
    }
}
</style>