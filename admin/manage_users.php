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
    <title>Manage Users</title>
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
    background-color: rgba(255, 255, 255, 0.1); /* Lighter background on active */
}
        /* Custom Pagination Styling */
        .pagination {
            position: fixed; /* Keeps it at the bottom */
            bottom: 20px; /* Distance from the bottom */
            left: 55%; /* Center horizontally */
            transform: translateX(-50%); /* Align center */
            display: flex;
            justify-content: center;
            background: #fff; /* Optional: Background color */
            padding: 10px;
            border-radius: 8px; /* Rounded corners */
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); /* Optional: Shadow */
        }
        .pagination .page-link {
            color: #343a40; /* Dark gray text */
            background-color: #f8f9fa; /* Light gray background */
            border: 1px solid #dee2e6; /* Light border */
            margin: 0 5px; /* Space between buttons */
            transition: all 0.3s ease; /* Smooth hover effect */
            padding: 8px 12px; /* Adjust padding */
            border-radius: 5px; /* Rounded edges */
        }
        .pagination .page-link:hover {
            color: #fff; /* White text on hover */
            background-color: #343a40; /* Dark background on hover */
            border-color: #343a40; /* Dark border on hover */
        }
        .pagination .page-item.disabled .page-link {
            color: #6c757d; /* Gray text for disabled buttons */
            background-color: #f8f9fa; /* Light gray background */
            border-color: #dee2e6; /* Light border */
        }
        .pagination .page-item.active .page-link {
            color: #fff; /* White text for active page */
            background-color: #007bff; /* Blue background for active page */
            border-color: #007bff; /* Blue border for active page */
        }
    </style>
</head>
<body>
    <!-- Top Bar -->
    <div class="top-bar">
        <h4>Manage Users</h4>
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
                <a href="manage_cancel_orders.php" class="nav-link">
                    <i class="fas fa-ban"></i> Cancel Orders
                </a>
                <a href="manage_experts.php" class="nav-link">
                    <i class="fas fa-user-tie"></i> Experts
                </a>
                <a href="manage_users.php" class="nav-link active">
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
            <button class="btn btn-success mb-4" id="addUserBtn"><i class="fas fa-plus"></i> Add User</button>
            <input type="text" id="searchUser" class="form-control mb-4" placeholder="Search users...">

            <table class="table table-hover table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Profile Photo</th>
                        <th>Full Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Address</th>
                        <th>City</th>
                        <th>Pincode</th>
                        <th>Role</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="userList">
                    <!-- Users will be loaded here via AJAX -->
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
            const usersPerPage = 3; // Display 3 users per page

            // Fetch users with pagination
            function fetchUsers(searchQuery = '', page = 1) {
                $.ajax({
                    url: "fetch_users.php",
                    type: "GET",
                    data: { search: searchQuery, page: page, limit: usersPerPage },
                    dataType: "json",
                    success: function (response) {
                        if (response.status === "success") {
                            const users = response.data;
                            const totalUsers = response.total;
                            const totalPages = Math.ceil(totalUsers / usersPerPage);

                            // Update table content
                            let html = "";
                            if (users.length > 0) {
                                users.forEach((user, index) => {
                                    html += `
                                        <tr>
                                            <td>${(page - 1) * usersPerPage + index + 1}</td>
                                            <td>
                                                <img src="${user.profile_photo}" 
                                                     alt="Profile Photo" 
                                                     style="width: 50px; height: auto;">
                                            </td>
                                            <td>${user.full_name}</td>
                                            <td>${user.email}</td>
                                            <td>${user.phone}</td>
                                            <td>${user.address}</td>
                                            <td>${user.city}</td>
                                            <td>${user.pincode}</td>
                                            <td>${user.role}</td>
                                            <td>
                                                <button class="btn btn-info btn-sm viewUserBtn" data-id="${user.user_id}">
                                                    <i class="fas fa-eye"></i> View
                                                </button>
                                                <button class="btn btn-warning btn-sm editUserBtn" data-id="${user.user_id}">
                                                    <i class="fas fa-edit"></i> Edit
                                                </button>
                                                <button class="btn btn-danger btn-sm deleteUserBtn" data-id="${user.user_id}">
                                                    <i class="fas fa-trash"></i> Delete
                                                </button>
                                            </td>
                                        </tr>
                                    `;
                                });
                            } else {
                                html = '<tr><td colspan="10" class="text-center">No users found.</td></tr>';
                            }
                            $("#userList").html(html);

                            // Update pagination controls
                            $("#pageInfo").text(`Page ${page} of ${totalPages}`);
                            $("#prevPageBtn").prop("disabled", page <= 1);
                            $("#nextPageBtn").prop("disabled", page >= totalPages);
                        } else {
                            alert("Failed to fetch users.");
                        }
                    },
                    error: function () {
                        alert("An error occurred while fetching users.");
                    },
                });
            }

            // Call fetchUsers on page load
            fetchUsers();

            // Search users
            $("#searchUser").on("input", function () {
                const searchQuery = $(this).val();
                currentPage = 1; // Reset to first page on new search
                fetchUsers(searchQuery, currentPage);
            });

            // Previous page button
            $("#prevPageBtn").click(function () {
                if (currentPage > 1) {
                    currentPage--;
                    fetchUsers($("#searchUser").val(), currentPage);
                }
            });

            // Next page button
            $("#nextPageBtn").click(function () {
                currentPage++;
                fetchUsers($("#searchUser").val(), currentPage);
            });

            // Add User button click
            $('#addUserBtn').click(function () {
    Swal.fire({
        title: 'Add New User',
        html: `
            <input type="text" id="fullName" class="swal2-input" placeholder="Full Name">
            <input type="email" id="email" class="swal2-input" placeholder="Email">
            <input type="text" id="phone" class="swal2-input" placeholder="Phone">
            <textarea id="address" class="swal2-input" placeholder="Address"></textarea>
            <input type="text" id="city" class="swal2-input" placeholder="City">
            <input type="text" id="pincode" class="swal2-input" placeholder="Pincode">
            <input type="file" id="profilePhoto" class="swal2-input" accept="image/*">
            <select id="role" class="swal2-input">
                <option value="user">User</option>
                <option value="admin">Admin</option>
            </select>
        `,
        showCancelButton: true,
        confirmButtonText: 'Add',
        preConfirm: () => {
            const fileInput = document.getElementById('profilePhoto');
            const file = fileInput.files[0];
            if (!file) {
                Swal.showValidationMessage('Profile photo is required');
                return false;
            }
            return {
                full_name: $('#fullName').val(),
                email: $('#email').val(),
                phone: $('#phone').val(),
                address: $('#address').val(),
                city: $('#city').val(),
                pincode: $('#pincode').val(),
                role: $('#role').val(),
                profile_photo: file
            };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const formData = new FormData();
            formData.append('full_name', result.value.full_name);
            formData.append('email', result.value.email);
            formData.append('phone', result.value.phone);
            formData.append('address', result.value.address);
            formData.append('city', result.value.city);
            formData.append('pincode', result.value.pincode);
            formData.append('role', result.value.role);
            formData.append('profile_photo', result.value.profile_photo);

            $.ajax({
                url: 'add_users.php',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    Swal.fire('Success', 'User added successfully!', 'success');
                    fetchUsers();
                }
            });
        }
    });
});

            // View User Details
            $(document).on("click", ".viewUserBtn", function () {
                const userId = $(this).data("id");

                // Fetch user details
                $.ajax({
                    url: "get_user.php",
                    type: "POST",
                    data: { user_id: userId },
                    success: function (response) {
                        const result = JSON.parse(response);

                        if (result.status === "success") {
                            const user = result.data;

                            // Display user details in a SweetAlert2 modal
                            Swal.fire({
                                title: `<h3>${user.full_name}</h3>`,
                                html: `
                                    <div class="container text-start">
                                        <div class="row">
                                            <div class="col-md-4 text-center mb-3">
                                                <img src="${user.profile_photo}" 
                                                     alt="Profile Photo" 
                                                     class="rounded" 
                                                     style="width: 100%; height: auto; object-fit: cover;">
                                            </div>
                                            <div class="col-md-8">
                                                <p><strong>Email:</strong> ${user.email}</p>
                                                <p><strong>Phone:</strong> ${user.phone}</p>
                                                <p><strong>Address:</strong> ${user.address}</p>
                                                <p><strong>City:</strong> ${user.city}</p>
                                                <p><strong>Pincode:</strong> ${user.pincode}</p>
                                                <p><strong>Role:</strong> ${user.role}</p>
                                            </div>
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
                            Swal.fire("Error", "Failed to fetch user details.", "error");
                        }
                    },
                    error: function () {
                        Swal.fire("Error", "An error occurred while fetching user details.", "error");
                    },
                });
            });

            // Edit User
            $(document).on("click", ".editUserBtn", function () {
    const userId = $(this).data("id");

    // Fetch user details for editing
    $.ajax({
        url: "get_user.php",
        type: "POST",
        data: { user_id: userId },
        success: function (response) {
            const result = JSON.parse(response);

            if (result.status === "success") {
                const user = result.data;

                // Display edit form in a SweetAlert2 modal
                Swal.fire({
                    title: 'Edit User',
                    html: `
                        <input type="text" id="editFullName" class="swal2-input" placeholder="Full Name" value="${user.full_name}">
                        <input type="email" id="editEmail" class="swal2-input" placeholder="Email" value="${user.email}">
                        <input type="text" id="editPhone" class="swal2-input" placeholder="Phone" value="${user.phone}">
                        <textarea id="editAddress" class="swal2-input" placeholder="Address">${user.address}</textarea>
                        <input type="text" id="editCity" class="swal2-input" placeholder="City" value="${user.city}">
                        <input type="text" id="editPincode" class="swal2-input" placeholder="Pincode" value="${user.pincode}">
                        <input type="file" id="editProfilePhoto" class="swal2-input" accept="image/*">
                        <select id="editRole" class="swal2-input">
                            <option value="user" ${user.role === 'user' ? 'selected' : ''}>User</option>
                            <option value="admin" ${user.role === 'admin' ? 'selected' : ''}>Admin</option>
                        </select>
                    `,
                    showCancelButton: true,
                    confirmButtonText: 'Save',
                    preConfirm: () => {
                        const fileInput = document.getElementById('editProfilePhoto');
                        const file = fileInput.files[0];
                        return {
                            user_id: userId,
                            full_name: $('#editFullName').val(),
                            email: $('#editEmail').val(),
                            phone: $('#editPhone').val(),
                            address: $('#editAddress').val(),
                            city: $('#editCity').val(),
                            pincode: $('#editPincode').val(),
                            role: $('#editRole').val(),
                            profile_photo: file
                        };
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        const formData = new FormData();
                        formData.append('user_id', result.value.user_id);
                        formData.append('full_name', result.value.full_name);
                        formData.append('email', result.value.email);
                        formData.append('phone', result.value.phone);
                        formData.append('address', result.value.address);
                        formData.append('city', result.value.city);
                        formData.append('pincode', result.value.pincode);
                        formData.append('role', result.value.role);
                        if (result.value.profile_photo) {
                            formData.append('profile_photo', result.value.profile_photo);
                        }

                        $.ajax({
                            url: 'edit_user.php',
                            method: 'POST',
                            data: formData,
                            processData: false,
                            contentType: false,
                            success: function (response) {
                                Swal.fire('Success', 'User updated successfully!', 'success');
                                fetchUsers();
                            }
                        });
                    }
                });
            } else {
                Swal.fire("Error", "Failed to fetch user details.", "error");
            }
        },
        error: function () {
            Swal.fire("Error", "An error occurred while fetching user details.", "error");
        },
    });
});

            // Delete User
            $(document).on('click', '.deleteUserBtn', function () {
                const userId = $(this).data('id');
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: 'delete_users.php', // Create this PHP file to handle user deletion
                            method: 'POST',
                            data: { user_id: userId },
                            success: function (response) {
                                Swal.fire('Deleted!', 'User has been deleted.', 'success');
                                fetchUsers();
                            }
                        });
                    }
                });
            });
        });
    </script>
</body>
</html>