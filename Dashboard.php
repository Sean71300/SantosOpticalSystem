<?php
// [Previous includes and setup code remains the same...]

function getSalesOverviewData($days = 7) {
    global $conn;
    
    $query = "SELECT 
                DATE(oh.Created_dt) as date, 
                SUM(od.Quantity) as total_sold 
              FROM orderDetails od
              JOIN Order_hdr oh ON od.OrderHdr_id = oh.Orderhdr_id
              WHERE oh.Created_dt >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
              AND od.Status = 'Complete'
              GROUP BY DATE(oh.Created_dt)
              ORDER BY date ASC";
              
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $days);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $salesData = [];
    while ($row = $result->fetch_assoc()) {
        $salesData[] = $row;
    }
    
    // Fill in missing days with 0 values
    $filledData = [];
    $currentDate = new DateTime("-" . ($days - 1) . " days");
    $endDate = new DateTime();
    
    while ($currentDate <= $endDate) {
        $dateStr = $currentDate->format('Y-m-d');
        $found = false;
        
        foreach ($salesData as $sale) {
            if ($sale['date'] == $dateStr) {
                $filledData[] = $sale;
                $found = true;
                break;
            }
        }
        
        if (!$found) {
            $filledData[] = ['date' => $dateStr, 'total_sold' => 0];
        }
        
        $currentDate->modify('+1 day');
    }
    
    return $filledData;
}

$salesData = getSalesOverviewData();
?>

<!-- [HTML head and other parts remain the same until the chart section...] -->

<!-- Sales Overview Section -->
<div class="row mt-4">
    <div class="col-md-8">
        <div class="dashboard-card">
            <h5 class="mb-3"><i class="fas fa-chart-line me-2"></i>Claimed Orders Overview (Last 7 Days)</h5>
            <div class="chart-container">
                <canvas id="salesChart"></canvas>
            </div>
        </div>
    </div>
    
    <!-- [Rest of your dashboard content remains the same...] -->
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // [Your existing sidebar toggle code remains the same...]

        // Sales Chart - Updated for claimed orders
        const salesData = {
            labels: <?php echo json_encode(array_column($salesData, 'date')); ?>,
            datasets: [{
                label: 'Claimed Products Sold',
                data: <?php echo json_encode(array_column($salesData, 'total_sold')); ?>,
                backgroundColor: 'rgba(40, 167, 69, 0.2)',
                borderColor: 'rgba(40, 167, 69, 1)',
                borderWidth: 2,
                tension: 0.1,
                fill: true
            }]
        };

        const salesCtx = document.getElementById('salesChart').getContext('2d');
        const salesChart = new Chart(salesCtx, {
            type: 'line',
            data: salesData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Claimed Products Sold'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Date'
                        }
                    }
                },
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.parsed.y + ' claimed products sold';
                            }
                        }
                    }
                }
            }
        });
    });
</script>