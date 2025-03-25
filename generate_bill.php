<?php
// Start with clean output buffer to prevent "headers already sent" issues
ob_start();

session_start();
require_once 'includes/db.php';

// Check if TCPDF is installed
if (!file_exists('vendor/autoload.php')) {
    die("TCPDF library not found. Please install it using: composer require tecnickcom/tcpdf");
}

require_once 'vendor/autoload.php'; // Require the TCPDF library

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Check if order ID is provided
if (!isset($_GET['order_id']) || !is_numeric($_GET['order_id'])) {
    header('Location: orders.php');
    exit;
}

$order_id = $_GET['order_id'];

try {
    // Fetch order details
    $sql = "SELECT * FROM jersey_orders WHERE id = ? AND user_id = ?";
    
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    if (!$stmt->bind_param("ii", $order_id, $_SESSION['user_id'])) {
        throw new Exception("Binding parameters failed: " . $stmt->error);
    }
    
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        // Order not found or doesn't belong to this user
        header('Location: orders.php');
        exit;
    }
    
    $order = $result->fetch_assoc();
    
    // Check if payment is successful
    if ($order['payment_status'] !== 'success') {
        header('Location: view_order.php?id=' . $order_id);
        exit;
    }
    
    // Fetch user information - get all fields and check which ones exist
    $user_sql = "SELECT * FROM users WHERE id = ?";
    $user_stmt = $conn->prepare($user_sql);
    $user_stmt->bind_param("i", $_SESSION['user_id']);
    $user_stmt->execute();
    $user_result = $user_stmt->get_result();
    $user = $user_result->fetch_assoc();
    
    // Extract user name from any available field
    $user_name = '';
    if (isset($user['name'])) {
        $user_name = $user['name'];
    } elseif (isset($user['first_name'])) {
        $user_name = $user['first_name'] . (isset($user['last_name']) ? ' ' . $user['last_name'] : '');
    } elseif (isset($user['username'])) {
        $user_name = $user['username'];
    } else {
        // Fallback to user ID if no name is available
        $user_name = 'User #' . $_SESSION['user_id'];
    }
    
    // Get user email or set a fallback
    $user_email = isset($user['email']) ? $user['email'] : 'N/A';
    
    // Fetch order items - simplified to only get name and price
    $items_sql = "SELECT 
                    j.name as jersey_name,
                    SUM(oi.price) as total_price
                 FROM order_items oi
                 LEFT JOIN jerseys j ON oi.jersey_id = j.id
                 WHERE oi.order_id = ?
                 GROUP BY j.name";
    
    $items_stmt = $conn->prepare($items_sql);
    $items_stmt->bind_param("i", $order_id);
    $items_stmt->execute();
    $items_result = $items_stmt->get_result();
    
    $items = [];
    $subtotal = 0;
    
    while ($item = $items_result->fetch_assoc()) {
        $items[] = $item;
        $subtotal += $item['total_price'];
    }

    // Clear any previous output
    ob_clean();

    // Create PDF invoice
    class MYPDF extends TCPDF {
        public function Header() {
            $this->SetFont('helvetica', 'B', 18);
            $this->Cell(0, 15, 'GoalSphere', 0, false, 'L', 0, '', 0, false, 'M', 'M');
            $this->Ln(10);
            $this->SetFont('helvetica', '', 10);
            $this->Cell(0, 10, 'Invoice', 0, false, 'L', 0, '', 0, false, 'M', 'M');
            $this->Ln(15);
        }
        
        public function Footer() {
            $this->SetY(-15);
            $this->SetFont('helvetica', 'I', 8);
            $this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
        }
    }

    // Initialize PDF
    $pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    
    // Set document information
    $pdf->SetCreator('GoalSphere');
    $pdf->SetAuthor('GoalSphere');
    $pdf->SetTitle('Invoice #' . $order['order_number']);
    $pdf->SetSubject('Invoice for Order #' . $order['order_number']);
    
    // Set margins
    $pdf->SetMargins(15, 15, 15);
    $pdf->SetHeaderMargin(10);
    $pdf->SetFooterMargin(10);
    
    // Set auto page breaks
    $pdf->SetAutoPageBreak(TRUE, 15);
    
    // Add a page
    $pdf->AddPage();
    
    // Set font
    $pdf->SetFont('helvetica', '', 10);
    
    // Invoice header
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 10, 'INVOICE', 0, 1, 'C');
    $pdf->SetFont('helvetica', '', 10);
    $pdf->Cell(0, 5, 'Invoice #: ' . $order['order_number'], 0, 1, 'R');
    $pdf->Cell(0, 5, 'Date: ' . date('F d, Y', strtotime($order['created_at'])), 0, 1, 'R');
    $pdf->Cell(0, 5, 'Order ID: ' . $order_id, 0, 1, 'R');
    $pdf->Ln(10);
    
    // Customer information
    $pdf->SetFont('helvetica', 'B', 11);
    $pdf->Cell(0, 10, 'Customer Information', 0, 1, 'L');
    $pdf->SetFont('helvetica', '', 10);
    $pdf->Cell(0, 5, 'Name: ' . $user_name, 0, 1, 'L');
    $pdf->Cell(0, 5, 'Email: ' . $user_email, 0, 1, 'L');
    $pdf->Ln(10);
    
    // Payment information
    $pdf->SetFont('helvetica', 'B', 11);
    $pdf->Cell(0, 10, 'Payment Information', 0, 1, 'L');
    $pdf->SetFont('helvetica', '', 10);
    $pdf->Cell(0, 5, 'Payment Method: Razorpay', 0, 1, 'L');
    $pdf->Cell(0, 5, 'Payment ID: ' . $order['razorpay_payment_id'], 0, 1, 'L');
    $pdf->Cell(0, 5, 'Payment Status: ' . ucfirst($order['payment_status']), 0, 1, 'L');
    $pdf->Ln(10);
    
    // Order items
    $pdf->SetFont('helvetica', 'B', 11);
    $pdf->Cell(0, 10, 'Order Items', 0, 1, 'L');
    $pdf->Ln(2);
    
    // Create simplified table header - removed size and quantity columns
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->Cell(130, 7, 'Product', 1, 0, 'L');
    $pdf->Cell(60, 7, 'Amount', 1, 1, 'R');
    
    // Fill table with items - simplified without size and quantity
    $pdf->SetFont('helvetica', '', 10);
    foreach ($items as $item) {
        $pdf->Cell(130, 7, $item['jersey_name'] ?? 'Unknown Jersey', 1, 0, 'L');
        $pdf->Cell(60, 7, 'INR ' . number_format($item['total_price'], 2), 1, 1, 'R');
    }
    
    // Total
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->Cell(130, 7, 'Total', 1, 0, 'R');
    $pdf->Cell(60, 7, 'INR ' . number_format($order['total_amount'], 2), 1, 1, 'R');
    
    $pdf->Ln(10);
    
    // Terms and conditions
    $pdf->SetFont('helvetica', 'B', 11);
    $pdf->Cell(0, 10, 'Terms and Conditions', 0, 1, 'L');
    $pdf->SetFont('helvetica', '', 9);
    $pdf->MultiCell(0, 5, "1. All items purchased are final sale and non-refundable.\n2. For any issues with your order, please contact customer support.\n3. Returns are accepted only for damaged or incorrect items within 7 days of delivery.", 0, 'L', 0, 1, '', '', true);
    
    // Thank you note
    $pdf->Ln(10);
    $pdf->SetFont('helvetica', 'B', 11);
    $pdf->Cell(0, 10, 'Thank you for shopping with GoalSphere!', 0, 1, 'C');
    
    // Output the PDF
    $pdf->Output('Invoice_' . $order['order_number'] . '.pdf', 'D');
    
} catch (Exception $e) {
    // Clear any output that might have been generated
    ob_end_clean();
    
    // Log the error
    error_log("Error in generate_bill.php: " . $e->getMessage());
    
    // Redirect with error
    header('Location: view_order.php?id=' . $order_id . '&error=true');
    exit;
}
?> 