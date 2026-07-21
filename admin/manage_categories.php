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
    <title>Manage Categories</title>
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
        .sidebar .nav-link.active {
    background-color: rgba(255, 255, 255, 0.1); /* Lighter background on active */
}
    </style>
</head>
<body>
    <!-- Top Bar -->
    <div class="top-bar">
        <h4>Manage Categories</h4>
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
                <a href="manage_admins.php" class="nav-link">
                    <i class="fas fa-user-shield"></i> Admins
                </a>
                <a href="manage_categories.php" class="nav-link  active">
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
            <button class="btn btn-success mb-4" id="addCategoryBtn"><i class="fas fa-plus"></i> Add Category</button>
            <input type="text" id="searchCategory" class="form-control mb-4" placeholder="Search categories...">

            <table class="table table-hover table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Category Name</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="categoryList">
                    <!-- Categories will be loaded here via AJAX -->
                </tbody>
            </table>
        </div>
    </div>

    <script>
        $(document).ready(function () {
            // Fetch categories on page load
            function fetchCategories(searchQuery = '') {
                $.ajax({
                    url: "fetch_categories.php",
                    type: "GET",
                    data: { search: searchQuery },
                    dataType: "json",
                    success: function (response) {
                        if (response.status === "success") {
                            const categories = response.data;
                            let html = "";

                            if (categories.length > 0) {
                                categories.forEach((category, index) => {
                                    html += `
                                        <tr>
                                            <td>${index + 1}</td>
                                            <td>${category.category_name}</td>
                                            <td>
                                                <button class="btn btn-warning btn-sm editCategoryBtn" data-id="${category.category_id}">
                                                    <i class="fas fa-edit"></i> Edit
                                                </button>
                                                <button class="btn btn-danger btn-sm deleteCategoryBtn" data-id="${category.category_id}">
                                                    <i class="fas fa-trash"></i> Delete
                                                </button>
                                            </td>
                                        </tr>
                                    `;
                                });
                            } else {
                                html = '<tr><td colspan="3" class="text-center">No categories found.</td></tr>';
                            }

                            $("#categoryList").html(html);
                        } else {
                            alert("Failed to fetch categories.");
                        }
                    },
                    error: function () {
                        alert("An error occurred while fetching categories.");
                    },
                });
            }

            // Call fetchCategories on page load
            fetchCategories();

            // Search categories
            $("#searchCategory").on("input", function () {
                const searchQuery = $(this).val();
                fetchCategories(searchQuery);
            });

            // Add Category button click
            $('#addCategoryBtn').click(function () {
                Swal.fire({
                    title: 'Add New Category',
                    html: `
                        <input type="text" id="categoryName" class="swal2-input" placeholder="Category Name">
                    `,
                    showCancelButton: true,
                    confirmButtonText: 'Add',
                    preConfirm: () => {
                        return {
                            category_name: $('#categoryName').val()
                        };
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: 'add_categories.php', // Create this PHP file to handle category insertion
                            method: 'POST',
                            data: result.value,
                            success: function (response) {
                                Swal.fire('Success', 'Category added successfully!', 'success');
                                fetchCategories();
                            }
                        });
                    }
                });
            });

            // Edit Category
            $(document).on("click", ".editCategoryBtn", function () {
    const categoryId = $(this).data("id");

    // Fetch category details for editing
    $.ajax({
        url: "get_categories.php",
        type: "POST",
        data: { category_id: categoryId },
        success: function (response) {
            console.log("Server Response: ", response); // Log the response
            
            // Assuming the response is in plain text format with category details
            let categoryData = response.split('|'); // Example: "1|Electronics" (ID|Category Name)
            if (categoryData.length === 2) {
                let categoryId = categoryData[0];
                let categoryName = categoryData[1];

                // Display edit form in a SweetAlert2 modal
                Swal.fire({
                    title: 'Edit Category',
                    html: `
                        <input type="text" id="editCategoryName" class="swal2-input" placeholder="Category Name" value="${categoryName}">
                    `,
                    showCancelButton: true,
                    confirmButtonText: 'Save',
                    preConfirm: () => {
                        return {
                            category_id: categoryId,
                            category_name: $('#editCategoryName').val()
                        };
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: 'edit_categories.php', // Create this PHP file to handle category editing
                            method: 'POST',
                            data: {
                                category_id: categoryId,
                                category_name: result.value.category_name
                            },
                            success: function () {
                                Swal.fire('Success', 'Category updated successfully!', 'success');
                                fetchCategories();
                            }
                        });
                    }
                });
            } else {
                Swal.fire("Error", "Failed to fetch category details.", "error");
            }
        },
        error: function () {
            Swal.fire("Error", "An error occurred while fetching category details.", "error");
        }
    });
});


            // Delete Category
            $(document).on('click', '.deleteCategoryBtn', function () {
                const categoryId = $(this).data('id');
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: 'delete_categories.php', // Create this PHP file to handle category deletion
                            method: 'POST',
                            data: { category_id: categoryId },
                            success: function (response) {
                                Swal.fire('Deleted!', 'Category has been deleted.', 'success');
                                fetchCategories();
                            }
                        });
                    }
                });
            });
        });
    </script>
</body>
</html>