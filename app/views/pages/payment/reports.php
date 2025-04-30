<?php
// Reports view for treasurer dashboard
$stats = $data['stats'] ?? [];
$totalCollected = $stats['total_collected'] ?? 0;
$monthlyStats = $stats['monthly_stats'] ?? [];
$paymentMethods = $stats['payment_methods'] ?? [];
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="mb-0"><i class="bi bi-bar-chart-fill me-2"></i>Payment Reports</h3>
    <div>
        <span class="badge bg-dark p-2">
            <i class="bi bi-calendar-event me-1"></i> <?= date('F d, Y') ?>
        </span>
    </div>
</div>

<!-- Stats Cards -->
<div class="row mb-4">
    <div class="col-md-4 mb-3">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h6 class="text-muted text-uppercase">Total Revenue</h6>
                        <h2 class="mb-0">₱<?= number_format($totalCollected, 2) ?></h2>
                    </div>
                    <div class="icon-circle bg-success text-white">
                        <i class="bi bi-cash-stack fs-4"></i>
                    </div>
                </div>
                <div class="mt-3">
                    <small class="text-success">
                        <i class="bi bi-arrow-up-right"></i> Successfully collected funds
                    </small>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4 mb-3">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h6 class="text-muted text-uppercase">Monthly Income</h6>
                        <?php
                        // Calculate current month's income
                        $currentMonth = date('Y-m');
                        $monthlyIncome = 0;
                        foreach ($monthlyStats as $stat) {
                            if ($stat->month === $currentMonth) {
                                $monthlyIncome = $stat->verified_amount;
                                break;
                            }
                        }
                        ?>
                        <h2 class="mb-0">₱<?= number_format($monthlyIncome, 2) ?></h2>
                    </div>
                    <div class="icon-circle bg-primary text-white">
                        <i class="bi bi-calendar-check fs-4"></i>
                    </div>
                </div>
                <div class="mt-3">
                    <small class="text-primary">
                        <i class="bi bi-calendar3"></i> <?= date('F Y') ?> revenue
                    </small>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4 mb-3">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h6 class="text-muted text-uppercase">Pending Payments</h6>
                        <?php
                        // Count pending payments
                        $pendingCount = 0;
                        foreach ($monthlyStats as $stat) {
                            $pendingCount += $stat->pending_count;
                        }
                        ?>
                        <h2 class="mb-0"><?= $pendingCount ?></h2>
                    </div>
                    <div class="icon-circle bg-warning text-white">
                        <i class="bi bi-hourglass-split fs-4"></i>
                    </div>
                </div>
                <div class="mt-3">
                    <small class="text-warning">
                        <i class="bi bi-clock"></i> Awaiting verification
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Monthly Revenue Chart -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white py-3">
        <h5 class="mb-0"><i class="bi bi-graph-up me-2"></i>Monthly Revenue Trend</h5>
    </div>
    <div class="card-body">
        <div>
            <canvas id="revenueChart" height="250"></canvas>
        </div>
    </div>
</div>

<!-- Payment Methods & Transactions -->
<div class="row mb-4">
    <!-- Payment Methods -->
    <div class="col-md-5 mb-3">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0"><i class="bi bi-credit-card me-2"></i>Payment Methods</h5>
            </div>
            <div class="card-body pb-0">
                <div style="height: 250px">
                    <canvas id="paymentMethodsChart"></canvas>
                </div>
            </div>
            <div class="card-footer bg-white border-0">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Method</th>
                                <th class="text-end">Amount</th>
                                <th class="text-end">Count</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($paymentMethods as $method): ?>
                                <tr>
                                    <td><?= htmlspecialchars($method->payment_method) ?></td>
                                    <td class="text-end">₱<?= number_format($method->verified_amount, 2) ?></td>
                                    <td class="text-end"><?= $method->total_count ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Transactions -->
    <div class="col-md-7 mb-3">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                <h5 class="mb-0"><i class="bi bi-list-check me-2"></i>Monthly Transactions Summary</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Month</th>
                                <th class="text-end">Transactions</th>
                                <th class="text-end">Verified (₱)</th>
                                <th class="text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($monthlyStats)): ?>
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">No transaction data available</td>
                                </tr>
                            <?php else: ?>
                                <?php 
                                // Sort by month descending
                                usort($monthlyStats, function($a, $b) {
                                    return strcmp($b->month, $a->month);
                                });
                                
                                // Take only the most recent 12 months
                                $recentMonthlyStats = array_slice($monthlyStats, 0, 6);
                                
                                foreach ($recentMonthlyStats as $stat): 
                                    $month = date('F Y', strtotime($stat->month . '-01'));
                                    $totalTransactions = $stat->total_payments;
                                    $verifiedAmount = $stat->verified_amount;
                                    $pendingCount = $stat->pending_count;
                                    $rejectedCount = $stat->rejected_count;
                                ?>
                                    <tr>
                                        <td><?= $month ?></td>
                                        <td class="text-end"><?= $totalTransactions ?></td>
                                        <td class="text-end">₱<?= number_format($verifiedAmount, 2) ?></td>
                                        <td class="text-center">
                                            <?php if ($pendingCount > 0): ?>
                                                <span class="badge rounded-pill bg-warning text-dark" title="Pending">
                                                    <?= $pendingCount ?> pending
                                                </span>
                                            <?php endif; ?>
                                            
                                            <?php if ($rejectedCount > 0): ?>
                                                <span class="badge rounded-pill bg-danger ms-1" title="Rejected">
                                                    <?= $rejectedCount ?> rejected
                                                </span>
                                            <?php endif; ?>
                                            
                                            <?php if ($pendingCount == 0 && $rejectedCount == 0): ?>
                                                <span class="badge rounded-pill bg-success">All verified</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-white text-end">
                <a href="<?= URLROOT ?>/treasurer/history" class="btn btn-sm btn-primary">
                    <i class="bi bi-clock-history me-1"></i> View Complete History
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Export Report Section -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white py-3">
        <h5 class="mb-0"><i class="bi bi-file-earmark-text me-2"></i>Generate Reports</h5>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-4">
                <div class="form-floating">
                    <select class="form-select" id="reportType">
                        <option value="monthly">Monthly Revenue Report</option>
                        <option value="payment-methods">Payment Methods Analysis</option>
                        <option value="business-types">Business Type Distribution</option>
                        <option value="complete">Complete Financial Report</option>
                    </select>
                    <label for="reportType">Report Type</label>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-floating">
                    <input type="month" class="form-select" id="reportMonth" value="<?= date('Y-m') ?>">
                    <label for="reportMonth">Month</label>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-floating">
                    <select class="form-select" id="reportFormat">
                        <option value="pdf">PDF Document</option>
                        <option value="excel">Excel Spreadsheet</option>
                        <option value="csv">CSV File</option>
                    </select>
                    <label for="reportFormat">Format</label>
                </div>
            </div>
            <div class="col-md-2 d-grid">
                <button class="btn btn-primary" id="generateReport">
                    <i class="bi bi-download me-2"></i>Generate
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Include Chart.js library -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Data for charts
    const monthlyLabels = [];
    const monthlyData = [];
    const methodLabels = [];
    const methodData = [];
    const methodColors = [
        'rgba(54, 162, 235, 0.8)',
        'rgba(255, 99, 132, 0.8)',
        'rgba(75, 192, 192, 0.8)',
        'rgba(255, 206, 86, 0.8)',
        'rgba(153, 102, 255, 0.8)'
    ];

    // Monthly data
    <?php 
    $sortedMonthlyStats = $monthlyStats;
    usort($sortedMonthlyStats, function($a, $b) {
        return strcmp($a->month, $b->month);
    });
    $recentMonths = array_slice($sortedMonthlyStats, -6); // Get last 6 months
    ?>
    
    <?php foreach ($recentMonths as $stat): ?>
    monthlyLabels.push('<?= date('M Y', strtotime($stat->month . '-01')) ?>');
    monthlyData.push(<?= (float)$stat->verified_amount ?>);
    <?php endforeach; ?>

    // Payment methods data
    <?php foreach ($paymentMethods as $method): ?>
    methodLabels.push('<?= $method->payment_method ?>');
    methodData.push(<?= (float)$method->verified_amount ?>);
    <?php endforeach; ?>

    // Revenue Chart
    const revenueCtx = document.getElementById('revenueChart').getContext('2d');
    const revenueChart = new Chart(revenueCtx, {
        type: 'bar',
        data: {
            labels: monthlyLabels,
            datasets: [{
                label: 'Monthly Revenue (₱)',
                data: monthlyData,
                backgroundColor: 'rgba(54, 162, 235, 0.5)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
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
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return '₱' + context.parsed.y.toLocaleString();
                        }
                    }
                }
            }
        }
    });

    // Payment Methods Chart
    const methodsCtx = document.getElementById('paymentMethodsChart').getContext('2d');
    const methodsChart = new Chart(methodsCtx, {
        type: 'doughnut',
        data: {
            labels: methodLabels,
            datasets: [{
                data: methodData,
                backgroundColor: methodColors,
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const value = context.raw;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = Math.round((value / total) * 100);
                            return `₱${value.toLocaleString()} (${percentage}%)`;
                        }
                    }
                },
                legend: {
                    position: 'right'
                }
            }
        }
    });

    // Generate Report functionality
    document.getElementById('generateReport').addEventListener('click', function() {
        const reportType = document.getElementById('reportType').value;
        const reportMonth = document.getElementById('reportMonth').value;
        const reportFormat = document.getElementById('reportFormat').value;
        
        // Redirect to the report generation URL
        window.location.href = `<?= URLROOT ?>/treasurer/generateReport?type=${reportType}&month=${reportMonth}&format=${reportFormat}`;
    });
});
</script>

<style>
.icon-circle {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}
</style>