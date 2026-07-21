<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Orders</title>
    <!-- Libraries -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3f37c9;
            --accent-color: #4895ef;
            --success-color: #4cc9f0;
            --warning-color: #f8961e;
            --danger-color: #f72585;
            --light-color: #f8f9fa;
            --dark-color: #212529;
        }
        body {
            display: flex;
            min-height: 100vh;
            flex-direction: column;
            background: #f8f9fa;
        }
        .top-bar {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            position: relative;
            z-index: 100;
        }
        
        .top-bar a {
            color: white;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .top-bar a:hover {
            transform: translateY(-2px);
        }
        
        .sidebar {
            width: 280px;
            height: 100vh;
            background: linear-gradient(180deg, var(--dark-color), #2a2e33);
            color: white;
            flex-shrink: 0;
            position: sticky;
            top: 0;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
            overflow-y: auto;
        }
        
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 12px 25px;
            margin: 2px 10px;
            border-radius: 8px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
        }
        
        .sidebar .nav-link i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        
        .sidebar .nav-link:hover {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            transform: translateX(5px);
        }
        
        .sidebar .nav-link.active {
            background: var(--primary-color);
            color: white;
            box-shadow: 0 4px 8px rgba(67, 97, 238, 0.3);
        }
        
        .sidebar h4 {
            text-align: center;
            padding: 25px 0;
            margin-bottom: 10px;
            background: rgba(0, 0, 0, 0.2);
            font-weight: 600;
        }
        .content {
            flex-grow: 1;
            padding: 20px;
        }
        .status-processing {
            color: #ffc107;
            font-weight: bold;
        }
        .status-shipped {
            color: #17a2b8;
            font-weight: bold;
        }
        .status-delivered {
            color: #28a745;
            font-weight: bold;
        }
        .status-cancelled {
            color: #dc3545;
            font-weight: bold;
        }
        .payment-pending {
            color: #ffc107;
            font-weight: bold;
        }
        .payment-completed {
            color: #28a745;
            font-weight: bold;
        }
        .payment-failed {
            color: #dc3545;
            font-weight: bold;
        }
        .sidebar .nav-link.active {
            background-color: rgba(255, 255, 255, 0.1);
        }
        .buttons {
            display: flex;
            gap: 5px;
            justify-content: center;
        }
        /* Custom Pagination Styling */
        .pagination {
            position: fixed;
            bottom: 20px;
            left: 55%;
            transform: translateX(-50%);
            display: flex;
            justify-content: center;
            background: #fff;
            padding: 10px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .pagination .page-link {
            color: #343a40;
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            margin: 0 5px;
            transition: all 0.3s ease;
            padding: 8px 12px;
            border-radius: 5px;
        }
        .pagination .page-link:hover {
            color: #fff;
            background-color: #343a40;
            border-color: #343a40;
        }
        .pagination .page-item.disabled .page-link {
            color: #6c757d;
            background-color: #f8f9fa;
            border-color: #dee2e6;
        }
        .pagination .page-item.active .page-link {
            color: #fff;
            background-color: #007bff;
            border-color: #007bff;
        }
    </style>
</head>
<body>
    <!-- Top Bar -->
    <div class="top-bar">
        <h4>Manage Orders</h4>
        <a href="logout.php" class="btn btn-danger">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>

    <!-- Sidebar -->
    <div class="d-flex">
        <!-- Sidebar -->
        <div class="sidebar">
            <h4><i class="fas fa-user-shield me-2"></i> Admin Panel</h4>
            <nav class="nav flex-column">
                <a href="admin_dashboard.php" class="nav-link ">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
                <a href="manage_admins.php" class="nav-link">
                    <i class="fas fa-user-shield"></i> Admins
                </a>
                <a href="manage_categories.php" class="nav-link">
                    <i class="fas fa-tags"></i> Categories
                </a>
                <a href="manage_products.php" class="nav-link">
                    <i class="fas fa-box"></i> Products
                </a>
                <a href="manage_orders.php" class="nav-link active">
                    <i class="fas fa-shopping-cart"></i> Orders
                </a>
                <a href="manage_payment.php" class="nav-link">
                    <i class="fas fa-credit-card"></i> Payments
                </a>
                <a href="manage_cancel_orders.php" class="nav-link">
                    <i class="fas fa-ban"></i> Cancel Orders
                </a>
                <a href="manage_experts.php" class="nav-link">
                    <i class="fas fa-user-tie"></i> Experts
                </a>
                <a href="manage_users.php" class="nav-link">
                    <i class="fas fa-users"></i> Users
                </a>
                <a href="manage_consultations.php" class="nav-link ">
                    <i class="fas fa-calendar-check"></i> Consultations
                </a>
                <a href="manage_contactUs.php" class="nav-link">
                    <i class="fas fa-envelope"></i> Contact Queries
                </a>
                <a href="manage_feedbacks.php" class="nav-link">
                    <i class="fas fa-comments"></i> Feedbacks
                </a>
                <a href="manage_testimonials.php" class="nav-link ">
                    <i class="fas fa-quote-left"></i> Testimonials
                </a>
            </nav>
        </div>



        <!-- Main Content -->
        <div class="content">
            <input type="text" id="searchOrder" class="form-control mb-4" placeholder="Search orders...">

            <table class="table table-hover table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Order ID</th>
                        <th>Customer Name</th>
                        <th>Total Amount</th>
                        <th>Order Date</th>
                        <th>Status</th>
                        <th>Payment Method</th>
                        <th>Payment Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="orderList">
                    <!-- Orders will be loaded here via AJAX -->
                </tbody>
            </table>
            <nav aria-label="Page navigation">
                <ul class="pagination">
                    <li class="page-item" id="prevPageBtn">
                        <a class="page-link" href="#" aria-label="Previous">
                            <span aria-hidden="true">&laquo;</span>
                            <span class="sr-only">Previous</span>
                        </a>
                    </li>
                    <li class="page-item disabled">
                        <span class="page-link" id="pageInfo">Page 1</span>
                    </li>
                    <li class="page-item" id="nextPageBtn">
                        <a class="page-link" href="#" aria-label="Next">
                            <span aria-hidden="true">&raquo;</span>
                            <span class="sr-only">Next</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </div>

    <script>
$(document).ready(function () {
    let currentPage = 1;
    const ordersPerPage = 8;

    function handleAjaxError(xhr, status, error) {
        let errorMsg = "An error occurred";
        try {
            const response = JSON.parse(xhr.responseText);
            errorMsg = response.message || errorMsg;
        } catch (e) {
            errorMsg = xhr.responseText || errorMsg;
        }
        console.error("Error:", status, error, xhr.responseText);
        Swal.fire("Error", errorMsg, "error");
    }

    function fetchOrders(searchQuery = '', page = 1) {
    $.ajax({
        url: "fetch_orders.php",
        type: "GET",
        data: { 
            search: searchQuery, 
            page: page, 
            limit: ordersPerPage 
        },
        success: function (response) {
            // Handle both JSON and plain text responses
            let responseData;
            if (typeof response === 'string') {
                try {
                    responseData = JSON.parse(response);
                } catch (e) {
                    console.error("Failed to parse response:", response);
                    Swal.fire("Error", "Invalid server response", "error");
                    return;
                }
            } else {
                responseData = response;
            }

            if (responseData.status === "success") {
                const orders = responseData.data;
                const totalOrders = responseData.total;
                const totalPages = Math.ceil(totalOrders / ordersPerPage);

                // Update table content
                let html = "";
                if (orders.length > 0) {
                    orders.forEach((order, index) => {
                        // Ensure total_amount is a number
                        const totalAmount = parseFloat(order.total_amount) || 0;
                        
                        // Determine status class
                        let statusClass = '';
                        if (order.status === 'Processing') statusClass = 'status-processing';
                        else if (order.status === 'Shipped') statusClass = 'status-shipped';
                        else if (order.status === 'Delivered') statusClass = 'status-delivered';
                        else if (order.status === 'Cancelled') statusClass = 'status-cancelled';

                        // Determine payment status class
                        let paymentStatusClass = '';
                        if (order.payment_status === 'Pending') paymentStatusClass = 'payment-pending';
                        else if (order.payment_status === 'Completed') paymentStatusClass = 'payment-completed';
                        else if (order.payment_status === 'Failed') paymentStatusClass = 'payment-failed';

                        // Format order date
                        const orderDate = new Date(order.order_date);
                        const formattedDate = orderDate.toLocaleDateString() + ' ' + orderDate.toLocaleTimeString();

                        html += `
                            <tr>
                                <td>${(page - 1) * ordersPerPage + index + 1}</td>
                                <td>${order.order_id}</td>
                                <td>${order.customer_name}</td>
                                <td>₹${totalAmount.toFixed(2)}</td>
                                <td>${formattedDate}</td>
                                <td class="${statusClass}">${order.status}</td>
                                <td>${order.payment_method || 'N/A'}</td>
                                <td class="${paymentStatusClass}">${order.payment_status}</td>
                                <td class="buttons">
                                    <button class="btn btn-info btn-sm viewOrderBtn" data-id="${order.order_id}">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-warning btn-sm editOrderBtn" data-id="${order.order_id}">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-danger btn-sm deleteOrderBtn" data-id="${order.order_id}">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        `;
                    });
                } else {
                    html = '<tr><td colspan="9" class="text-center">No orders found.</td></tr>';
                }
                $("#orderList").html(html);

                // Update pagination controls
                $("#pageInfo").text(`Page ${page} of ${totalPages}`);
                $("#prevPageBtn").prop("disabled", page <= 1);
                $("#nextPageBtn").prop("disabled", page >= totalPages);
            } else {
                Swal.fire("Error", responseData.message || "Failed to fetch orders", "error");
            }
        },
        error: handleAjaxError
    });
}

            // Call fetchOrders on page load
            fetchOrders();

            // Search orders
            $("#searchOrder").on("input", function () {
                const searchQuery = $(this).val();
                currentPage = 1;
                fetchOrders(searchQuery, currentPage);
            });

            // Previous page button
            $("#prevPageBtn").click(function () {
                if (currentPage > 1) {
                    currentPage--;
                    fetchOrders($("#searchOrder").val(), currentPage);
                }
            });

            // Next page button
            $("#nextPageBtn").click(function () {
                currentPage++;
                fetchOrders($("#searchOrder").val(), currentPage);
            });

            // View Order
            $(document).on("click", ".viewOrderBtn", function () {
                const orderId = $(this).data("id");

                // Show loading indicator
                Swal.fire({
                    title: 'Loading...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                // Fetch order details
                $.ajax({
                    url: "get_order_details.php",
                    type: "POST",
                    data: { order_id: orderId },
                    dataType: "json", // Expect JSON response
                    success: function (response) {
                        Swal.close();
                        
                        // No need to parse if dataType is json and server sends proper JSON
                        const result = response;

                        if (result.status === "success") {
                            const order = result.order;
                            const orderItems = result.order_items;

                            // Format order date
                            const orderDate = new Date(order.order_date);
                            const formattedDate = orderDate.toLocaleDateString() + ' ' + orderDate.toLocaleTimeString();

                            // Determine status class
                            let statusClass = '';
                            if (order.status === 'Processing') statusClass = 'status-processing';
                            else if (order.status === 'Shipped') statusClass = 'status-shipped';
                            else if (order.status === 'Delivered') statusClass = 'status-delivered';
                            else if (order.status === 'Cancelled') statusClass = 'status-cancelled';

                            // Determine payment status class
                            let paymentStatusClass = '';
                            if (order.payment_status === 'Pending') paymentStatusClass = 'payment-pending';
                            else if (order.payment_status === 'Completed') paymentStatusClass = 'payment-completed';
                            else if (order.payment_status === 'Failed') paymentStatusClass = 'payment-failed';

                            // Ensure total_amount is a number
                            const totalAmount = parseFloat(order.total_amount) || 0;

                            // Build order items table
                            let itemsHtml = '';
                            if (orderItems && orderItems.length > 0) {
                                orderItems.forEach(item => {
                                    const itemPrice = parseFloat(item.price) || 0;
                                    const itemQuantity = parseInt(item.quantity) || 0;
                                    
                                    itemsHtml += `
                                        <tr>
                                            <td>${item.product_name}</td>
                                            <td>${item.quantity}</td>
                                            <td>₹${itemPrice.toFixed(2)}</td>
                                            <td>₹${(itemPrice * itemQuantity).toFixed(2)}</td>
                                        </tr>
                                    `;
                                });
                            } else {
                                itemsHtml = '<tr><td colspan="4" class="text-center">No items found for this order.</td></tr>';
                            }

                            // Display order details in a SweetAlert2 modal
                            Swal.fire({
                                title: `<h3>Order #${order.order_id}</h3>`,
                                html: `
                                    <div class="container text-start">
                                        <div class="row mb-4">
                                            <div class="col-md-6">
                                                <h5>Customer Details</h5>
                                                <p><strong>Name:</strong> ${order.customer_name}</p>
                                                <p><strong>Email:</strong> ${order.email}</p>
                                                <p><strong>Phone:</strong> ${order.phone}</p>
                                                <p><strong>Address:</strong> ${order.address}, ${order.pincode}</p>
                                            </div>
                                            <div class="col-md-6">
                                                <h5>Order Information</h5>
                                                <p><strong>Order Date:</strong> ${formattedDate}</p>
                                                <p><strong>Status:</strong> <span class="${statusClass}">${order.status}</span></p>
                                                <p><strong>Payment Method:</strong> ${order.payment_method || 'N/A'}</p>
                                                <p><strong>Payment Status:</strong> <span class="${paymentStatusClass}">${order.payment_status}</span></p>
                                                <p><strong>Payment ID:</strong> ${order.payment_id || 'N/A'}</p>
                                            </div>
                                        </div>
                                        
                                        <h5>Order Items</h5>
                                        <div class="table-responsive">
                                            <table class="table table-bordered">
                                                <thead>
                                                    <tr>
                                                        <th>Product Name</th>
                                                        <th>Quantity</th>
                                                        <th>Unit Price</th>
                                                        <th>Total</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    ${itemsHtml}
                                                </tbody>
                                                <tfoot>
                                                    <tr>
                                                        <th colspan="3" class="text-end">Total Amount:</th>
                                                        <th>₹${totalAmount.toFixed(2)}</th>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                        
                                        <div class="text-center mt-3">
                                            <button class="btn btn-primary" onclick="Swal.close()">Close</button>
                                        </div>
                                    </div>
                                `,
                                showConfirmButton: false,
                                customClass: {
                                    popup: 'border-radius-xl',
                                },
                                width: '900px',
                            });
                        } else {
                            Swal.fire("Error", result.message || "Failed to fetch order details.", "error");
                        }
                    },
                    error: function (xhr, status, error) {
                        Swal.close();
                        let errorMsg = "An error occurred while fetching order details.";
                        try {
                            const response = JSON.parse(xhr.responseText);
                            errorMsg = response.message || errorMsg;
                        } catch (e) {
                            errorMsg = xhr.responseText || errorMsg;
                        }
                        Swal.fire("Error", errorMsg, "error");
                    }
                });
            });

                        // Edit Order
            $(document).on("click", ".editOrderBtn", function () {
                const orderId = $(this).data("id");

                // Show loading indicator
                Swal.fire({
                    title: 'Loading...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                // Fetch order details for editing
                $.ajax({
                    url: "get_order_details.php",
                    type: "POST",
                    data: { order_id: orderId },
                    dataType: "json", // Expect JSON response
                    success: function (response) {
                        Swal.close();
                        
                        // No need to parse if dataType is json
                        const result = response;

                        if (result.status === "success") {
                            const order = result.order;

                            // Display edit form in a SweetAlert2 modal
                            Swal.fire({
                                title: 'Edit Order Status',
                                html: `
                                    <div class="container text-start">
                                        <div class="mb-3">
                                            <label for="editStatus" class="form-label">Order Status</label>
                                            <select id="editStatus" class="form-select">
                                                <option value="Processing" ${order.status === 'Processing' ? 'selected' : ''}>Processing</option>
                                                <option value="Shipped" ${order.status === 'Shipped' ? 'selected' : ''}>Shipped</option>
                                                <option value="Delivered" ${order.status === 'Delivered' ? 'selected' : ''}>Delivered</option>
                                                <option value="Cancelled" ${order.status === 'Cancelled' ? 'selected' : ''}>Cancelled</option>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label for="editPaymentStatus" class="form-label">Payment Status</label>
                                            <select id="editPaymentStatus" class="form-select">
                                                <option value="Pending" ${order.payment_status === 'Pending' ? 'selected' : ''}>Pending</option>
                                                <option value="Completed" ${order.payment_status === 'Completed' ? 'selected' : ''}>Completed</option>
                                                <option value="Failed" ${order.payment_status === 'Failed' ? 'selected' : ''}>Failed</option>
                                            </select>
                                        </div>
                                    </div>
                                `,
                                showCancelButton: true,
                                confirmButtonText: 'Update',
                                preConfirm: () => {
                                    return {
                                        order_id: orderId,
                                        status: $('#editStatus').val(),
                                        payment_status: $('#editPaymentStatus').val()
                                    };
                                }
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    const data = result.value;

                                    // Show loading for update
                                    Swal.fire({
                                        title: 'Updating...',
                                        allowOutsideClick: false,
                                        didOpen: () => {
                                            Swal.showLoading();
                                        }
                                    });

                                    $.ajax({
                                        url: 'update_orders.php',
                                        method: 'POST',
                                        data: data,
                                        dataType: 'json',
                                        success: function (response) {
                                            Swal.close();
                                            if (response.status === 'success') {
                                                Swal.fire('Success', 'Order updated successfully!', 'success');
                                                fetchOrders();
                                            } else {
                                                Swal.fire('Error', response.message || 'Failed to update order.', 'error');
                                            }
                                        },
                                        error: function (xhr, status, error) {
                                            Swal.close();
                                            let errorMsg = "An error occurred while updating the order.";
                                            try {
                                                const response = JSON.parse(xhr.responseText);
                                                errorMsg = response.message || errorMsg;
                                            } catch (e) {
                                                errorMsg = xhr.responseText || errorMsg;
                                            }
                                            Swal.fire('Error', errorMsg, 'error');
                                        }
                                    });
                                }
                            });
                        } else {
                            Swal.fire("Error", result.message || "Failed to fetch order details.", "error");
                        }
                    },
                    error: function (xhr, status, error) {
                        Swal.close();
                        let errorMsg = "An error occurred while fetching order details.";
                        try {
                            const response = JSON.parse(xhr.responseText);
                            errorMsg = response.message || errorMsg;
                        } catch (e) {
                            errorMsg = xhr.responseText || errorMsg;
                        }
                        Swal.fire("Error", errorMsg, "error");
                    }
                });
            });

            // Delete Order
            $(document).on('click', '.deleteOrderBtn', function () {
                const orderId = $(this).data('id');
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: 'delete_order.php',
                            method: 'POST',
                            data: { order_id: orderId },
                            success: function (response) {
                                const res = JSON.parse(response);
                                if (res.status === 'success') {
                                    Swal.fire('Deleted!', 'Order has been deleted.', 'success');
                                    fetchOrders();
                                } else {
                                    Swal.fire('Error', res.message || 'Failed to delete order.', 'error');
                                }
                            },
                            error: function () {
                                Swal.fire('Error', 'An error occurred while deleting the order.', 'error');
                            }
                        });
                    }
                });
            });
        });
    </script>
</body>
</html>