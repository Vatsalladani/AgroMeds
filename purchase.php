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

// Check if this is a reorder
$isReorder = isset($_GET['data']);
$purchaseData = [];
$totalPrice = 0;

if ($isReorder) {
    // Get data from reorder
    $purchaseData = json_decode($_GET['data'], true);
    $totalPrice = $_GET['total'];
} else {
    // Get data from cart (existing logic)
    if (isset($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $product_id => $item) {
            // Get current product details including stock
          $product_sql = "SELECT product_name, price, image_url, quantity FROM products WHERE product_id = ?";
            $stmt = $conn->prepare($product_sql);
            $stmt->bind_param("i", $product_id);
            $stmt->execute();
            $product = $stmt->get_result()->fetch_assoc();
            
          if ($product && $product['quantity'] > 0) {
             $quantity = min($item['quantity'], $product['quantity']);
                $purchaseData[] = [
                    'product_id' => $product_id,
                    'product_name' => $product['product_name'],
                    'price' => $product['price'],
                    'quantity' => $quantity,
                    'image_url' => $product['image_url'],
                    'stock_quantity' => $product['quantity']
                ];
                $totalPrice += $product['price'] * $quantity;
            }
        }
    }
}

// Fetch all products for the modify functionality
$sql = "SELECT * FROM products WHERE quantity > 0";
$result = $conn->query($sql);
$products = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase Confirmation | AgroMeds</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        :root {
            --primary: #6a11cb;
            --secondary: #2575fc;
            --success: #28a745;
            --warning: #ffc107;
            --danger: #dc3545;
            --light: #f8f9fa;
            --dark: #343a40;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e8f0 100%);
            min-height: 100vh;
        }

        .purchase-container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 20px;
        }

        .purchase-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.08);
            padding: 25px;
            margin-bottom: 30px;
        }

        .product-img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
            border: 1px solid #eee;
        }

        .quantity-input {
            width: 70px;
            text-align: center;
            padding: 8px;
            border-radius: 5px;
            border: 1px solid #ddd;
        }

        .btn-action {
            border-radius: 20px;
            padding: 8px 20px;
            font-weight: 500;
        }

        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1100;
            display: none;
            padding: 15px 25px;
            border-radius: 8px;
            color: white;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        .notification.show {
            display: block;
            animation: slideIn 0.5s forwards;
        }

        .notification.success {
            background-color: var(--success);
        }

        .notification.error {
            background-color: var(--danger);
        }

        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }

        .popup {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1050;
            overflow-y: auto;
        }

        .popup-content {
            background: white;
            border-radius: 12px;
            margin: 50px auto;
            padding: 25px;
            max-width: 90%;
            width: 800px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.2);
        }

        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }

        .product-card {
            border: 1px solid #eee;
            border-radius: 10px;
            padding: 15px;
            transition: all 0.3s;
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0,0,0,0.1);
        }

        .product-card-img {
            width: 100%;
            height: 120px;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 10px;
        }

        .stock-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background: rgba(0,0,0,0.7);
            color: white;
            padding: 3px 8px;
            border-radius: 10px;
            font-size: 0.8rem;
        }
    </style>
</head>
<body>
    <!-- Navbar (same as your original) -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light sticky-top shadow">
        <!-- Your navbar content -->
    </nav>

    <div class="purchase-container">
        <div class="purchase-card">
            <h2 class="mb-4"><i class="bi bi-cart-check"></i> Purchase Confirmation</h2>
            
            <?php if ($isReorder): ?>
            <div class="alert alert-info mb-4">
                <i class="bi bi-info-circle"></i> You are reordering items from a previous order.
            </div>
            <?php endif; ?>

            <?php if (empty($purchaseData)): ?>
            <div class="text-center py-5">
                <i class="bi bi-cart-x" style="font-size: 3rem; color: #6c757d;"></i>
                <h4 class="mt-3">Your cart is empty</h4>
                <p class="text-muted">There are no items to purchase</p>
                <a href="products.php" class="btn btn-primary mt-3">Browse Products</a>
            </div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <thead class="table-light">
                        <tr>
                            <th>Product</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Total</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($purchaseData as $item): ?>
                        <tr data-product-id="<?= htmlspecialchars($item['product_id']) ?>">
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="<?= htmlspecialchars($item['image_url'] ?? 'images/default-product.jpg') ?>" 
                                         class="product-img me-3" 
                                         alt="<?= htmlspecialchars($item['product_name']) ?>">
                                    <div>
                                        <h6 class="mb-0"><?= htmlspecialchars($item['product_name']) ?></h6>
                                        <small class="text-muted">Available: <?= htmlspecialchars($item['stock_quantity'] ?? 0) ?></small>
                                    </div>
                                </div>
                            </td>
                            <td>₹<?= number_format($item['price'], 2) ?></td>
                            <td>
                                <input type="number" class="quantity-input" 
                                       value="<?= htmlspecialchars($item['quantity']) ?>" 
                                       min="1" 
                                       max="<?= htmlspecialchars($item['stock_quantity'] ?? 10) ?>"
                                       onchange="updateProductTotal(this)">
                            </td>
                            <td class="product-total">₹<?= number_format($item['price'] * $item['quantity'], 2) ?></td>
                            <td>
                                <button class="btn btn-outline-danger btn-sm" onclick="removeProduct(this)">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" class="text-end"><strong>Grand Total:</strong></td>
                            <td id="grand-total" class="fw-bold">₹<?= number_format($totalPrice, 2) ?></td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="d-flex justify-content-between mt-4">
                <button class="btn btn-outline-secondary" onclick="window.history.back()">
                    <i class="bi bi-arrow-left"></i> Back
                </button>
                <div>
                    <button class="btn btn-primary me-2" onclick="openModifyPopup()">
                        <i class="bi bi-plus-circle"></i> Add Products
                    </button>
                    <button class="btn btn-success" onclick="completePurchase()">
                        <i class="bi bi-check-circle"></i> Complete Purchase
                    </button>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Notification div -->
    <div id="notification" class="notification"></div>

    <!-- Modify Popup -->
    <div id="modify-popup" class="popup">
        <div class="popup-content">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3><i class="bi bi-plus-circle"></i> Add Products</h3>
                <button type="button" class="btn-close" onclick="closeModifyPopup()"></button>
            </div>
            
            <div class="input-group mb-3">
                <span class="input-group-text"><i class="bi bi-search"></i></span>
                <input type="text" class="form-control" placeholder="Search products..." id="product-search" onkeyup="searchProducts()">
            </div>
            
            <div class="product-grid" id="product-grid">
                <!-- Products will be loaded here -->
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Pass PHP data to JavaScript
        const products = <?= json_encode($products) ?>;
        const currentCart = <?= json_encode($purchaseData) ?>;
        
        // Initialize the page
        document.addEventListener('DOMContentLoaded', function() {
            // Load products into modify popup
            loadProductGrid();
        });
        
        // Load products into the modify popup grid
        function loadProductGrid(filter = '') {
            const grid = document.getElementById('product-grid');
            grid.innerHTML = '';
            
            const filteredProducts = products.filter(product => 
                product.product_name.toLowerCase().includes(filter.toLowerCase())
            );
            
            if (filteredProducts.length === 0) {
                grid.innerHTML = '<div class="col-12 text-center py-4 text-muted">No products found</div>';
                return;
            }
            
            filteredProducts.forEach(product => {
                const isInCart = currentCart.some(item => item.product_id == product.product_id);
                
                const card = document.createElement('div');
                card.className = 'product-card position-relative';
                card.innerHTML = `
                    <span class="stock-badge">Stock: ${product.quantity}</span>
                    <img src="${product.image_url || 'images/default-product.jpg'}" 
                         class="product-card-img" 
                         alt="${product.product_name}">
                    <h6 class="mb-1">${product.product_name.split(' ').slice(0, 4).join(' ')}</h6>
                    <p class="text-success mb-2">₹${parseFloat(product.price).toFixed(2)}</p>
                    <button class="btn btn-sm ${isInCart ? 'btn-outline-secondary disabled' : 'btn-primary'}" 
                            onclick="addProductToCart(${product.product_id})"
                            ${isInCart ? 'disabled' : ''}>
                        ${isInCart ? 'Added' : 'Add to Cart'}
                    </button>
                `;
                grid.appendChild(card);
            });
        }
        
        // Search products in modify popup
        function searchProducts() {
            const searchTerm = document.getElementById('product-search').value;
            loadProductGrid(searchTerm);
        }
        
        // Open modify popup
        function openModifyPopup() {
            document.getElementById('modify-popup').style.display = 'block';
            loadProductGrid();
        }
        
        // Close modify popup
        function closeModifyPopup() {
            document.getElementById('modify-popup').style.display = 'none';
        }
        
        // Add product to cart
        function addProductToCart(productId) {
            const product = products.find(p => p.product_id == productId);
            if (!product) return;
            
            // Check if already in cart
            if (currentCart.some(item => item.product_id == productId)) {
                showNotification('Product is already in your cart', 'error');
                return;
            }
            
            // Add to cart
            const newItem = {
                product_id: product.product_id,
                product_name: product.product_name,
                price: parseFloat(product.price),
                quantity: 1,
                image_url: product.image_url,
                stock_quantity: parseInt(product.stock_quantity)
            };
            
            currentCart.push(newItem);
            updateCartDisplay(newItem);
            showNotification(`${product.product_name} added to cart`, 'success');
            loadProductGrid(document.getElementById('product-search').value);
        }
        
        // Update cart display with new item
        function updateCartDisplay(item) {
            const tbody = document.querySelector('tbody');
            const row = document.createElement('tr');
            row.setAttribute('data-product-id', item.product_id);
            row.innerHTML = `
                <td>
                    <div class="d-flex align-items-center">
                        <img src="${item.image_url || 'images/default-product.jpg'}" 
                             class="product-img me-3" 
                             alt="${item.product_name}">
                        <div>
                            <h6 class="mb-0">${item.product_name}</h6>
                            <small class="text-muted">Available: ${item.stock_quantity}</small>
                        </div>
                    </div>
                </td>
                <td>₹${item.price.toFixed(2)}</td>
                <td>
                    <input type="number" class="quantity-input" 
                           value="1" min="1" 
                           max="${item.stock_quantity}"
                           onchange="updateProductTotal(this)">
                </td>
                <td class="product-total">₹${item.price.toFixed(2)}</td>
                <td>
                    <button class="btn btn-outline-danger btn-sm" onclick="removeProduct(this)">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            `;
            tbody.appendChild(row);
            
            // Update grand total
            updateGrandTotal();
        }
        
        // Update product total when quantity changes
        function updateProductTotal(input) {
            const row = input.closest('tr');
            const price = parseFloat(row.querySelector('td:nth-child(2)').textContent.replace('₹', ''));
            const quantity = parseInt(input.value);
            const maxQuantity = parseInt(input.max);
            
            // Ensure quantity doesn't exceed available stock
            if (quantity > maxQuantity) {
                input.value = maxQuantity;
                showNotification(`Only ${maxQuantity} available in stock`, 'error');
            }
            
            const total = price * (quantity > maxQuantity ? maxQuantity : quantity);
            row.querySelector('.product-total').textContent = `₹${total.toFixed(2)}`;
            
            // Update grand total
            updateGrandTotal();
        }
        
        // Update grand total
        function updateGrandTotal() {
            let grandTotal = 0;
            document.querySelectorAll('.product-total').forEach(cell => {
                grandTotal += parseFloat(cell.textContent.replace('₹', ''));
            });
            document.getElementById('grand-total').textContent = `₹${grandTotal.toFixed(2)}`;
        }
        
        // Remove product from cart
        function removeProduct(button) {
            const row = button.closest('tr');
            const productId = row.getAttribute('data-product-id');
            
            // Remove from currentCart array
            const index = currentCart.findIndex(item => item.product_id == productId);
            if (index !== -1) {
                currentCart.splice(index, 1);
            }
            
            row.remove();
            updateGrandTotal();
            loadProductGrid(document.getElementById('product-search').value);
            showNotification('Product removed from cart', 'success');
        }
        
        // Complete purchase
        function completePurchase() {
            if (currentCart.length === 0) {
                showNotification('Your cart is empty', 'error');
                return;
            }
            
            // Prepare purchase data
            const purchaseData = currentCart.map(item => {
                const row = document.querySelector(`tr[data-product-id="${item.product_id}"]`);
                const quantity = row ? parseInt(row.querySelector('.quantity-input').value) : 1;
                
                return {
                    product_id: item.product_id,
                    product_name: item.product_name,
                    price: item.price,
                    quantity: quantity,
                    image_url: item.image_url
                };
            });
            
            const grandTotal = parseFloat(document.getElementById('grand-total').textContent.replace('₹', ''));
            
            // Encode data for URL
            const encodedData = encodeURIComponent(JSON.stringify(purchaseData));
            
            // Redirect to checkout page
            window.location.href = `checkout.php?data=${encodedData}&total=${grandTotal}`;
        }
        
        // Show notification
        function showNotification(message, type) {
            const notification = document.getElementById('notification');
            notification.textContent = message;
            notification.className = `notification ${type} show`;
            
            setTimeout(() => {
                notification.classList.remove('show');
            }, 3000);
        }
    </script>
</body>
</html>