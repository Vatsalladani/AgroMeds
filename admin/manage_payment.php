<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.html");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Payments</title>
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
        .status-completed {
            color: #28a745;
            font-weight: bold;
        }
        .status-failed {
            color: #dc3545;
            font-weight: bold;
        }
        .method-upi {
            color: #6f42c1;
            font-weight: bold;
        }
        .method-card {
            color: #17a2b8;
            font-weight: bold;
        }
        .method-netbanking {
            color: #fd7e14;
            font-weight: bold;
        }
        .method-cod {
            color: #20c997;
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
        .payment-details {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-top: 10px;
            font-family: monospace;
            max-height: 200px;
            overflow-y: auto;
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
        .badge-new {
            background-color: #17a2b8;
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
    </style>
</head>
<body>
    <!-- Top Bar -->
    <div class="top-bar">
        <h4>Manage Payments</h4>
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
                <a href="manage_payment.php" class="nav-link active">
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
            <div class="d-flex justify-content-between mb-4">
                <input type="text" id="searchPayment" class="form-control" placeholder="Search payments..." style="width: 300px;">
                <div>
                    <button class="btn btn-primary" id="refreshBtn">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </button>
                </div>
            </div>

            <table class="table table-hover table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Payment ID</th>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Amount</th>
                        <th>Method</th>
                        <th>Status</th>
                        <th>Transaction ID</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="paymentList">
                    <!-- Payments will be loaded here via AJAX -->
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
        const paymentsPerPage = 8;

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

        function fetchPayments(searchQuery = '', page = 1) {
            $.ajax({
                url: "fetch_payments.php",
                type: "GET",
                data: { 
                    search: searchQuery, 
                    page: page, 
                    limit: paymentsPerPage 
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
                        const payments = responseData.data;
                        const totalPayments = responseData.total;
                        const totalPages = Math.ceil(totalPayments / paymentsPerPage);

                        let html = "";
                        if (payments.length > 0) {
                            payments.forEach((payment, index) => {
                                const amount = parseFloat(payment.amount) || 0;
                                const paymentDate = new Date(payment.created_at);
                                const formattedDate = paymentDate.toLocaleDateString() + ' ' + paymentDate.toLocaleTimeString();
                                
                                // Determine status class
                                let statusClass = '';
                                if (payment.payment_status === 'Pending') statusClass = 'status-pending';
                                else if (payment.payment_status === 'Completed') statusClass = 'status-completed';
                                else if (payment.payment_status === 'Failed') statusClass = 'status-failed';

                                // Determine method class
                                let methodClass = '';
                                if (payment.payment_method === 'upi') methodClass = 'method-upi';
                                else if (payment.payment_method === 'card') methodClass = 'method-card';
                                else if (payment.payment_method === 'netbanking') methodClass = 'method-netbanking';
                                else if (payment.payment_method === 'cod') methodClass = 'method-cod';

                                // Format payment details
                                let paymentDetails = {};
                                try {
                                    paymentDetails = payment.payment_details ? JSON.parse(payment.payment_details) : {};
                                } catch (e) {
                                    console.error("Error parsing payment details:", e);
                                }

                                // Highlight new payments (within last 24 hours)
                                const isNew = (new Date() - new Date(payment.created_at)) < (24 * 60 * 60 * 1000);
                                const newBadge = isNew ? '<span class="badge badge-new ms-2">New</span>' : '';

                                html += `
                                    <tr>
                                        <td>${(page - 1) * paymentsPerPage + index + 1}</td>
                                        <td>${payment.payment_id}${newBadge}</td>
                                        <td>${payment.order_id}</td>
                                        <td>${payment.customer_name || 'N/A'}</td>
                                        <td>₹${amount.toFixed(2)}</td>
                                        <td class="${methodClass} text-capitalize">${payment.payment_method}</td>
                                        <td class="${statusClass}">${payment.payment_status}</td>
                                        <td>${payment.transaction_id || 'N/A'}</td>
                                        <td>${formattedDate}</td>
                                        <td class="buttons">
                                            <button class="btn btn-info btn-sm viewPaymentBtn" data-id="${payment.payment_id}">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-warning btn-sm editPaymentBtn" data-id="${payment.payment_id}">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-danger btn-sm refundPaymentBtn" data-id="${payment.payment_id}" ${payment.payment_status !== 'Completed' ? 'disabled' : ''}>
                                                <i class="fas fa-undo"></i>
                                            </button>
                                        </td>
                                    </tr>
                                `;
                            });
                        } else {
                            html = '<tr><td colspan="10" class="text-center">No payments found.</td></tr>';
                        }
                        $("#paymentList").html(html);

                        // Update pagination controls
                        $("#pageInfo").text(`Page ${page} of ${totalPages}`);
                        $("#prevPageBtn").prop("disabled", page <= 1);
                        $("#nextPageBtn").prop("disabled", page >= totalPages);
                    } else {
                        Swal.fire("Error", responseData.message || "Failed to fetch payments", "error");
                    }
                },
                error: handleAjaxError
            });
        }

        // Initial load
        fetchPayments();

        // Search payments
        $("#searchPayment").on("input", function () {
            const searchQuery = $(this).val();
            currentPage = 1;
            fetchPayments(searchQuery, currentPage);
        });

        // Refresh button
        $("#refreshBtn").click(function() {
            fetchPayments($("#searchPayment").val(), currentPage);
            $(this).html('<i class="fas fa-sync-alt fa-spin"></i> Refreshing...');
            setTimeout(() => {
                $(this).html('<i class="fas fa-sync-alt"></i> Refresh');
            }, 1000);
        });

        // Previous page button
        $("#prevPageBtn").click(function () {
            if (currentPage > 1) {
                currentPage--;
                fetchPayments($("#searchPayment").val(), currentPage);
            }
        });

        // Next page button
        $("#nextPageBtn").click(function () {
            currentPage++;
            fetchPayments($("#searchPayment").val(), currentPage);
        });

        // View Payment Details
        $(document).on("click", ".viewPaymentBtn", function () {
            const paymentId = $(this).data("id");

            Swal.fire({
                title: 'Loading...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: "get_payment_details.php",
                type: "POST",
                data: { payment_id: paymentId },
                dataType: "json",
                success: function (response) {
                    Swal.close();
                    
                    if (response.status === "success") {
                        const payment = response.payment;
                        const order = response.order || {};
                        const paymentDate = new Date(payment.created_at);
                        const formattedDate = paymentDate.toLocaleDateString() + ' ' + paymentDate.toLocaleTimeString();
                        
                        // Parse payment details
                        let paymentDetails = {};
                        try {
                            paymentDetails = payment.payment_details ? JSON.parse(payment.payment_details) : {};
                        } catch (e) {
                            console.error("Error parsing payment details:", e);
                        }

                        // Format payment details for display
                        let paymentDetailsHtml = '';
                        for (const [key, value] of Object.entries(paymentDetails)) {
                            paymentDetailsHtml += `<p><strong>${key}:</strong> ${value}</p>`;
                        }

                        Swal.fire({
                            title: `<h3>Payment #${payment.payment_id}</h3>`,
                            html: `
                                <div class="container text-start">
                                    <div class="row mb-4">
                                        <div class="col-md-6">
                                            <h5>Payment Information</h5>
                                            <p><strong>Order ID:</strong> ${payment.order_id}</p>
                                            <p><strong>Customer:</strong> ${order.customer_name || 'N/A'}</p>
                                            <p><strong>Amount:</strong> ₹${parseFloat(payment.amount).toFixed(2)}</p>
                                            <p><strong>Method:</strong> <span class="text-capitalize">${payment.payment_method}</span></p>
                                        </div>
                                        <div class="col-md-6">
                                            <h5>Status & Details</h5>
                                            <p><strong>Status:</strong> <span class="${payment.payment_status === 'Completed' ? 'status-completed' : payment.payment_status === 'Pending' ? 'status-pending' : 'status-failed'}">${payment.payment_status}</span></p>
                                            <p><strong>Transaction ID:</strong> ${payment.transaction_id || 'N/A'}</p>
                                            <p><strong>Date:</strong> ${formattedDate}</p>
                                            <p><strong>Updated At:</strong> ${new Date(payment.updated_at).toLocaleString()}</p>
                                        </div>
                                    </div>
                                    
                                    <h5>Payment Details</h5>
                                    <div class="payment-details">
                                        ${paymentDetailsHtml || '<p>No additional details available</p>'}
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
                            width: '800px',
                        });
                    } else {
                        Swal.fire("Error", response.message || "Failed to fetch payment details.", "error");
                    }
                },
                error: handleAjaxError
            });
        });

        // Edit Payment Status
        $(document).on("click", ".editPaymentBtn", function () {
            const paymentId = $(this).data("id");

            Swal.fire({
                title: 'Loading...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: "get_payment_details.php",
                type: "POST",
                data: { payment_id: paymentId },
                dataType: "json",
                success: function (response) {
                    Swal.close();
                    
                    if (response.status === "success") {
                        const payment = response.payment;

                        Swal.fire({
                            title: 'Update Payment Status',
                            html: `
                                <div class="container text-start">
                                    <div class="mb-3">
                                        <label class="form-label">Payment Status</label>
                                        <select id="editPaymentStatus" class="form-select">
                                            <option value="Pending" ${payment.payment_status === 'Pending' ? 'selected' : ''}>Pending</option>
                                            <option value="Completed" ${payment.payment_status === 'Completed' ? 'selected' : ''}>Completed</option>
                                            <option value="Failed" ${payment.payment_status === 'Failed' ? 'selected' : ''}>Failed</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Transaction ID</label>
                                        <input type="text" id="editTransactionId" class="form-control" value="${payment.transaction_id || ''}" placeholder="Enter transaction ID">
                                    </div>
                                    <div class="form-check mb-3">
                                        <input class="form-check-input" type="checkbox" id="updateOrderStatus">
                                        <label class="form-check-label" for="updateOrderStatus">
                                            Also update order payment status
                                        </label>
                                    </div>
                                    <div class="form-check mb-3">
                                        <input class="form-check-input" type="checkbox" id="notifyUser" checked>
                                        <label class="form-check-label" for="notifyUser">
                                            Notify user via email
                                        </label>
                                    </div>
                                </div>
                            `,
                            showCancelButton: true,
                            confirmButtonText: 'Update',
                            preConfirm: () => {
                                return {
                                    payment_id: paymentId,
                                    payment_status: $('#editPaymentStatus').val(),
                                    transaction_id: $('#editTransactionId').val(),
                                    update_order: $('#updateOrderStatus').is(':checked'),
                                    notify_user: $('#notifyUser').is(':checked')
                                };
                            }
                        }).then((result) => {
                            if (result.isConfirmed) {
                                const data = result.value;

                                Swal.fire({
                                    title: 'Updating...',
                                    allowOutsideClick: false,
                                    didOpen: () => {
                                        Swal.showLoading();
                                    }
                                });

                                $.ajax({
                                    url: 'update_payment.php',
                                    method: 'POST',
                                    data: data,
                                    dataType: 'json',
                                    success: function (response) {
                                        Swal.close();
                                        if (response.status === 'success') {
                                            Swal.fire('Success', 'Payment updated successfully!', 'success');
                                            fetchPayments();
                                        } else {
                                            Swal.fire('Error', response.message || 'Failed to update payment.', 'error');
                                        }
                                    },
                                    error: handleAjaxError
                                });
                            }
                        });
                    } else {
                        Swal.fire("Error", response.message || "Failed to fetch payment details.", "error");
                    }
                },
                error: handleAjaxError
            });
        });

        // Refund Payment
        $(document).on('click', '.refundPaymentBtn', function () {
            const paymentId = $(this).data('id');
            
            Swal.fire({
                title: 'Initiate Refund',
                html: `
                    <div class="container text-start">
                        <div class="mb-3">
                            <label class="form-label">Refund Amount</label>
                            <input type="number" id="refundAmount" class="form-control" placeholder="Enter refund amount">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Reason for Refund</label>
                            <select id="refundReason" class="form-select">
                                <option value="Duplicate payment">Duplicate payment</option>
                                <option value="Product not available">Product not available</option>
                                <option value="Customer request">Customer request</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="mb-3" id="otherReasonDiv" style="display: none;">
                            <label class="form-label">Specify Reason</label>
                            <input type="text" id="otherReason" class="form-control" placeholder="Enter reason">
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="notifyUserRefund" checked>
                            <label class="form-check-label" for="notifyUserRefund">
                                Notify user via email
                            </label>
                        </div>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: 'Process Refund',
                preConfirm: () => {
                    const reason = $('#refundReason').val() === 'Other' ? $('#otherReason').val() : $('#refundReason').val();
                    return {
                        payment_id: paymentId,
                        amount: $('#refundAmount').val(),
                        reason: reason,
                        notify_user: $('#notifyUserRefund').is(':checked')
                    };
                },
                didOpen: () => {
                    $('#refundReason').change(function() {
                        if ($(this).val() === 'Other') {
                            $('#otherReasonDiv').show();
                        } else {
                            $('#otherReasonDiv').hide();
                        }
                    });
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    const data = result.value;
                    
                    if (!data.amount || isNaN(data.amount) || parseFloat(data.amount) <= 0) {
                        Swal.fire('Error', 'Please enter a valid refund amount', 'error');
                        return;
                    }
                    
                    if (!data.reason) {
                        Swal.fire('Error', 'Please provide a refund reason', 'error');
                        return;
                    }

                    Swal.fire({
                        title: 'Processing Refund...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    $.ajax({
                        url: 'process_refund.php',
                        method: 'POST',
                        data: data,
                        dataType: 'json',
                        success: function (response) {
                            Swal.close();
                            if (response.status === 'success') {
                                Swal.fire('Success', 'Refund processed successfully!', 'success');
                                fetchPayments();
                            } else {
                                Swal.fire('Error', response.message || 'Failed to process refund.', 'error');
                            }
                        },
                        error: handleAjaxError
                    });
                }
            });
        });
    });
    </script>
</body>
</html>