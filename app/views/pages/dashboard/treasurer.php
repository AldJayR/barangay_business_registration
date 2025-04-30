<?php
// View loaded within 'app' layout
// Access $data['title'], $data['payments'] (when implemented)
$payments = $data['payments'] ?? []; // Ensure $payments is an array
?>

<!-- Dashboard Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="mb-0"><i class="bi bi-cash-coin me-2"></i>Treasurer Dashboard</h3>
    <div>
        <span class="badge bg-dark p-2">
            <i class="bi bi-calendar-event me-1"></i> <?= date('F d, Y') ?>
        </span>
    </div>
</div>

<!-- Stats Cards Row -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card shadow">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted fw-normal mb-0">Pending Verifications</h6>
                        <h3 class="fw-bold mb-0"><?= isset($data['stats']) ? $data['stats']['pending'] ?? 0 : 0 ?></h3>
                    </div>
                    <div class="bg-warning-soft p-3 rounded">
                        <i class="bi bi-hourglass-split text-warning fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted fw-normal mb-0">Today's Collections</h6>
                        <h3 class="fw-bold mb-0">₱<?= isset($data['stats']) ? number_format($data['stats']['today'] ?? 0, 2) : '0.00' ?></h3>
                    </div>
                    <div class="bg-success-soft p-3 rounded">
                        <i class="bi bi-cash-stack text-success fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted fw-normal mb-0">Monthly Revenue</h6>
                        <h3 class="fw-bold mb-0">₱<?= isset($data['stats']) ? number_format($data['stats']['monthly'] ?? 0, 2) : '0.00' ?></h3>
                    </div>
                    <div class="bg-primary-soft p-3 rounded">
                        <i class="bi bi-graph-up-arrow text-primary fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Payments Card -->
<div class="card shadow mb-4">
    <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
        <h5 class="mb-0"><i class="bi bi-credit-card-2-front me-2"></i>Pending Payment Verifications</h5>
        <div class="btn-group">
            <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-filter me-1"></i> Filter
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="#">All Payments</a></li>
                <li><a class="dropdown-item" href="#">This Week</a></li>
                <li><a class="dropdown-item" href="#">This Month</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="#">New Applications</a></li>
                <li><a class="dropdown-item" href="#">Renewals</a></li>
            </ul>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th scope="col">Payment ID</th>
                        <th scope="col">Business Details</th>
                        <th scope="col">Amount</th>
                        <th scope="col">Date Uploaded</th>
                        <th scope="col" class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($payments)): ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">No pending payments found</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($payments as $payment): ?>
                            <tr>
                                <td><?= htmlspecialchars($payment->reference_number) ?></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="bg-light rounded-circle p-2 me-2">
                                            <i class="bi bi-shop"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0"><?= htmlspecialchars($payment->business_name) ?></h6>
                                            <small class="text-muted"><?= htmlspecialchars($payment->owner_name ?? '') ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td>₱<?= number_format($payment->amount, 2) ?></td>
                                <td><?= date('Y-m-d', strtotime($payment->created_at)) ?></td>
                                <td class="text-end">
                                    <div class="d-flex justify-content-end gap-1">
                                        <button type="button" class="btn btn-sm btn-outline-primary view-proof-btn" data-action="openProofModal" data-payment-id="<?= $payment->id ?>" title="View Payment Proof">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <form method="post" action="<?= URLROOT ?>/payment/verify/<?= $payment->id ?>" style="display:inline;">
                                            <input type="hidden" name="status" value="Verified">
                                            <button type="submit" class="btn btn-sm btn-outline-success" data-bs-toggle="tooltip" title="Approve Payment" onclick="return confirm('Approve this payment?');">
                                                <i class="bi bi-check-lg"></i>
                                            </button>
                                        </form>
                                        <form method="post" action="<?= URLROOT ?>/payment/verify/<?= $payment->id ?>" style="display:inline;">
                                            <input type="hidden" name="status" value="Rejected">
                                            <button type="submit" class="btn btn-sm btn-outline-danger" data-bs-toggle="tooltip" title="Reject Payment" onclick="return confirm('Reject this payment?');">
                                                <i class="bi bi-x-lg"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer bg-white d-flex justify-content-between align-items-center py-3">
        <span class="text-muted small">Showing recent payment submissions</span>
    </div>
</div>

<!-- Recent Activity / Revenue Chart Card -->
<div class="card shadow mb-4">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-activity me-2"></i>Revenue Overview</h5>
        <div>
            <select id="chartPeriodSelector" class="form-select form-select-sm" style="width: auto;">
                <option value="6">Last 6 Months</option>
                <option value="12" selected>Last 12 Months</option>
                <option value="24">Last 24 Months</option>
            </select>
        </div>
    </div>
    <div class="card-body">
        <div class="revenue-chart-container" style="height: 300px;">
            <canvas id="revenueChart"></canvas>
        </div>
    </div>
    <div class="card-footer bg-white d-flex justify-content-between py-2">
        <div class="d-flex align-items-center">
            <span class="badge bg-primary me-2"></span>
            <small class="text-muted">Monthly Collections</small>
        </div>
        <div class="d-flex align-items-center">
            <span class="badge bg-success me-2"></span>
            <small class="text-muted">Target</small>
        </div>
        <div class="d-flex align-items-center">
            <span class="badge bg-info me-2"></span>
            <small class="text-muted">Year Average</small>
        </div>
    </div>
</div>

<!-- Load Chart.js library -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.3.0/dist/chart.umd.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Sample monthly revenue data for the chart
    // This should ideally come from your backend
    const monthlyData = {
        labels: [
            <?php
            // Generate labels for the last 12 months
            for ($i = 11; $i >= 0; $i--) {
                echo "'" . date('M Y', strtotime("-$i months")) . "',";
            }
            ?>
        ],
        datasets: [
            {
                label: 'Monthly Revenue',
                data: [
                    <?php
                    // This should be replaced with actual data from your database
                    // For now we'll use sample data
                    $sampleData = [];
                    for ($i = 0; $i < 12; $i++) {
                        // Generate realistic looking random data (higher for more recent months)
                        $baseValue = 5000 + ($i * 500);
                        $randomVariation = mt_rand(-1000, 2000);
                        $sampleData[] = $baseValue + $randomVariation;
                    }
                    echo implode(',', $sampleData);
                    ?>
                ],
                backgroundColor: 'rgba(13, 110, 253, 0.1)',
                borderColor: 'rgba(13, 110, 253, 1)',
                borderWidth: 2,
                tension: 0.3,
                fill: true
            },
            {
                label: 'Target',
                data: [8000, 8000, 8000, 8000, 8000, 8000, 8000, 8000, 8000, 8000, 8000, 8000],
                borderColor: 'rgba(25, 135, 84, 0.7)',
                borderWidth: 2,
                borderDash: [5, 5],
                fill: false,
                tension: 0
            }
        ]
    };

    // Calculate average for yearly average line
    const revenueValues = monthlyData.datasets[0].data;
    const averageValue = revenueValues.reduce((a, b) => a + b, 0) / revenueValues.length;
    
    // Add average dataset
    monthlyData.datasets.push({
        label: 'Yearly Average',
        data: Array(12).fill(averageValue),
        borderColor: 'rgba(13, 202, 240, 0.7)',
        borderWidth: 2,
        borderDash: [3, 3],
        fill: false,
        tension: 0
    });

    // Initialize the chart
    const ctx = document.getElementById('revenueChart').getContext('2d');
    const revenueChart = new Chart(ctx, {
        type: 'line',
        data: monthlyData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: {
                    grid: {
                        display: false
                    }
                },
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '₱' + value.toLocaleString();
                        }
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (context.parsed.y !== null) {
                                label += '₱' + context.parsed.y.toLocaleString();
                            }
                            return label;
                        }
                    }
                }
            },
            interaction: {
                intersect: false,
                mode: 'index'
            }
        }
    });

    // Period selector functionality
    document.getElementById('chartPeriodSelector').addEventListener('change', function() {
        const months = parseInt(this.value);
        // This would typically fetch new data via AJAX
        // For demo purposes, we'll just update the visible months
        
        // Update labels
        const newLabels = [];
        for (let i = months - 1; i >= 0; i--) {
            newLabels.push(getMonthLabel(i));
        }
        revenueChart.data.labels = newLabels;
        
        // Update data (in a real app, you'd fetch this from the server)
        // Here we're just adjusting the visible portion of existing data
        const newData = [];
        const targetData = [];
        for (let i = 0; i < months; i++) {
            // Generate data that looks somewhat realistic
            const baseValue = 5000 + (i * 300);
            const randomVariation = Math.floor(Math.random() * 3000) - 1000;
            newData.push(baseValue + randomVariation);
            targetData.push(8000); // Constant target
        }
        
        revenueChart.data.datasets[0].data = newData;
        revenueChart.data.datasets[1].data = targetData;
        
        // Calculate and update the new average line
        const newAverage = newData.reduce((a, b) => a + b, 0) / newData.length;
        revenueChart.data.datasets[2].data = Array(months).fill(newAverage);
        
        revenueChart.update();
    });

    // Helper function to get month labels
    function getMonthLabel(monthsAgo) {
        const date = new Date();
        date.setMonth(date.getMonth() - monthsAgo);
        return date.toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
    }
});
</script>

<!-- Quick Actions -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="d-flex flex-wrap gap-2">
            <a href="<?= URLROOT ?>/treasurer/history" class="btn btn-primary">
                <i class="bi bi-clock-history me-1"></i> View Payment History
            </a>
            <a href="<?= URLROOT ?>/treasurer/reports" class="btn btn-outline-dark">
                <i class="bi bi-file-earmark-bar-graph me-1"></i> Generate Reports
            </a>
            <a href="<?= URLROOT ?>/treasurer/settings" class="btn btn-outline-secondary">
                <i class="bi bi-gear me-1"></i> Payment Settings
            </a>
        </div>
    </div>
</div>

<!-- Initialize tooltips -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    })
});
</script>

<!-- Custom Modal Container -->
<div id="customModalContainer" class="custom-modal-container">
    <div id="customModalBackdrop" class="custom-modal-backdrop"></div>
    <!-- Proof Modal Template -->
    <div id="proofModalTemplate" class="custom-modal custom-modal-lg">
        <div class="custom-modal-content">
            <div class="custom-modal-header">
                <h5 class="custom-modal-title"><i class="bi bi-file-earmark-image me-2"></i>Payment Proof</h5>
                <button type="button" class="btn-close custom-close" data-action="closeModal"></button>
            </div>
            <div class="custom-modal-body"></div>
            <div class="custom-modal-footer">
                
            </div>
        </div>
    </div>
    <!-- Approve Modal Template -->
    <div id="approveModalTemplate" class="custom-modal">
        <div class="custom-modal-content">
            <form id="approveForm" action="" method="post">
                <div class="custom-modal-header">
                    <h5 class="custom-modal-title"><i class="bi bi-check-circle text-success me-2"></i>Approve Payment</h5>
                    <button type="button" class="btn-close custom-close" data-action="closeModal"></button>
                </div>
                <div class="custom-modal-body">
                    <div class="alert alert-info"><i class="bi bi-info-circle me-2"></i>Approving this payment will activate the business permit.</div>
                    <input type="hidden" name="status" value="Verified">
                    <div class="mb-3"><label for="notes-approve" class="form-label">Notes (Optional)</label><textarea class="form-control" id="notes-approve" name="notes" rows="3" placeholder="Add any verification notes or comments"></textarea></div>
                    <div class="payment-details"></div>
                </div>
                <div class="custom-modal-footer"><button type="button" class="btn btn-secondary" data-action="closeModal"><i class="bi bi-x me-1"></i>Cancel</button><button type="submit" class="btn btn-success"><i class="bi bi-check-lg me-1"></i>Approve Payment</button></div>
            </form>
        </div>
    </div>
    <!-- Reject Modal Template -->
    <div id="rejectModalTemplate" class="custom-modal">
        <div class="custom-modal-content">
            <form id="rejectForm" action="" method="post">
                <div class="custom-modal-header">
                    <h5 class="custom-modal-title"><i class="bi bi-x-circle text-danger me-2"></i>Reject Payment</h5>
                    <button type="button" class="btn-close custom-close" data-action="closeModal"></button>
                </div>
                <div class="custom-modal-body">
                    <div class="alert alert-warning"><i class="bi bi-exclamation-triangle me-2"></i>The business owner will be notified and asked to submit a new payment.</div>
                    <input type="hidden" name="status" value="Rejected">
                    <div class="mb-3"><label for="notes-reject" class="form-label">Reason for Rejection <span class="text-danger">*</span></label><textarea class="form-control" id="notes-reject" name="notes" rows="3" placeholder="Explain why the payment is being rejected" required></textarea></div>
                </div>
                <div class="custom-modal-footer"><button type="button" class="btn btn-secondary" data-action="closeModal"><i class="bi bi-x me-1"></i>Cancel</button><button type="submit" class="btn btn-danger"><i class="bi bi-x-lg me-1"></i>Reject Payment</button></div>
            </form>
        </div>
    </div>
</div>

<!-- Payment Data Store -->
<div id="paymentDataStore" style="display: none;" data-payments='<?= json_encode($payments) ?>'></div>

<!-- Modal Styles -->
<style>
.custom-modal-container { display:none; position:fixed; top:0;left:0;width:100%;height:100%;z-index:1050; }
.custom-modal-backdrop { position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);z-index:1051; }
.custom-modal { position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);background:#fff;border-radius:.5rem;max-width:500px;width:95%;box-shadow:0 .5rem 1rem rgba(0,0,0,.15);z-index:1052;display:none;opacity:0;transition:opacity .3s; }
.custom-modal.show { display:block;opacity:1; }
.custom-modal-lg { max-width:800px; }
.custom-modal-header, .custom-modal-footer { display:flex;justify-content:space-between;align-items:center;padding:1rem;border-top:1px solid #dee2e6; }
.custom-modal-body { padding:1rem;max-height:calc(100vh - 200px);overflow-y:auto; }
.custom-close { background:transparent;border:0;cursor:pointer; }
.fadeIn { animation:fadeIn .3s ease forwards; }
.fadeOut { animation:fadeOut .3s ease forwards; }
@keyframes fadeIn { from{opacity:0;}to{opacity:1;} }
@keyframes fadeOut { from{opacity:1;}to{opacity:0;} }
</style>

<!-- Modal Scripts -->
<script>
(function(){
    const payments = JSON.parse(document.getElementById('paymentDataStore').getAttribute('data-payments'));
    const container = document.getElementById('customModalContainer');
    document.addEventListener('click', e=>{
        const btn = e.target.closest('[data-action]'); if(!btn) return;
        const action = btn.getAttribute('data-action');
        const id = btn.getAttribute('data-payment-id') || window.currentPaymentId;
        const payment = payments.find(p=>p.id==id);
        window.currentPaymentId = id;
        switch(action){
            case 'openProofModal': openProofModal(payment);break;
            case 'openApproveModal': closeAllModals(); openApproveModal(payment);break;
            case 'openRejectModal': closeAllModals(); openRejectModal(payment);break;
            case 'closeModal': closeAllModals();break;
        }
    });
    function openProofModal(p){
        const modal = document.getElementById('proofModalTemplate');
        const body = modal.querySelector('.custom-modal-body');
        const filename = p.proof_file.split('/').pop();
        const url = '<?= URLROOT ?>//uploads/proofs/'+filename;
        body.innerHTML = '<p><strong>'+p.business_name+'</strong></p><img src="'+url+'" class="img-fluid"/>';
        const apr = modal.querySelector('.proof-approve-btn'); apr.setAttribute('data-payment-id',p.id);
        const rej = modal.querySelector('.proof-reject-btn'); rej.setAttribute('data-payment-id',p.id);
        show(modal);
    }
    function openApproveModal(p){
        const modal=document.getElementById('approveModalTemplate');
        const form=modal.querySelector('form'); form.action='<?= URLROOT ?>/treasurer/verifyPayment/'+p.id;
        const det=modal.querySelector('.payment-details'); det.innerHTML='<p>Ref:'+p.reference_number+'</p><p>Amount:₱'+p.amount+'</p>';
        show(modal);
    }
    function openRejectModal(p){
        const modal=document.getElementById('rejectModalTemplate');
        modal.querySelector('form').action='<?= URLROOT ?>/treasurer/verifyPayment/'+p.id;
        show(modal);
    }
    function show(m){ container.style.display='block'; m.classList.add('show','fadeIn'); }
    function closeAllModals(){ document.querySelectorAll('.custom-modal.show').forEach(m=>{ m.classList.remove('fadeIn'); m.classList.add('fadeOut'); setTimeout(()=>{ m.classList.remove('show','fadeOut'); if(!document.querySelector('.custom-modal.show')) container.style.display='none'; },300); }); }
    document.getElementById('customModalBackdrop').addEventListener('click', closeAllModals);
})();
</script>