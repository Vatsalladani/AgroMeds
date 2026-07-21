<?php
session_start();
require 'vendor/autoload.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.html");
    exit();
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Cancel Orders</title>
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
        .status-pending {
            color: #ffc107;
            font-weight: bold;
        }
        .status-processed {
            color: #28a745;
            font-weight: bold;
        }
        .status-rejected {
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
        <h4>Manage Cancel Orders</h4>
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
                <a href="manage_orders.php" class="nav-link">
                    <i class="fas fa-shopping-cart"></i> Orders
                </a>
                <a href="manage_payment.php" class="nav-link">
                    <i class="fas fa-credit-card"></i> Payments
                </a>
                <a href="manage_cancel_orders.php" class="nav-link active">
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
            <input type="text" id="searchCancelOrder" class="form-control mb-4" placeholder="Search cancel orders...">

            <table class="table table-hover table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Cancel ID</th>
                        <th>Order ID</th>
                        <th>Customer Name</th>
                        <th>Amount</th>
                        <th>Cancel Date</th>
                        <th>Reason</th>
                        <th>Refund Method</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="cancelOrderList">
                    <!-- Cancel orders will be loaded here via AJAX -->
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
    const cancelOrdersPerPage = 5;

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

    function fetchCancelOrders(searchQuery = '', page = 1) {
        $.ajax({
            url: "fetch_cancel_orders.php",
            type: "GET",
            data: { 
                search: searchQuery, 
                page: page, 
                limit: cancelOrdersPerPage 
            },
            success: function (response) {
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
                    const cancelOrders = responseData.data;
                    const totalCancelOrders = responseData.total;
                    const totalPages = Math.ceil(totalCancelOrders / cancelOrdersPerPage);

                    // Update table content
                    let html = "";
                    if (cancelOrders.length > 0) {
                        cancelOrders.forEach((cancelOrder, index) => {
                            // Determine status class
                            let statusClass = '';
                            if (cancelOrder.status === 'Pending') statusClass = 'status-pending';
                            else if (cancelOrder.status === 'Processed') statusClass = 'status-processed';
                            else if (cancelOrder.status === 'Rejected') statusClass = 'status-rejected';

                            // Format cancel date
                            const cancelDate = new Date(cancelOrder.cancellation_date);
                            const formattedDate = cancelDate.toLocaleDateString() + ' ' + cancelDate.toLocaleTimeString();

                            html += `
                                <tr>
                                    <td>${(page - 1) * cancelOrdersPerPage + index + 1}</td>
                                    <td>${cancelOrder.cancellation_id}</td>
                                    <td>${cancelOrder.order_id}</td>
                                    <td>${cancelOrder.customer_name}</td>
                                    <td>₹${parseFloat(cancelOrder.amount || 0).toFixed(2)}</td>
                                    <td>${formattedDate}</td>
                                    <td>${cancelOrder.reason}${cancelOrder.other_reason ? ' (' + cancelOrder.other_reason + ')' : ''}</td>
                                    <td>${cancelOrder.refund_preference}</td>
                                    <td class="${statusClass}">${cancelOrder.status}</td>
                                    <td class="buttons">
                                        <button class="btn btn-info btn-sm viewCancelOrderBtn" data-id="${cancelOrder.cancellation_id}">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-warning btn-sm editCancelOrderBtn" data-id="${cancelOrder.cancellation_id}" ${cancelOrder.status === 'Processed' || cancelOrder.status === 'Rejected' ? 'disabled' : ''}>
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    </td>
                                </tr>
                            `;
                        });
                    } else {
                        html = '<tr><td colspan="10" class="text-center">No cancel orders found.</td></tr>';
                    }
                    $("#cancelOrderList").html(html);

                    // Update pagination controls
                    $("#pageInfo").text(`Page ${page} of ${totalPages}`);
                    $("#prevPageBtn").prop("disabled", page <= 1);
                    $("#nextPageBtn").prop("disabled", page >= totalPages);
                } else {
                    Swal.fire("Error", responseData.message || "Failed to fetch cancel orders", "error");
                }
            },
            error: handleAjaxError
        });
    }

    // Call fetchCancelOrders on page load
    fetchCancelOrders();

    // Search cancel orders
    $("#searchCancelOrder").on("input", function () {
        const searchQuery = $(this).val();
        currentPage = 1;
        fetchCancelOrders(searchQuery, currentPage);
    });

    // Previous page button
    $("#prevPageBtn").click(function () {
        if (currentPage > 1) {
            currentPage--;
            fetchCancelOrders($("#searchCancelOrder").val(), currentPage);
        }
    });

    // Next page button
    $("#nextPageBtn").click(function () {
        currentPage++;
        fetchCancelOrders($("#searchCancelOrder").val(), currentPage);
    });

    // View Cancel Order Details
    $(document).on("click", ".viewCancelOrderBtn", function () {
        const cancellationId = $(this).data("id");

        // Show loading indicator
        Swal.fire({
            title: 'Loading...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        // Fetch cancel order details
        $.ajax({
            url: "get_cancel_order_details.php",
            type: "POST",
            data: { cancellation_id: cancellationId },
            dataType: "json",
            success: function (response) {
                Swal.close();
                
                const result = response;

                if (result.status === "success") {
                    const cancelOrder = result.cancel_order;
                    const order = result.order;
                    const orderItems = result.order_items;

                    // Format dates
                    const orderDate = new Date(order.order_date);
                    const formattedOrderDate = orderDate.toLocaleDateString() + ' ' + orderDate.toLocaleTimeString();
                    
                    const cancelDate = new Date(cancelOrder.cancellation_date);
                    const formattedCancelDate = cancelDate.toLocaleDateString() + ' ' + cancelDate.toLocaleTimeString();

                    // Determine status class
                    let statusClass = '';
                    if (cancelOrder.status === 'Pending') statusClass = 'status-pending';
                    else if (cancelOrder.status === 'Processed') statusClass = 'status-processed';
                    else if (cancelOrder.status === 'Rejected') statusClass = 'status-rejected';

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

                    // Display cancel order details in a SweetAlert2 modal
                    Swal.fire({
                        title: `<h3>Cancel Order #${cancelOrder.cancellation_id}</h3>`,
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
                                        <h5>Cancellation Information</h5>
                                        <p><strong>Order ID:</strong> ${order.order_id}</p>
                                        <p><strong>Order Date:</strong> ${formattedOrderDate}</p>
                                        <p><strong>Cancel Date:</strong> ${formattedCancelDate}</p>
                                        <p><strong>Status:</strong> <span class="${statusClass}">${cancelOrder.status}</span></p>
                                        <p><strong>Reason:</strong> ${cancelOrder.reason}${cancelOrder.other_reason ? ' (' + cancelOrder.other_reason + ')' : ''}</p>
                                        <p><strong>Refund Method:</strong> ${cancelOrder.refund_preference}</p>
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
                                                <th>₹${parseFloat(order.total_amount || 0).toFixed(2)}</th>
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
                    Swal.fire("Error", result.message || "Failed to fetch cancel order details.", "error");
                }
            },
            error: function (xhr, status, error) {
                Swal.close();
                let errorMsg = "An error occurred while fetching cancel order details.";
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

    // Edit Cancel Order Status
    $(document).on("click", ".editCancelOrderBtn", function () {
        const cancellationId = $(this).data("id");

        // Show loading indicator
        Swal.fire({
            title: 'Loading...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        // Fetch cancel order details for editing
        $.ajax({
            url: "get_cancel_order_details.php",
            type: "POST",
            data: { cancellation_id: cancellationId },
            dataType: "json",
            success: function (response) {
                Swal.close();
                
                const result = response;

                if (result.status === "success") {
                    const cancelOrder = result.cancel_order;

                    // Display edit form in a SweetAlert2 modal
                    Swal.fire({
                        title: 'Update Cancellation Status',
                        html: `
                            <div class="container text-start">
                                <div class="mb-3">
                                    <label for="editCancelStatus" class="form-label">Status</label>
                                    <select id="editCancelStatus" class="form-select">
                                        <option value="Processed" ${cancelOrder.status === 'Processed' ? 'selected' : ''}>Processed</option>
                                        <option value="Rejected" ${cancelOrder.status === 'Rejected' ? 'selected' : ''}>Rejected</option>
                                    </select>
                                </div>
                                <div class="mb-3" id="rejectionReasonContainer" style="display: none;">
                                    <label for="rejectionReason" class="form-label">Rejection Reason</label>
                                    <textarea id="rejectionReason" class="form-control" rows="3" placeholder="Please provide reason for rejection"></textarea>
                                </div>
                                <div class="mb-3" id="refundDateContainer" style="display: none;">
                                    <label for="refundDate" class="form-label">Expected Refund Date</label>
                                    <input type="date" id="refundDate" class="form-control" min="${new Date().toISOString().split('T')[0]}">
                                </div>
                            </div>
                        `,
                        showCancelButton: true,
                        confirmButtonText: 'Update',
                        didOpen: () => {
                            // Show/hide fields based on status selection
                            $('#editCancelStatus').change(function() {
                                if ($(this).val() === 'Rejected') {
                                    $('#rejectionReasonContainer').show();
                                    $('#refundDateContainer').hide();
                                    $('#rejectionReason').prop('required', true);
                                } else {
                                    $('#rejectionReasonContainer').hide();
                                    $('#refundDateContainer').show();
                                    $('#rejectionReason').prop('required', false);
                                }
                            });
                            
                            // Trigger change to set initial state
                            $('#editCancelStatus').trigger('change');
                        },
                        preConfirm: () => {
                            const status = $('#editCancelStatus').val();
                            const data = {
                                cancellation_id: cancellationId,
                                status: status
                            };

                            if (status === 'Rejected') {
                                const rejectionReason = $('#rejectionReason').val();
                                if (!rejectionReason) {
                                    Swal.showValidationMessage('Please provide rejection reason');
                                    return false;
                                }
                                data.rejection_reason = rejectionReason;
                            } else {
                                const refundDate = $('#refundDate').val();
                                if (!refundDate) {
                                    Swal.showValidationMessage('Please select expected refund date');
                                    return false;
                                }
                                data.refund_date = refundDate;
                            }

                            return data;
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
                                url: 'update_cancel_order.php',
                                method: 'POST',
                                data: data,
                                dataType: 'json',
                                success: function (response) {
                                    Swal.close();
                                    if (response.status === 'success') {
                                        Swal.fire('Success', 'Cancellation status updated successfully!', 'success');
                                        
                                        // If status was changed to Processed, also update the order status
                                        if (data.status === 'Processed') {
                                            updateOrderStatus(cancelOrder.order_id, 'Cancelled');
                                        }
                                        
                                        fetchCancelOrders();
                                    } else {
                                        Swal.fire('Error', response.message || 'Failed to update cancellation status.', 'error');
                                    }
                                },
                                error: function (xhr, status, error) {
                                    Swal.close();
                                    let errorMsg = "An error occurred while updating cancellation status.";
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
                    Swal.fire("Error", result.message || "Failed to fetch cancel order details.", "error");
                }
            },
            error: function (xhr, status, error) {
                Swal.close();
                let errorMsg = "An error occurred while fetching cancel order details.";
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

    // Function to update order status
    function updateOrderStatus(orderId, status) {
        $.ajax({
            url: 'update_order_status.php',
            method: 'POST',
            data: { 
                order_id: orderId, 
                status: status 
            },
            dataType: 'json',
            success: function(response) {
                if (response.status !== 'success') {
                    console.error('Failed to update order status:', response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error updating order status:', error);
            }
        });
    }
});
    </script>
</body>
</html>