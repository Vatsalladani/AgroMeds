<?php
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.html");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Admins</title>
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
        .table img {
            max-width: 50px;
            height: auto;
        }
        .table td{
            padding: 15px;
        }
        .sidebar .nav-link.active {
            background-color: rgba(255, 255, 255, 0.1);
        }
        
        /* Enhanced Modal Styles */
        .swal2-popup {
            border-radius: 12px !important;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2) !important;
        }
        
        .swal2-title {
            font-size: 1.5rem !important;
            color: var(--primary-color) !important;
        }
        
        .swal2-input, .swal2-select, .swal2-textarea {
            border: 1px solid #e0e0e0 !important;
            border-radius: 8px !important;
            padding: 12px 15px !important;
            margin: 8px 0 !important;
            box-shadow: none !important;
            transition: all 0.3s ease !important;
        }
        
        .swal2-input:focus, .swal2-select:focus, .swal2-textarea:focus {
            border-color: var(--primary-color) !important;
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.2) !important;
        }
        
        .swal2-file {
            padding: 8px !important;
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
        
        /* Badge for Roles */
        .badge-role {
            padding: 6px 10px;
            border-radius: 20px;
            font-weight: 500;
            font-size: 0.8rem;
        }
        .badge-admin {
            background-color: #6f42c1;
            color: white;
        }
        
        /* Action Buttons */
        .btn-action {
            padding: 6px 10px;
            border-radius: 8px;
            font-size: 0.85rem;
            margin: 2px;
        }
    </style>
</head>
<body>
    <!-- Top Bar -->
    <div class="top-bar">
        <h4>Manage Admins</h4>
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
                <a href="admin_dashboard.php" class="nav-link">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
                <a href="manage_admins.php" class="nav-link active">
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
                <a href="manage_cancel_orders.php" class="nav-link">
                    <i class="fas fa-ban"></i> Cancel Orders
                </a>
                <a href="manage_experts.php" class="nav-link">
                    <i class="fas fa-user-tie"></i> Experts
                </a>
                <a href="manage_users.php" class="nav-link">
                    <i class="fas fa-users"></i> Users
                </a>
                <a href="manage_consultations.php" class="nav-link">
                    <i class="fas fa-calendar-check"></i> Consultations
                </a>
                <a href="manage_contactUs.php" class="nav-link">
                    <i class="fas fa-envelope"></i> Contact Queries
                </a>
                <a href="manage_feedbacks.php" class="nav-link">
                    <i class="fas fa-comments"></i> Feedbacks
                </a>
                <a href="manage_testimonials.php" class="nav-link">
                    <i class="fas fa-quote-left"></i> Testimonials
                </a>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="content">
            <button class="btn btn-success mb-4" id="addAdminBtn"><i class="fas fa-plus-circle me-2"></i> Add Admin</button>
            <input type="text" id="searchAdmin" class="form-control mb-4" placeholder="Search admins by name or email...">

            <table class="table table-hover table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="adminList">
                    <!-- Admins will be loaded here via AJAX -->
                </tbody>
            </table>

            <!-- Pagination Controls -->
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
            const adminsPerPage = 5; // Display 5 admins per page

            // Fetch admins with pagination
            function fetchAdmins(searchQuery = '', page = 1) {
                $.ajax({
                    url: "fetch_admins.php",
                    type: "GET",
                    data: { search: searchQuery, page: page, limit: adminsPerPage },
                    dataType: "json",
                    success: function (response) {
                        if (response.status === "success") {
                            const admins = response.data;
                            const totalAdmins = response.total;
                            const totalPages = Math.ceil(totalAdmins / adminsPerPage);

                            // Update table content
                            let html = "";
                            if (admins.length > 0) {
                                admins.forEach((admin, index) => {
                                    // Format created_at date
                                    const createdAt = new Date(admin.created_at);
                                    const formattedDate = createdAt.toLocaleDateString('en-US', {
                                        year: 'numeric',
                                        month: 'short',
                                        day: 'numeric',
                                        hour: '2-digit',
                                        minute: '2-digit'
                                    });
                                    
                                    html += `
                                        <tr>
                                            <td>${(page - 1) * adminsPerPage + index + 1}</td>
                                            <td>${admin.name}</td>
                                            <td>${admin.email}</td>
                                            <td><span class="badge badge-role badge-admin">${admin.role}</span></td>
                                            <td>${formattedDate}</td>
                                            <td>
                                                <button class="btn btn-info btn-sm btn-action viewAdminBtn" data-id="${admin.admin_id}" title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button class="btn btn-warning btn-sm btn-action editAdminBtn" data-id="${admin.admin_id}" title="Edit Admin">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-danger btn-sm btn-action deleteAdminBtn" data-id="${admin.admin_id}" title="Delete Admin">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    `;
                                });
                            } else {
                                html = '<tr><td colspan="6" class="text-center">No admins found.</td></tr>';
                            }
                            $("#adminList").html(html);

                            // Update pagination controls
                            $("#pageInfo").text(`Page ${page} of ${totalPages}`);
                            $("#prevPageBtn").prop("disabled", page <= 1);
                            $("#nextPageBtn").prop("disabled", page >= totalPages);
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message || 'Failed to fetch admins.',
                                timer: 3000,
                                showConfirmButton: false
                            });
                        }
                    },
                    // In your fetchAdmins function, replace the error handler with:
error: function(xhr, status, error) {
    let errorMsg = 'An error occurred while fetching admins.';
    
    // Try to get more detailed error message
    try {
        const response = JSON.parse(xhr.responseText);
        if (response.message) {
            errorMsg = response.message;
            if (response.debug) {
                console.error('Debug:', response.debug);
            }
        }
    } catch (e) {
        console.error('Failed to parse error response:', e);
    }
    
    Swal.fire({
        icon: 'error',
        title: 'Error',
        text: errorMsg,
        timer: 3000,
        showConfirmButton: false
    });
    
    console.error('AJAX Error:', status, error);
},
                });
            }

            // Call fetchAdmins on page load
            fetchAdmins();

            // Search admins
            $("#searchAdmin").on("input", function () {
                const searchQuery = $(this).val();
                currentPage = 1; // Reset to first page on new search
                fetchAdmins(searchQuery, currentPage);
            });

            // Previous page button
            $("#prevPageBtn").click(function () {
                if (currentPage > 1) {
                    currentPage--;
                    fetchAdmins($("#searchAdmin").val(), currentPage);
                }
            });

            // Next page button
            $("#nextPageBtn").click(function () {
                currentPage++;
                fetchAdmins($("#searchAdmin").val(), currentPage);
            });

            // Add Admin button click - Enhanced UI/UX
            $('#addAdminBtn').click(function () {
                Swal.fire({
                    title: '<h3 style="color: #4361ee;">Add New Admin</h3>',
                    html: `
                        <div class="form-group">
                            <label for="addName" class="form-label">Full Name</label>
                            <input type="text" id="addName" class="form-control swal2-input" placeholder="Enter full name">
                        </div>
                        <div class="form-group mt-3">
                            <label for="addEmail" class="form-label">Email</label>
                            <input type="email" id="addEmail" class="form-control swal2-input" placeholder="Enter email">
                        </div>
                        <div class="form-group mt-3">
                            <label for="addPassword" class="form-label">Password</label>
                            <div class="input-group">
                                <input type="password" id="addPassword" class="form-control swal2-input" placeholder="Enter password">
                                <button class="btn btn-outline-secondary toggle-password" type="button">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        <div class="form-group mt-3">
                            <label for="addConfirmPassword" class="form-label">Confirm Password</label>
                            <div class="input-group">
                                <input type="password" id="addConfirmPassword" class="form-control swal2-input" placeholder="Confirm password">
                                <button class="btn btn-outline-secondary toggle-password" type="button">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                    `,
                    showCancelButton: true,
                    confirmButtonText: '<i class="fas fa-user-plus me-2"></i> Add Admin',
                    confirmButtonColor: '#4361ee',
                    cancelButtonText: 'Cancel',
                    showLoaderOnConfirm: true,
                    preConfirm: () => {
                        const name = $('#addName').val();
                        const email = $('#addEmail').val();
                        const password = $('#addPassword').val();
                        const confirmPassword = $('#addConfirmPassword').val();
                        
                        // Validation
                        if (!name || !email || !password || !confirmPassword) {
                            Swal.showValidationMessage('All fields are required');
                            return false;
                        }
                        
                        if (password !== confirmPassword) {
                            Swal.showValidationMessage('Passwords do not match');
                            return false;
                        }
                        
                        if (password.length < 8) {
                            Swal.showValidationMessage('Password must be at least 8 characters');
                            return false;
                        }
                        
                        return { name, email, password };
                    },
                    didOpen: () => {
                        // Toggle password visibility
                        $('.toggle-password').click(function() {
                            const input = $(this).siblings('input');
                            const icon = $(this).find('i');
                            
                            if (input.attr('type') === 'password') {
                                input.attr('type', 'text');
                                icon.removeClass('fa-eye').addClass('fa-eye-slash');
                            } else {
                                input.attr('type', 'password');
                                icon.removeClass('fa-eye-slash').addClass('fa-eye');
                            }
                        });
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: 'add_admin.php',
                            method: 'POST',
                            data: {
                                name: result.value.name,
                                email: result.value.email,
                                password: result.value.password
                            },
                            success: function (response) {
                                const res = JSON.parse(response);
                                if (res.status === 'success') {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Success!',
                                        text: 'Admin added successfully',
                                        showConfirmButton: false,
                                        timer: 1500
                                    });
                                    fetchAdmins();
                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error',
                                        text: res.message || 'Failed to add admin',
                                        timer: 3000,
                                        showConfirmButton: false
                                    });
                                }
                            },
                            error: function () {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: 'An error occurred while adding admin',
                                    timer: 3000,
                                    showConfirmButton: false
                                });
                            }
                        });
                    }
                });
            });

            // View Admin Details - Enhanced UI/UX
            $(document).on("click", ".viewAdminBtn", function () {
                const adminId = $(this).data("id");

                // Fetch admin details
                $.ajax({
                    url: "get_admin.php",
                    type: "POST",
                    data: { admin_id: adminId },
                    success: function (response) {
                        const result = JSON.parse(response);

                        if (result.status === "success") {
                            const admin = result.data;
                            const createdAt = new Date(admin.created_at);
                            const formattedDate = createdAt.toLocaleDateString('en-US', {
                                year: 'numeric',
                                month: 'long',
                                day: 'numeric',
                                hour: '2-digit',
                                minute: '2-digit'
                            });

                            // Display admin details in a SweetAlert2 modal
                            Swal.fire({
                                title: `<h3 style="color: #4361ee;">${admin.name}</h3>`,
                                html: `
                                    <div class="container text-start">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="admin-details-card p-4 rounded" style="background-color: #f8f9fa; border-left: 4px solid #4361ee;">
                                                    <div class="detail-item mb-3">
                                                        <h5 class="detail-label" style="color: #6c757d; font-size: 0.9rem;">Email</h5>
                                                        <p class="detail-value">${admin.email}</p>
                                                    </div>
                                                    <div class="detail-item mb-3">
                                                        <h5 class="detail-label" style="color: #6c757d; font-size: 0.9rem;">Role</h5>
                                                        <p class="detail-value"><span class="badge badge-admin">${admin.role}</span></p>
                                                    </div>
                                                    <div class="detail-item">
                                                        <h5 class="detail-label" style="color: #6c757d; font-size: 0.9rem;">Created At</h5>
                                                        <p class="detail-value">${formattedDate}</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                `,
                                showConfirmButton: false,
                                showCloseButton: true,
                                customClass: {
                                    popup: 'border-radius-xl',
                                    closeButton: 'btn-close-custom'
                                },
                                width: '600px'
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: result.message || 'Failed to fetch admin details.',
                                timer: 3000,
                                showConfirmButton: false
                            });
                        }
                    },
                    error: function () {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'An error occurred while fetching admin details.',
                            timer: 3000,
                            showConfirmButton: false
                        });
                    },
                });
            });

            // Edit Admin - Enhanced UI/UX
            $(document).on("click", ".editAdminBtn", function () {
                const adminId = $(this).data("id");

                // Fetch admin details for editing
                $.ajax({
                    url: "get_admin.php",
                    type: "POST",
                    data: { admin_id: adminId },
                    success: function (response) {
                        const result = JSON.parse(response);

                        if (result.status === "success") {
                            const admin = result.data;

                            // Display edit form in a SweetAlert2 modal
                            Swal.fire({
                                title: `<h3 style="color: #4361ee;">Edit Admin</h3>`,
                                html: `
                                    <div class="form-group">
                                        <label for="editName" class="form-label">Full Name</label>
                                        <input type="text" id="editName" class="form-control swal2-input" value="${admin.name}" placeholder="Enter full name">
                                    </div>
                                    <div class="form-group mt-3">
                                        <label for="editEmail" class="form-label">Email</label>
                                        <input type="email" id="editEmail" class="form-control swal2-input" value="${admin.email}" placeholder="Enter email">
                                    </div>
                                    <div class="form-group mt-3">
                                        <label for="editPassword" class="form-label">New Password (Leave blank to keep current)</label>
                                        <div class="input-group">
                                            <input type="password" id="editPassword" class="form-control swal2-input" placeholder="Enter new password">
                                            <button class="btn btn-outline-secondary toggle-password" type="button">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </div>
                                    </div>
                                `,
                                showCancelButton: true,
                                confirmButtonText: '<i class="fas fa-save me-2"></i> Save Changes',
                                confirmButtonColor: '#4361ee',
                                cancelButtonText: 'Cancel',
                                showLoaderOnConfirm: true,
                                preConfirm: () => {
                                    const name = $('#editName').val();
                                    const email = $('#editEmail').val();
                                    const password = $('#editPassword').val();
                                    
                                    // Validation
                                    if (!name || !email) {
                                        Swal.showValidationMessage('Name and email are required');
                                        return false;
                                    }
                                    
                                    if (password && password.length < 8) {
                                        Swal.showValidationMessage('Password must be at least 8 characters');
                                        return false;
                                    }
                                    
                                    return { 
                                        admin_id: adminId,
                                        name, 
                                        email, 
                                        password: password || null 
                                    };
                                },
                                didOpen: () => {
                                    // Toggle password visibility
                                    $('.toggle-password').click(function() {
                                        const input = $(this).siblings('input');
                                        const icon = $(this).find('i');
                                        
                                        if (input.attr('type') === 'password') {
                                            input.attr('type', 'text');
                                            icon.removeClass('fa-eye').addClass('fa-eye-slash');
                                        } else {
                                            input.attr('type', 'password');
                                            icon.removeClass('fa-eye-slash').addClass('fa-eye');
                                        }
                                    });
                                }
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    $.ajax({
                                        url: 'edit_admin.php',
                                        method: 'POST',
                                        data: result.value,
                                        success: function (response) {
                                            const res = JSON.parse(response);
                                            if (res.status === 'success') {
                                                Swal.fire({
                                                    icon: 'success',
                                                    title: 'Success!',
                                                    text: 'Admin updated successfully',
                                                    showConfirmButton: false,
                                                    timer: 1500
                                                });
                                                fetchAdmins();
                                            } else {
                                                Swal.fire({
                                                    icon: 'error',
                                                    title: 'Error',
                                                    text: res.message || 'Failed to update admin',
                                                    timer: 3000,
                                                    showConfirmButton: false
                                                });
                                            }
                                        },
                                        error: function () {
                                            Swal.fire({
                                                icon: 'error',
                                                title: 'Error',
                                                text: 'An error occurred while updating admin',
                                                timer: 3000,
                                                showConfirmButton: false
                                            });
                                        }
                                    });
                                }
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: result.message || 'Failed to fetch admin details.',
                                timer: 3000,
                                showConfirmButton: false
                            });
                        }
                    },
                    error: function () {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'An error occurred while fetching admin details.',
                            timer: 3000,
                            showConfirmButton: false
                        });
                    },
                });
            });

            // Delete Admin - Enhanced UI/UX
            $(document).on('click', '.deleteAdminBtn', function () {
                const adminId = $(this).data('id');
                
                Swal.fire({
                    title: '<h4 style="color: #dc3545;">Confirm Deletion</h4>',
                    html: `
                        <div class="alert alert-danger" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Are you sure you want to delete this admin? This action cannot be undone.
                        </div>
                        <div class="text-muted mt-2" style="font-size: 0.9rem;">
                            Note: You cannot delete your own admin account.
                        </div>
                    `,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: '<i class="fas fa-trash-alt me-2"></i> Delete',
                    confirmButtonColor: '#dc3545',
                    cancelButtonText: 'Cancel',
                    reverseButtons: true,
                    focusCancel: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: 'delete_admin.php',
                            method: 'POST',
                            data: { admin_id: adminId },
                            success: function (response) {
                                const res = JSON.parse(response);
                                if (res.status === 'success') {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Deleted!',
                                        text: 'Admin has been deleted.',
                                        showConfirmButton: false,
                                        timer: 1500
                                    });
                                    fetchAdmins();
                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error',
                                        text: res.message || 'Failed to delete admin.',
                                        timer: 3000,
                                        showConfirmButton: false
                                    });
                                }
                            },
                            error: function () {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: 'An error occurred while deleting admin.',
                                    timer: 3000,
                                    showConfirmButton: false
                                });
                            }
                        });
                    }
                });
            });
        });
    </script>
</body>
</html>