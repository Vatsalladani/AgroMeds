<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Database connection
$host = 'localhost';
$dbname = 'agriculture';
$username = 'root';
$password = '';
$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get purchase data from URL
$purchaseData = json_decode(urldecode($_GET['data']), true);
$totalPrice = $_GET['total'];

// Fetch user details from database
$user_id = $_SESSION['user_id'];
$user_sql = "SELECT * FROM users WHERE user_id = $user_id";
$user_result = $conn->query($user_sql);
$user = $user_result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        /* Gradient background with animation */
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #6a11cb, #2575fc);
            background-size: 200% 200%;
            animation: gradientAnimation 10s ease infinite;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
        }

        @keyframes gradientAnimation {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        /* Purchase details box styling */
        .details-box {
            background: rgba(255, 255, 255, 0.9);
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
            padding: 30px;
            width: 100%;
            max-width: 800px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .details-box:hover {
            transform: translateY(-10px);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.3);
        }

        /* Form styling */
        .form-group {
            margin-bottom: 20px;
        }

        .form-control {
            border-radius: 5px;
            padding: 10px 15px;
            border: 1px solid #ddd;
        }

        .form-control:focus {
            border-color: #28a745;
            box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
        }

        /* Button styling */
        .btn-cancel {
            background-color: #dc3545;
            border: none;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            font-size: 16px;
            transition: background-color 0.3s ease;
            margin-right: 10px;
        }

        .btn-cancel:hover {
            background-color: #c82333;
        }

        .btn-confirm {
            background-color: #28a745;
            border: none;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            font-size: 16px;
            transition: background-color 0.3s ease;
        }

        .btn-confirm:hover {
            background-color: #218838;
        }

        /* Order summary styling */
        .order-summary {
            background-color: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-top: 20px;
        }

        .order-summary h4 {
            margin-bottom: 15px;
            color: #28a745;
        }

        .order-item {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }

        .order-total {
            font-weight: bold;
            font-size: 1.2rem;
            margin-top: 10px;
            color: #28a745;
        }
    </style>
</head>
<body>
    <div class="details-box">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Complete Your Order</h2>
            <button class="btn btn-cancel" onclick="cancelOrder()">Cancel</button>
        </div>
        
        <form id="orderForm">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="name">Full Name</label>
                        <input type="text" class="form-control" id="name" value="<?php echo htmlspecialchars($user['name'] ?? ''); ?>" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" class="form-control" id="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" required>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="tel" class="form-control" id="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="pincode">PIN Code</label>
                        <input type="text" class="form-control" id="pincode" required>
                    </div>
                </div>
            </div>
            
            <div class="form-group">
                <label for="address">Full Address</label>
                <textarea class="form-control" id="address" rows="3" required></textarea>
            </div>
            
            <div class="order-summary">
                <h4>Order Summary</h4>
                <?php foreach ($purchaseData as $item): ?>
                    <div class="order-item">
                        <span><?php echo htmlspecialchars($item['product_name']); ?> (x<?php echo htmlspecialchars($item['quantity']); ?>)</span>
                        <span>₹<?php echo number_format($item['total'], 2); ?></span>
                    </div>
                <?php endforeach; ?>
                <div class="order-total text-right">
                    Total: ₹<?php echo number_format($totalPrice, 2); ?>
                </div>
            </div>
            
            <div class="text-right mt-4">
                <button type="button" class="btn btn-confirm" onclick="confirmOrder()">Confirm Order</button>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Function to cancel the order
        function cancelOrder() {
            Swal.fire({
                title: 'Are you sure?',
                text: "You're about to cancel this order. This action cannot be undone.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, cancel it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'cart.php';
                }
            });
        }

        // Function to confirm the order
        function confirmOrder() {
            // Validate form
            const form = document.getElementById('orderForm');
            const inputs = form.querySelectorAll('input[required], textarea[required]');
            let isValid = true;
            
            inputs.forEach(input => {
                if (!input.value.trim()) {
                    input.style.borderColor = '#dc3545';
                    isValid = false;
                } else {
                    input.style.borderColor = '#28a745';
                }
            });
            
            if (!isValid) {
                Swal.fire({
                    title: 'Incomplete Information',
                    text: 'Please fill in all required fields.',
                    icon: 'error',
                    confirmButtonColor: '#28a745'
                });
                return;
            }
            
            // Prepare order data
            const orderData = {
                name: document.getElementById('name').value,
                email: document.getElementById('email').value,
                phone: document.getElementById('phone').value,
                pincode: document.getElementById('pincode').value,
                address: document.getElementById('address').value,
                products: <?php echo json_encode($purchaseData); ?>,
                total: <?php echo $totalPrice; ?>,
                user_id: <?php echo $_SESSION['user_id']; ?>
            };
            
            // Show loading animation
            Swal.fire({
                title: 'Processing Your Order',
                html: 'Please wait while we process your order...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            // Send data to server via AJAX
            fetch('process_order.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(orderData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        title: 'Order Confirmed!',
                        text: 'Your order has been placed successfully.',
                        icon: 'success',
                        confirmButtonColor: '#28a745'
                    }).then(() => {
                        window.location.href = 'order_confirmation.php?order_id=' + data.order_id;
                    });
                } else {
                    Swal.fire({
                        title: 'Error',
                        text: data.message || 'There was an error processing your order.',
                        icon: 'error',
                        confirmButtonColor: '#28a745'
                    });
                }
            })
            .catch(error => {
                Swal.fire({
                    title: 'Error',
                    text: 'There was an error processing your order. Please try again.',
                    icon: 'error',
                    confirmButtonColor: '#28a745'
                });
                console.error('Error:', error);
            });
        }
    </script>
</body>
</html>