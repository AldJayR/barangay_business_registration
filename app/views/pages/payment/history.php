<!-- Payment History content -->
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4 shadow">
                <div class="card-header p-3 d-flex justify-content-between align-items-center bg-light">
                    <div>
                        <h5 class="mb-0 fw-bold text-primary">Payment History</h5>
                        <p class="text-sm text-muted mb-0">View and manage all payment transactions</p>
                    </div>
                    
                    <?php if(isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'business_owner'): ?>
                    <div>
                        <a href="<?php echo URLROOT; ?>/owner/dashboard" class="btn btn-primary">
                            <i class="bi bi-plus"></i> Make New Payment
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
                            <div class="col-md-3 col-sm-6">
                                <label for="statusFilter" class="form-label text-xs text-uppercase text-muted fw-semibold">Payment Status</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-white border-end-0">
                                        <i class="bi bi-funnel-fill text-primary"></i>
                                    </span>
                                    <select id="statusFilter" class="form-select form-select-sm border-start-0 ps-0 shadow-none">
                                        <option value="">All Statuses</option>
                                        <option value="Pending">Pending</option>
                                        <option value="Verified">Verified</option>
                                        <option value="Rejected">Rejected</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <label for="dateFilter" class="form-label text-xs text-uppercase text-muted fw-semibold">Date Range</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-white border-end-0">
                                        <i class="bi bi-calendar-event text-primary"></i>
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
                            <?php if(isset($_SESSION['user_role']) && ($_SESSION['user_role'] === 'admin' || $_SESSION['user_role'] === 'treasurer')): ?>
                            <div class="col-md-3 col-sm-6">
                                <label for="businessFilter" class="form-label text-xs text-uppercase text-muted fw-semibold">Business Name</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-white border-end-0">
                                        <i class="bi bi-search text-primary"></i>
                                    </span>
                                    <input type="text" id="businessFilter" class="form-control form-control-sm border-start-0 ps-0 shadow-none" placeholder="Search business...">
                                </div>
                            </div>
                            <?php endif; ?>
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
                        <p class="text-muted mt-2">Loading payment data...</p>
                    </div>
                    
                    <?php if(empty($data['payments'])) : ?>
                        <div class="text-center p-5 my-4">

                            <h4 class="text-secondary mt-3">No payment records found</h4>
                            <p class="text-muted">No payment transactions have been recorded yet.</p>
                            <?php if(isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'business_owner'): ?>
                            <a href="<?php echo URLROOT; ?>/owner/dashboard" class="btn btn-primary mt-2">
                                <i class="bi bi-plus"></i> Make a Payment
                            </a>
                            <?php endif; ?>
                        </div>
                    <?php else : ?>
                        <div class="table-responsive p-0">
                            <table class="table align-items-center mb-0 table-hover" id="paymentsTable">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-3">Business</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Reference Number</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Amount</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Method</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Date</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Status</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2 text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($data['payments'] as $payment) : ?>
                                        <tr class="payment-row" 
                                            data-status="<?php echo $payment->payment_status; ?>" 
                                            data-date="<?php echo date('Y-m-d', strtotime($payment->payment_date)); ?>"
                                            data-business="<?php echo strtolower($payment->business_name); ?>">
                                            <td>
                                                <div class="d-flex px-3 py-2">
                                                    <div class="avatar-circle bg-gradient-primary text-white me-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; border-radius: 50%;">
                                                        <span class="fw-bold"><?php echo substr($payment->business_name, 0, 1); ?></span>
                                                    </div>
                                                    <div class="d-flex flex-column justify-content-center">
                                                        <h6 class="mb-0 text-sm"><?php echo $payment->business_name; ?></h6>
                                                        <p class="text-xs text-secondary mb-0">ID: <?php echo $payment->business_id; ?></p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <p class="text-xs font-weight-bold mb-0"><?php echo $payment->reference_number; ?></p>
                                            </td>
                                            <td>
                                                <p class="text-xs font-weight-bold mb-0">₱<?php echo number_format($payment->amount, 2); ?></p>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <?php 
                                                        $methodIcon = 'bi-cash';
                                                        $methodClass = 'text-success';
                                                        
                                                        if(strtolower($payment->payment_method) == 'gcash') {
                                                            $methodIcon = 'bi-phone';
                                                            $methodClass = 'text-info';
                                                        } elseif(strtolower($payment->payment_method) == 'bank transfer') {
                                                            $methodIcon = 'bi-bank';
                                                            $methodClass = 'text-primary';
                                                        }
                                                    ?>
                                                    <div class="icon-circle bg-light me-2 d-flex align-items-center justify-content-center" style="width: 28px; height: 28px; border-radius: 50%;">
                                                        <i class="bi <?php echo $methodIcon; ?> <?php echo $methodClass; ?> fa-sm"></i>
                                                    </div>
                                                    <p class="text-xs font-weight-bold mb-0"><?php echo $payment->payment_method; ?></p>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex flex-column">
                                                    <p class="text-xs font-weight-bold mb-0"><?php echo date('M d, Y', strtotime($payment->payment_date)); ?></p>
                                                    <p class="text-xs text-secondary mb-0"><?php echo date('h:i A', strtotime($payment->created_at)); ?></p>
                                                </div>
                                            </td>
                                            <td>
                                                <?php 
                                                    $statusClass = 'bg-warning bg-opacity-10 text-warning';
                                                    $statusIcon = 'bi-clock';
                                                    
                                                    if(strtolower($payment->payment_status) == 'verified') {
                                                        $statusClass = 'bg-success bg-opacity-10 text-success';
                                                        $statusIcon = 'bi-check-circle';
                                                    } elseif(strtolower($payment->payment_status) == 'rejected') {
                                                        $statusClass = 'bg-danger bg-opacity-10 text-danger';
                                                        $statusIcon = 'bi-x-circle';
                                                    }
                                                ?>
                                                <span class="badge <?php echo $statusClass; ?> rounded-pill px-3 py-2">
                                                    <i class="bi <?php echo $statusIcon; ?> me-1"></i>
                                                    <?php echo $payment->payment_status; ?>
                                                </span>
                                            </td>
                                            <td class="align-middle text-center">
                                                <div class="btn-group">
                                                    <!-- Print Receipt Button (Verified payments only) -->
                                                    <?php if(strtolower($payment->payment_status) == 'verified') : ?>
                                                    <a href="#" class="btn btn-sm btn-outline-primary print-receipt" data-id="<?php echo $payment->id; ?>" title="Print Receipt">
                                                        <i class="bi bi-printer me-1"></i><span class="d-none d-lg-inline">Print Receipt</span>
                                                    </a>
                                                    <?php endif; ?>
                                                    
                                                    <!-- More Options Dropdown for additional actions -->
                                                    <?php if((strtolower($payment->payment_status) == 'pending' && ($_SESSION['user_role'] === 'business_owner')) || 
                                                             (strtolower($payment->payment_status) == 'pending' && (($_SESSION['user_role'] === 'admin') || ($_SESSION['user_role'] === 'treasurer')))) : ?>
                                                    <div class="dropdown">
                                                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                                            <i class="bi bi-three-dots"></i>
                                                        </button>
                                                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                                                            <?php if(strtolower($payment->payment_status) == 'pending' && ($_SESSION['user_role'] === 'business_owner')) : ?>
                                                            <li>
                                                                <a href="<?php echo URLROOT; ?>/payment/cancel/<?php echo $payment->id; ?>" class="dropdown-item py-2 px-3 text-danger" 
                                                                   onclick="return confirm('Are you sure you want to cancel this payment?');">
                                                                    <i class="bi bi-x me-2"></i> Cancel Payment
                                                                </a>
                                                            </li>
                                                            <?php endif; ?>
                                                            <?php if(strtolower($payment->payment_status) == 'pending' && (($_SESSION['user_role'] === 'admin') || ($_SESSION['user_role'] === 'treasurer'))) : ?>
                                                            <li>
                                                                <form method="post" action="<?php echo URLROOT; ?>/payment/verify/<?php echo $payment->id; ?>" style="display:inline;">
                                                                    <input type="hidden" name="status" value="Verified">
                                                                    <button type="submit" class="dropdown-item py-2 px-3 text-success" onclick="return confirm('Approve this payment?');">
                                                                        <i class="bi bi-check-circle me-2"></i> Approve
                                                                    </button>
                                                                </form>
                                                            </li>
                                                            <li>
                                                                <form method="post" action="<?php echo URLROOT; ?>/payment/verify/<?php echo $payment->id; ?>" style="display:inline;">
                                                                    <input type="hidden" name="status" value="Rejected">
                                                                    <button type="submit" class="dropdown-item py-2 px-3 text-danger" onclick="return confirm('Reject this payment?');">
                                                                        <i class="bi bi-x-circle me-2"></i> Reject
                                                                    </button>
                                                                </form>
                                                            </li>
                                                            <?php endif; ?>
                                                        </ul>
                                                    </div>
                                                    <?php endif; ?>
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
                                <i class="bi bi-search fa-3x text-secondary opacity-50"></i>
                            </div>
                            <h5 class="text-secondary mt-3">No matching payments found</h5>
                            <p class="text-muted">Try adjusting your filters to find what you're looking for.</p>
                            <button type="button" id="clearFiltersAlt" class="btn btn-sm btn-outline-primary mt-2">
                                <i class="bi bi-arrow-repeat me-1"></i> Clear Filters
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
                                    Showing <span id="visibleRows" class="fw-bold"><?php echo count($data['payments']); ?></span> of 
                                    <span class="fw-bold total-count"><?php echo count($data['payments']); ?></span> payments
                                </p>
                            </div>
                            
                            <nav aria-label="Page navigation">
                                <ul class="pagination pagination-sm justify-content-end mb-0">
                                    <li class="page-item disabled" id="prevPage">
                                        <a class="page-link" href="#" tabindex="-1" aria-disabled="true">
                                            <i class="bi bi-chevron-left"></i> Previous
                                        </a>
                                    </li>
                                    <li class="page-item active"><a class="page-link" href="#">1</a></li>
                                    <li class="page-item disabled" id="nextPage">
                                        <a class="page-link" href="#">
                                            Next <i class="bi bi-chevron-right"></i>
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
    const statusFilter = document.getElementById('statusFilter');
    const dateFilter = document.getElementById('dateFilter');
    const businessFilter = document.getElementById('businessFilter');
    const clearFiltersBtn = document.getElementById('clearFilters');
    const clearFiltersAltBtn = document.getElementById('clearFiltersAlt');
    const paymentRows = document.querySelectorAll('.payment-row');
    const visibleRowsCount = document.getElementById('visibleRows');
    const totalCount = document.querySelector('.total-count');
    const noResultsMessage = document.getElementById('noResultsMessage');
    const loadingIndicator = document.getElementById('loadingIndicator');
    const paymentsTable = document.getElementById('paymentsTable');
    const rowsPerPageSelect = document.getElementById('rowsPerPage');
    
    // Pagination variables
    let rowsPerPage = rowsPerPageSelect ? parseInt(rowsPerPageSelect.value) : 10;
    let currentPage = 1;
    let filteredRows = [...paymentRows];
    
    function showLoading() {
        if (loadingIndicator) {
            loadingIndicator.classList.remove('d-none');
        }
        if (paymentsTable) {
            paymentsTable.closest('.table-responsive').classList.add('d-none');
        }
    }
    
    function hideLoading() {
        if (loadingIndicator) {
            loadingIndicator.classList.add('d-none');
        }
        if (paymentsTable) {
            paymentsTable.closest('.table-responsive').classList.remove('d-none');
        }
    }
    
    function applyFilters() {
        showLoading();
        
        // Small delay to show loading indicator (simulates processing time)
        setTimeout(() => {
            let visible = 0;
            filteredRows = [];
            
            paymentRows.forEach(row => {
                const status = row.getAttribute('data-status');
                const date = row.getAttribute('data-date');
                const business = row.getAttribute('data-business');
                
                let statusMatch = true;
                let dateMatch = true;
                let businessMatch = true;
                
                // Status filter
                if (statusFilter && statusFilter.value && status !== statusFilter.value) {
                    statusMatch = false;
                }
                
                // Date filter
                if (dateFilter && dateFilter.value) {
                    const today = new Date();
                    const paymentDate = new Date(date);
                    
                    if (dateFilter.value === 'today') {
                        if (paymentDate.toDateString() !== today.toDateString()) {
                            dateMatch = false;
                        }
                    } else if (dateFilter.value === 'week') {
                        const weekStart = new Date(today);
                        weekStart.setDate(today.getDate() - today.getDay());
                        if (paymentDate < weekStart) {
                            dateMatch = false;
                        }
                    } else if (dateFilter.value === 'month') {
                        if (paymentDate.getMonth() !== today.getMonth() || 
                            paymentDate.getFullYear() !== today.getFullYear()) {
                            dateMatch = false;
                        }
                    } else if (dateFilter.value === 'year') {
                        if (paymentDate.getFullYear() !== today.getFullYear()) {
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
                if (statusMatch && dateMatch && businessMatch) {
                    filteredRows.push(row);
                    visible++;
                }
            });
            
            // Show/hide no results message
            if (noResultsMessage) {
                if (filteredRows.length === 0 && paymentRows.length > 0) {
                    noResultsMessage.classList.remove('d-none');
                    if (paymentsTable) {
                        paymentsTable.closest('.table-responsive').classList.add('d-none');
                    }
                } else {
                    noResultsMessage.classList.add('d-none');
                    if (paymentsTable && filteredRows.length > 0) {
                        paymentsTable.closest('.table-responsive').classList.remove('d-none');
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
        paymentRows.forEach(row => {
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
    if (statusFilter) {
        statusFilter.addEventListener('change', applyFilters);
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
            if (statusFilter) statusFilter.value = '';
            if (dateFilter) dateFilter.value = '';
            if (businessFilter) businessFilter.value = '';
            applyFilters();
        });
    }
    
    // Alternative clear filters button
    if (clearFiltersAltBtn) {
        clearFiltersAltBtn.addEventListener('click', function() {
            if (statusFilter) statusFilter.value = '';
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
                window.open(`<?php echo URLROOT; ?>/payment/receipt/${paymentId}`, '_blank');
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
    
    // Add hover effect on rows
    paymentRows.forEach(row => {
        row.classList.add('transition-all');
    });
    
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
.avatar-circle {
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
}
.icon-circle {
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.05);
}
.badge {
    font-weight: 500;
    letter-spacing: 0.3px;
}
.dropdown-menu {
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
    border-radius: 0.5rem;
}
.dropdown-item:hover {
    background-color: rgba(0, 123, 255, 0.05);
}
.dropdown-item:active {
    background-color: rgba(0, 123, 255, 0.1);
    color: inherit;
}
.page-link {
    border-radius: 0.25rem;
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
}
</style>