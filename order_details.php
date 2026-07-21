<?php
session_start();
require 'vendor/autoload.php';

// Database connection
$host = 'localhost';
$dbname = 'agriculture';
$username = 'root';
$password = '';
$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(['error' => 'Database connection failed']));
}

// Check if order_id is provided
if (!isset($_GET['order_id']) || !is_numeric($_GET['order_id'])) {
    die(json_encode(['error' => 'Invalid order ID']));
}

$order_id = intval($_GET['order_id']);
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;

// Fetch order details
$order_sql = "SELECT o.*, p.payment_method, p.payment_status as payment_table_status, 
              p.transaction_id, p.created_at as payment_date
              FROM orders o
              LEFT JOIN payments p ON o.order_id = p.order_id
              WHERE o.order_id = $order_id AND o.user_id = $user_id";

$order_result = $conn->query($order_sql);

if ($order_result->num_rows === 0) {
    die(json_encode(['error' => 'Order not found or access denied']));
}

$order = $order_result->fetch_assoc();

// Fetch order items
$items_sql = "SELECT oi.*, p.product_name, p.description, p.image_url, p.image1, p.image2, p.image3
              FROM order_items oi
              JOIN products p ON oi.product_id = p.product_id
              WHERE oi.order_id = $order_id";
$items_result = $conn->query($items_sql);

$order_items = [];
while ($item = $items_result->fetch_assoc()) {
    $order_items[] = $item;
}

// Calculate timeline
function calculateTimeline($order_date, $status) {
    $timeline = ['Order Placed' => ['date' => $order_date, 'completed' => true]];
    $order_time = strtotime($order_date);
    
    switch ($status) {
        case 'Processing':
            $timeline['Processing'] = ['date' => date('Y-m-d H:i:s', $order_time + 3600), 'completed' => true];
            $timeline['Shipped'] = ['date' => date('Y-m-d H:i:s', $order_time + 86400 * 2), 'completed' => false];
            $timeline['Delivered'] = ['date' => date('Y-m-d H:i:s', $order_time + 86400 * 5), 'completed' => false];
            break;
        case 'Shipped':
            $timeline['Processing'] = ['date' => date('Y-m-d H:i:s', $order_time + 3600), 'completed' => true];
            $timeline['Shipped'] = ['date' => date('Y-m-d H:i:s', $order_time + 86400 * 2), 'completed' => true];
            $timeline['Delivered'] = ['date' => date('Y-m-d H:i:s', $order_time + 86400 * 5), 'completed' => false];
            break;
        case 'Delivered':
            $timeline['Processing'] = ['date' => date('Y-m-d H:i:s', $order_time + 3600), 'completed' => true];
            $timeline['Shipped'] = ['date' => date('Y-m-d H:i:s', $order_time + 86400 * 2), 'completed' => true];
            $timeline['Delivered'] = ['date' => date('Y-m-d H:i:s', $order_time + 86400 * 4), 'completed' => true];
            break;
        case 'Cancelled':
            $timeline['Processing'] = ['date' => date('Y-m-d H:i:s', $order_time + 3600), 'completed' => true];
            $timeline['Cancelled'] = ['date' => date('Y-m-d H:i:s', $order_time + 86400), 'completed' => true];
            break;
    }
    return $timeline;
}

$timeline = calculateTimeline($order['order_date'], $order['status']);

// Generate HTML response
ob_start();
?>
<div class="order-details-container">
    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Order Summary</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-6">
                            <strong>Order ID:</strong>
                        </div>
                        <div class="col-6">
                            #<?php echo $order['order_id']; ?>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-6">
                            <strong>Order Date:</strong>
                        </div>
                        <div class="col-6">
                            <?php echo date('M d, Y h:i A', strtotime($order['order_date'])); ?>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-6">
                            <strong>Status:</strong>
                        </div>
                        <div class="col-6">
                            <span class="order-status <?php 
                                switch($order['status']) {
                                    case 'Processing': echo 'status-processing'; break;
                                    case 'Shipped': echo 'status-shipped'; break;
                                    case 'Delivered': echo 'status-delivered'; break;
                                    case 'Cancelled': echo 'status-cancelled'; break;
                                    default: echo 'status-pending';
                                }
                            ?>">
                                <?php echo $order['status']; ?>
                            </span>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-6">
                            <strong>Payment Status:</strong>
                        </div>
                        <div class="col-6">
                            <span class="order-status <?php 
                                switch($order['payment_table_status']) {
                                    case 'Completed': echo 'status-delivered'; break;
                                    case 'Failed': echo 'status-cancelled'; break;
                                    default: echo 'status-pending';
                                }
                            ?>">
                                <?php echo $order['payment_table_status'] ?? 'Pending'; ?>
                            </span>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-6">
                            <strong>Payment Method:</strong>
                        </div>
                        <div class="col-6">
                            <?php echo $order['payment_method'] ?? 'N/A'; ?>
                        </div>
                    </div>
                    <?php if (!empty($order['transaction_id'])): ?>
                    <div class="row mb-3">
                        <div class="col-6">
                            <strong>Transaction ID:</strong>
                        </div>
                        <div class="col-6">
                            <?php echo $order['transaction_id']; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    <div class="row mb-3">
                        <div class="col-6">
                            <strong>Total Amount:</strong>
                        </div>
                        <div class="col-6">
                            ₹<?php echo number_format($order['total_amount'], 2); ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Delivery Information</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-12">
                            <strong>Customer Name:</strong>
                            <p><?php echo htmlspecialchars($order['customer_name']); ?></p>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-12">
                            <strong>Email:</strong>
                            <p><?php echo htmlspecialchars($order['email']); ?></p>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-12">
                            <strong>Phone:</strong>
                            <p><?php echo htmlspecialchars($order['phone']); ?></p>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-12">
                            <strong>Address:</strong>
                            <p><?php echo nl2br(htmlspecialchars($order['address'])); ?></p>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-12">
                            <strong>Pincode:</strong>
                            <p><?php echo htmlspecialchars($order['pincode']); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Order Items</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Price</th>
                                    <th>Qty</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($order_items as $item): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="<?php echo htmlspecialchars($item['image_url']); ?>" 
                                                 alt="<?php echo htmlspecialchars($item['product_name']); ?>" 
                                                 class="img-thumbnail me-3" style="width: 60px; height: 60px; object-fit: cover;">
                                            <div>
                                                <h6 class="mb-0"><?php echo htmlspecialchars($item['product_name']); ?></h6>
                                                <small class="text-muted"><?php echo substr(htmlspecialchars($item['description']), 0, 50); ?>...</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>₹<?php echo number_format($item['price'], 2); ?></td>
                                    <td><?php echo $item['quantity']; ?></td>
                                    <td>₹<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" class="text-end"><strong>Subtotal:</strong></td>
                                    <td>₹<?php echo number_format($order['total_amount'], 2); ?></td>
                                </tr>
                                <tr>
                                    <td colspan="3" class="text-end"><strong>Shipping:</strong></td>
                                    <td>₹0.00</td>
                                </tr>
                                <tr>
                                    <td colspan="3" class="text-end"><strong>Total:</strong></td>
                                    <td>₹<?php echo number_format($order['total_amount'], 2); ?></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .order-status {
        display: inline-block;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .status-pending {
        background-color: rgba(255, 193, 7, 0.2);
        color: #856404;
    }

    .status-processing {
        background-color: rgba(0, 123, 255, 0.2);
        color: #004085;
    }

    .status-shipped {
        background-color: rgba(40, 167, 69, 0.2);
        color: #155724;
    }

    .status-delivered {
        background-color: rgba(40, 167, 69, 0.3);
        color: #155724;
    }

    .status-cancelled {
        background-color: rgba(220, 53, 69, 0.2);
        color: #721c24;
    }

    .order-timeline {
        position: relative;
        padding-left: 30px;
    }

    .order-timeline::before {
        content: '';
        position: absolute;
        left: 10px;
        top: 0;
        bottom: 0;
        width: 2px;
        background: #e9ecef;
    }

    .timeline-step {
        position: relative;
        padding-bottom: 20px;
    }

    .timeline-step:last-child {
        padding-bottom: 0;
    }

    .timeline-step::before {
        content: '';
        position: absolute;
        left: -30px;
        top: 3px;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background: #adb5bd;
        z-index: 1;
    }

    .timeline-step.active::before {
        background: #6a11cb;
        box-shadow: 0 0 0 3px rgba(106, 17, 203, 0.2);
    }

    .timeline-step.completed::before {
        background: #28a745;
        box-shadow: 0 0 0 3px rgba(40, 167, 69, 0.2);
    }

    .timeline-date {
        font-size: 0.75rem;
        color: #6c757d;
    }
</style>
<?php
$html = ob_get_clean();
echo $html;
?>