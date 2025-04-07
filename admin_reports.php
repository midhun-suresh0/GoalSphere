<?php
session_start();

// Check if user is logged in and is an admin
if (!isset($_SESSION['admin_id'])) {
    header('Location: signin.php');
    exit();
}

// Database connection
require_once 'includes/db.php';

// Set default date range (last 30 days)
$end_date = date('Y-m-d');
$start_date = date('Y-m-d', strtotime('-30 days'));

// Get date range from filter if set
if (isset($_GET['start_date']) && isset($_GET['end_date'])) {
    $start_date = $_GET['start_date'];
    $end_date = $_GET['end_date'];
}

// Get user filter if set
$user_filter = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;

// Initialize stats with default values in case of query failure
$stats = [
    'total_orders' => 0,
    'total_revenue' => 0,
    'average_order' => 0,
    'unique_customers' => 0
];

// First check if the orders table exists
$table_exists = $conn->query("SHOW TABLES LIKE 'orders'");
if ($table_exists && $table_exists->num_rows > 0) {
    
    // Check if the required columns exist in the orders table
    $check_columns = $conn->query("DESCRIBE orders");
    $has_required_columns = false;
    $columns = [];
    
    if ($check_columns) {
        while ($col = $check_columns->fetch_assoc()) {
            $columns[] = $col['Field'];
        }
        
        // Check if we have all necessary columns
        $has_required_columns = in_array('id', $columns) && 
                               in_array('total_amount', $columns) && 
                               in_array('user_id', $columns) && 
                               in_array('order_date', $columns);
    }
    
    if ($has_required_columns) {
        // Build base query for summary statistics
        $stats_query = "SELECT 
                        COUNT(DISTINCT id) as total_orders,
                        SUM(total_amount) as total_revenue,
                        AVG(total_amount) as average_order,
                        COUNT(DISTINCT user_id) as unique_customers
                        FROM orders 
                        WHERE order_date BETWEEN ? AND ?";
        
        // Add user filter if specified
        $user_condition = "";
        if ($user_filter > 0) {
            $stats_query .= " AND user_id = ?";
            $user_condition = " AND user_id = " . $user_filter;
        }
        
        // Prepare and execute stats query
        $stmt = $conn->prepare($stats_query);
        if ($stmt) {
            if ($user_filter > 0) {
                $stmt->bind_param("ssi", $start_date, $end_date, $user_filter);
            } else {
                $stmt->bind_param("ss", $start_date, $end_date);
            }
            $stmt->execute();
            $result = $stmt->get_result();
            if ($row = $result->fetch_assoc()) {
                $stats = $row;
            }
        } else {
            // Handle prepare error
            error_log("SQL prepare error: " . $conn->error);
        }
    }
}

// Initialize chart data arrays
$chart_dates = [];
$chart_revenue = [];
$chart_orders = [];

// Get daily sales data for chart if table exists
if ($table_exists && $table_exists->num_rows > 0 && $has_required_columns) {
    $daily_sales_query = "SELECT DATE(order_date) as day, SUM(total_amount) as revenue, COUNT(*) as orders
                          FROM orders
                          WHERE order_date BETWEEN ? AND ?";
    
    if ($user_filter > 0) {
        $daily_sales_query .= " AND user_id = ?";
    }
    
    $daily_sales_query .= " GROUP BY DATE(order_date) ORDER BY day";
    
    $daily_stmt = $conn->prepare($daily_sales_query);
    if ($daily_stmt) {
        if ($user_filter > 0) {
            $daily_stmt->bind_param("ssi", $start_date, $end_date, $user_filter);
        } else {
            $daily_stmt->bind_param("ss", $start_date, $end_date);
        }
        $daily_stmt->execute();
        $daily_sales = $daily_stmt->get_result();
        
        while ($day = $daily_sales->fetch_assoc()) {
            $chart_dates[] = $day['day'];
            $chart_revenue[] = $day['revenue'];
            $chart_orders[] = $day['orders'];
        }
    }
}

// Initialize top products array
$top_products = null;

// Get top products if jersey_orders exists
$jersey_orders_exists = $conn->query("SHOW TABLES LIKE 'jersey_orders'");
if ($jersey_orders_exists && $jersey_orders_exists->num_rows > 0) {
    $jerseys_exists = $conn->query("SHOW TABLES LIKE 'jerseys'");
    if ($jerseys_exists && $jerseys_exists->num_rows > 0) {
        $top_products_query = "SELECT j.name, COUNT(o.id) as order_count, SUM(o.total_amount) as revenue
                              FROM orders o
                              JOIN jersey_orders jo ON o.id = jo.order_id
                              JOIN jerseys j ON jo.jersey_id = j.id
                              WHERE o.order_date BETWEEN ? AND ?";
        
        if ($user_filter > 0) {
            $top_products_query .= " AND o.user_id = ?";
        }
        
        $top_products_query .= " GROUP BY j.id ORDER BY revenue DESC LIMIT 5";
        
        $products_stmt = $conn->prepare($top_products_query);
        if ($products_stmt) {
            if ($user_filter > 0) {
                $products_stmt->bind_param("ssi", $start_date, $end_date, $user_filter);
            } else {
                $products_stmt->bind_param("ss", $start_date, $end_date);
            }
            $products_stmt->execute();
            $top_products = $products_stmt->get_result();
        }
    }
}

// Initialize purchases and pagination
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;
$purchases = null;
$total_pages = 0;

// Get detailed purchase data if users table exists
$users_exists = $conn->query("SHOW TABLES LIKE 'users'");
if ($table_exists && $table_exists->num_rows > 0 && $users_exists && $users_exists->num_rows > 0) {
    $purchases_query = "SELECT o.*, u.first_name, u.last_name, u.email
                       FROM orders o
                       JOIN users u ON o.user_id = u.id
                       WHERE o.order_date BETWEEN ? AND ?";
    
    if ($user_filter > 0) {
        $purchases_query .= " AND o.user_id = ?";
    }
    
    $purchases_query .= " ORDER BY o.order_date DESC LIMIT ?, ?";
    
    $purchase_stmt = $conn->prepare($purchases_query);
    if ($purchase_stmt) {
        if ($user_filter > 0) {
            $purchase_stmt->bind_param("ssii", $start_date, $end_date, $offset, $per_page);
        } else {
            $purchase_stmt->bind_param("ssii", $start_date, $end_date, $offset, $per_page);
        }
        $purchase_stmt->execute();
        $purchases = $purchase_stmt->get_result();
        
        // Get total records for pagination
        $count_query = "SELECT COUNT(*) as total FROM orders WHERE order_date BETWEEN ? AND ?";
        if ($user_filter > 0) {
            $count_query .= " AND user_id = ?";
        }
        
        $count_stmt = $conn->prepare($count_query);
        if ($count_stmt) {
            if ($user_filter > 0) {
                $count_stmt->bind_param("ssi", $start_date, $end_date, $user_filter);
            } else {
                $count_stmt->bind_param("ss", $start_date, $end_date);
            }
            $count_stmt->execute();
            $count_result = $count_stmt->get_result();
            if ($count_row = $count_result->fetch_assoc()) {
                $total_records = $count_row['total'];
                $total_pages = ceil($total_records / $per_page);
            }
        }
    }
}

// Set current page for sidebar highlighting
$current_page = 'admin_reports.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase Reports - GoalSphere Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="css/styles.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="flex h-screen">
        <!-- Include Sidebar -->
        <?php include 'includes/admin_sidebar.php'; ?>

        <!-- Main Content -->
        <div class="flex-1 overflow-auto">
            <div class="p-8">
                <h1 class="text-2xl font-bold text-gray-800 mb-6">Purchase Reports</h1>
                
                <?php if (!($table_exists && $table_exists->num_rows > 0)): ?>
                <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-8">
                    <p>The orders table does not exist in the database. Please create it to see purchase reports.</p>
                </div>
                <?php else: ?>
                
                <!-- Filter Controls -->
                <div class="bg-white rounded-lg shadow-md p-6 mb-8">
                    <h2 class="text-lg font-semibold text-gray-700 mb-4">Filter Reports</h2>
                    <form action="admin_reports.php" method="get" class="flex flex-wrap items-end gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1">Start Date</label>
                            <input type="date" name="start_date" value="<?php echo $start_date; ?>" 
                                   class="border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1">End Date</label>
                            <input type="date" name="end_date" value="<?php echo $end_date; ?>" 
                                   class="border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1">User (Optional)</label>
                            <select name="user_id" class="border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="0">All Users</option>
                                <?php 
                                $users = $conn->query("SELECT id, first_name, last_name, email FROM users ORDER BY first_name");
                                if ($users) {
                                    while ($user = $users->fetch_assoc()):
                                        $selected = ($user_filter == $user['id']) ? 'selected' : '';
                                    ?>
                                    <option value="<?php echo $user['id']; ?>" <?php echo $selected; ?>>
                                        <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name'] . ' (' . $user['email'] . ')'); ?>
                                    </option>
                                    <?php endwhile;
                                } ?>
                            </select>
                        </div>
                        <div>
                            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                                Apply Filters
                            </button>
                            <a href="admin_reports.php" class="ml-2 text-gray-600 hover:text-gray-800">
                                Reset
                            </a>
                        </div>
                    </form>
                </div>
                
                <!-- Summary Statistics -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h3 class="text-gray-500 text-sm mb-2">Total Orders</h3>
                        <p class="text-3xl font-bold"><?php echo number_format($stats['total_orders']); ?></p>
                    </div>

                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h3 class="text-gray-500 text-sm mb-2">Total Revenue</h3>
                        <p class="text-3xl font-bold">₹<?php echo number_format($stats['total_revenue'] ?? 0, 2); ?></p>
                    </div>

                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h3 class="text-gray-500 text-sm mb-2">Average Order Value</h3>
                        <p class="text-3xl font-bold">₹<?php echo number_format($stats['average_order'] ?? 0, 2); ?></p>
                    </div>

                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h3 class="text-gray-500 text-sm mb-2">Unique Customers</h3>
                        <p class="text-3xl font-bold"><?php echo number_format($stats['unique_customers']); ?></p>
                    </div>
                </div>
                
                <!-- Charts -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
                    <!-- Sales Chart -->
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h2 class="text-lg font-semibold text-gray-700 mb-4">Sales Trend</h2>
                        <?php if (count($chart_dates) > 0): ?>
                        <canvas id="salesChart" height="300"></canvas>
                        <?php else: ?>
                        <p class="text-gray-500 italic text-center py-12">No sales data available for the selected period.</p>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Top Products -->
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h2 class="text-lg font-semibold text-gray-700 mb-4">Top Products</h2>
                        <?php if ($top_products && $top_products->num_rows > 0): ?>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead>
                                        <tr>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Orders</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Revenue</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200">
                                        <?php while ($product = $top_products->fetch_assoc()): ?>
                                        <tr>
                                            <td class="px-4 py-3"><?php echo htmlspecialchars($product['name']); ?></td>
                                            <td class="px-4 py-3"><?php echo number_format($product['order_count']); ?></td>
                                            <td class="px-4 py-3">₹<?php echo number_format($product['revenue'], 2); ?></td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p class="text-gray-500 italic text-center py-12">No product data available for the selected period.</p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Purchase Details -->
                <div class="bg-white rounded-lg shadow-md p-6 mb-4">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-lg font-semibold text-gray-700">Purchase Details</h2>
                        <a href="export_purchases.php?start=<?php echo $start_date; ?>&end=<?php echo $end_date; ?>&user=<?php echo $user_filter; ?>" 
                           class="bg-green-600 text-white px-4 py-2 rounded-md text-sm hover:bg-green-700">
                            Export CSV
                        </a>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order ID</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <?php if ($purchases && $purchases->num_rows > 0): ?>
                                    <?php while ($order = $purchases->fetch_assoc()): ?>
                                    <tr>
                                        <td class="px-4 py-3">#<?php echo $order['id']; ?></td>
                                        <td class="px-4 py-3"><?php echo date('M d, Y', strtotime($order['order_date'])); ?></td>
                                        <td class="px-4 py-3">
                                            <?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?>
                                            <div class="text-xs text-gray-500"><?php echo htmlspecialchars($order['email']); ?></div>
                                        </td>
                                        <td class="px-4 py-3">₹<?php echo number_format($order['total_amount'], 2); ?></td>
                                        <td class="px-4 py-3">
                                            <span class="px-2 py-1 rounded-full text-xs 
                                                <?php echo ($order['status'] == 'completed') ? 'bg-green-100 text-green-800' : 
                                                          (($order['status'] == 'processing') ? 'bg-blue-100 text-blue-800' : 'bg-yellow-100 text-yellow-800'); ?>">
                                                <?php echo ucfirst($order['status']); ?>
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <a href="admin_order_details.php?id=<?php echo $order['id']; ?>" class="text-blue-600 hover:text-blue-800">
                                                View Details
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="px-4 py-3 text-center text-gray-500 italic">No purchases found for the selected criteria.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                    <div class="flex justify-center mt-6">
                        <div class="flex space-x-1">
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <a href="?start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>&user_id=<?php echo $user_filter; ?>&page=<?php echo $i; ?>" 
                                   class="px-3 py-1 rounded-md <?php echo ($page == $i) ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'; ?>">
                                    <?php echo $i; ?>
                                </a>
                            <?php endfor; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script>
    // Initialize sales chart
    document.addEventListener('DOMContentLoaded', function() {
        <?php if (count($chart_dates) > 0): ?>
        const ctx = document.getElementById('salesChart').getContext('2d');
        
        const salesChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($chart_dates); ?>,
                datasets: [
                    {
                        label: 'Revenue (₹)',
                        data: <?php echo json_encode($chart_revenue); ?>,
                        borderColor: 'rgba(59, 130, 246, 1)',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4,
                        yAxisID: 'y'
                    },
                    {
                        label: 'Orders',
                        data: <?php echo json_encode($chart_orders); ?>,
                        borderColor: 'rgba(16, 185, 129, 1)',
                        backgroundColor: 'rgba(16, 185, 129, 0)',
                        borderWidth: 2,
                        borderDash: [5, 5],
                        tension: 0.4,
                        yAxisID: 'y1'
                    }
                ]
            },
            options: {
                responsive: true,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        title: {
                            display: true,
                            text: 'Revenue (₹)'
                        }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        grid: {
                            drawOnChartArea: false,
                        },
                        title: {
                            display: true,
                            text: 'Number of Orders'
                        }
                    }
                }
            }
        });
        <?php endif; ?>
    });
    </script>
</body>
</html> 