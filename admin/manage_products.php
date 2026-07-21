<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Products</title>
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
            background-color: #343a40;
            color: white;
            padding: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
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
            background-color: rgba(255, 255, 255, 0.1);
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
        .buttons {
            display: flex;
            gap: 5px;
            justify-content: center;
            padding-top: 22%;
        }
        
        /* Custom SweetAlert2 Styles */
        .swal2-popup {
            border-radius: 12px !important;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15) !important;
        }
        .swal2-title {
            color: #2c3e50 !important;
            font-weight: 600 !important;
            font-size: 1.5rem !important;
        }
        .swal2-html-container {
            color: #34495e !important;
            font-size: 1rem !important;
        }
        .swal2-input, .swal2-file, .swal2-textarea, .swal2-select {
            border: 1px solid #e0e0e0 !important;
            border-radius: 8px !important;
            padding: 10px 15px !important;
            box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.05) !important;
            transition: all 0.3s ease !important;
        }
        .swal2-input:focus, .swal2-file:focus, .swal2-textarea:focus, .swal2-select:focus {
            border-color: #3498db !important;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2) !important;
        }
        .swal2-confirm {
            background-color: #2ecc71 !important;
            border: none !important;
            border-radius: 8px !important;
            padding: 10px 24px !important;
            font-weight: 500 !important;
            transition: all 0.3s ease !important;
        }
        .swal2-confirm:hover {
            background-color: #27ae60 !important;
            transform: translateY(-1px) !important;
            box-shadow: 0 4px 8px rgba(46, 204, 113, 0.3) !important;
        }
        .swal2-cancel {
            border-radius: 8px !important;
            padding: 10px 24px !important;
            font-weight: 500 !important;
            transition: all 0.3s ease !important;
        }
        .swal2-cancel:hover {
            transform: translateY(-1px) !important;
            box-shadow: 0 4px 8px rgba(108, 117, 125, 0.3) !important;
        }
        .product-view-image {
            width: 100%;
            height: 200px;
            object-fit: contain;
            border-radius: 8px;
            margin-bottom: 15px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .product-thumbnail {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 6px;
            margin-right: 10px;
            margin-bottom: 10px;
            border: 1px solid #e0e0e0;
            transition: all 0.3s ease;
        }
        .product-thumbnail:hover {
            transform: scale(1.05);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }
        .detail-label {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 5px;
        }
        .detail-value {
            color: #34495e;
            margin-bottom: 15px;
            padding-left: 10px;
            border-left: 3px solid #3498db;
        }
        .swal2-file-label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: #2c3e50;
        }
        .swal2-file-hint {
            font-size: 0.85rem;
            color: #7f8c8d;
            margin-top: 3px;
            display: block;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .swal2-progress-steps .swal2-progress-step {
            background: #3498db !important;
        }
        .swal2-progress-steps .swal2-progress-step.swal2-active-progress-step {
            background: #2ecc71 !important;
        }
    </style>
</head>
<body>
    <!-- Top Bar -->
    <div class="top-bar">
        <h4>Manage Products</h4>
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
                <a href="manage_products.php" class="nav-link active">
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
            <button class="btn btn-success mb-4" id="addProductBtn"><i class="fas fa-plus"></i> Add Product</button>
            <input type="text" id="searchProduct" class="form-control mb-4" placeholder="Search products...">

            <table class="table table-hover table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Image</th>
                        <th>Product Name</th>
                        <th>Description</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Category ID</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="productList">
                    <!-- Products will be loaded here via AJAX -->
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
            const productsPerPage = 5;

            function fetchProducts(searchQuery = '', page = 1) {
                $.ajax({
                    url: "fetch_products.php",
                    type: "GET",
                    data: { search: searchQuery, page: page, limit: productsPerPage },
                    dataType: "json",
                    success: function (response) {
                        if (response.status === "success") {
                            const products = response.data;
                            const totalProducts = response.total;
                            const totalPages = Math.ceil(totalProducts / productsPerPage);

                            let html = "";
                            if (products.length > 0) {
                                products.forEach((product, index) => {
                                    html += `
                                        <tr>
                                            <td>${(page - 1) * productsPerPage + index + 1}</td>
                                            <td>
                                                <img src="${product.image_url}" 
                                                     alt="Product Image" 
                                                     style="width: 50px; height: auto;">
                                            </td>
                                            <td>${product.product_name.split(' ').slice(0, 3).join(' ') + (product.product_name.split(' ').length > 3 ? '...' : '')}</td>
                                            <td>${product.description.split(' ').slice(0, 11).join(' ') + (product.description.split(' ').length > 11 ? '...' : '')}</td>
                                            <td>${product.price}</td>
                                            <td>${product.quantity}</td>
                                            <td>${product.category_id}</td>
                                            <td class="buttons">
                                                <button class="btn btn-info btn-sm viewProductBtn" data-id="${product.product_id}">
                                                    <i class="fas fa-eye"></i> 
                                                </button>
                                                <button class="btn btn-warning btn-sm editProductBtn" data-id="${product.product_id}">
                                                    <i class="fas fa-edit"></i> 
                                                </button>
                                                <button class="btn btn-danger btn-sm deleteProductBtn" data-id="${product.product_id}">
                                                    <i class="fas fa-trash"></i> 
                                                </button>
                                            </td>
                                        </tr>
                                    `;
                                });
                            } else {
                                html = '<tr><td colspan="8" class="text-center">No products found.</td></tr>';
                            }
                            $("#productList").html(html);

                            $("#pageInfo").text(`Page ${page} of ${totalPages}`);
                            $("#prevPageBtn").prop("disabled", page <= 1);
                            $("#nextPageBtn").prop("disabled", page >= totalPages);
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Failed to fetch products.',
                                confirmButtonColor: '#2ecc71'
                            });
                        }
                    },
                    error: function () {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'An error occurred while fetching products.',
                            confirmButtonColor: '#2ecc71'
                        });
                    },
                });
            }

            fetchProducts();

            $("#searchProduct").on("input", function () {
                const searchQuery = $(this).val();
                currentPage = 1;
                fetchProducts(searchQuery, currentPage);
            });

            $("#prevPageBtn").click(function () {
                if (currentPage > 1) {
                    currentPage--;
                    fetchProducts($("#searchProduct").val(), currentPage);
                }
            });

            $("#nextPageBtn").click(function () {
                currentPage++;
                fetchProducts($("#searchProduct").val(), currentPage);
            });

            // Add Product button click
            $('#addProductBtn').click(function () {
                Swal.fire({
                    title: '<span style="color: #2c3e50; font-weight: 600;">Add New Product</span>',
                    html: `
                        <div class="form-group">
                            <label class="swal2-file-label">Product Name</label>
                            <input type="text" id="productName" class="swal2-input" placeholder="Enter product name">
                        </div>
                        <div class="form-group">
                            <label class="swal2-file-label">Description</label>
                            <textarea id="description" class="swal2-input" placeholder="Enter product description" style="height: 100px;"></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="swal2-file-label">Price</label>
                                    <input type="number" id="price" class="swal2-input" placeholder="Enter price">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="swal2-file-label">Quantity</label>
                                    <input type="number" id="quantity" class="swal2-input" placeholder="Enter quantity">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="swal2-file-label">Category ID</label>
                            <input type="number" id="categoryId" class="swal2-input" placeholder="Enter category ID">
                        </div>
                        <div class="form-group">
                            <label class="swal2-file-label">Main Image</label>
                            <input type="file" id="imageUrl" class="swal2-input" accept="image/*">
                            <small class="swal2-file-hint">Recommended size: 800x800px</small>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="swal2-file-label">Image 1</label>
                                    <input type="file" id="image1" class="swal2-input" accept="image/*">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="swal2-file-label">Image 2</label>
                                    <input type="file" id="image2" class="swal2-input" accept="image/*">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="swal2-file-label">Image 3</label>
                            <input type="file" id="image3" class="swal2-input" accept="image/*">
                        </div>
                        <div class="form-group">
                            <label class="swal2-file-label">Weighting Type</label>
                            <select id="weightingType" class="swal2-input">
                                <option value="">Select Weighting Type</option>
                                <option value="ml">Milliliters (ml)</option>
                                <option value="kg">Kilograms (kg)</option>
                                <option value="packs">Packs</option>
                            </select>
                        </div>
                        <div id="weightingInput" style="display: none;">
                            <label class="swal2-file-label">Weighting Value</label>
                            <input type="number" id="weightingValue" class="swal2-input" placeholder="Enter value">
                        </div>
                    `,
                    showCancelButton: true,
                    confirmButtonText: 'Add Product',
                    cancelButtonText: 'Cancel',
                    confirmButtonColor: '#2ecc71',
                    cancelButtonColor: '#6c757d',
                    width: '700px',
                    padding: '2em',
                    backdrop: 'rgba(0,0,0,0.4)',
                    preConfirm: () => {
                        const weightingType = $('#weightingType').val();
                        const weightingValue = $('#weightingValue').val();

                        const getFileName = (fileInputId) => {
                            const fileInput = document.getElementById(fileInputId);
                            if (fileInput.files.length > 0) {
                                const file = fileInput.files[0];
                                return file.name;
                            }
                            return null;
                        };

                        const basePath = '/Farming_meds/Uploads/Products/';
                        const image_url = getFileName('imageUrl') ? basePath + getFileName('imageUrl') : null;
                        const image1 = getFileName('image1') ? basePath + getFileName('image1') : null;
                        const image2 = getFileName('image2') ? basePath + getFileName('image2') : null;
                        const image3 = getFileName('image3') ? basePath + getFileName('image3') : null;

                        const data = {
                            product_name: $('#productName').val(),
                            description: $('#description').val(),
                            price: $('#price').val(),
                            quantity: $('#quantity').val(),
                            category_id: $('#categoryId').val(),
                            image_url: image_url,
                            image1: image1,
                            image2: image2,
                            image3: image3,
                            weighting_ml: weightingType === 'ml' ? weightingValue : null,
                            weighting_kg: weightingType === 'kg' ? weightingValue : null,
                            weighting_packs: weightingType === 'packs' ? weightingValue : null,
                        };

                        return data;
                    },
                    didOpen: () => {
                        $('#weightingType').on('change', function () {
                            const weightingType = $(this).val();
                            if (weightingType) {
                                $('#weightingInput').show();
                                $('#weightingValue').attr('placeholder', `Weighting (${weightingType})`);
                            } else {
                                $('#weightingInput').hide();
                            }
                        });
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        const data = result.value;

                        // Validate required fields
                        if (!data.product_name || !data.description || !data.price || !data.category_id) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Validation Error',
                                text: 'Please fill all required fields.',
                                confirmButtonColor: '#2ecc71'
                            });
                            return;
                        }

                        $.ajax({
                            url: 'add_products.php',
                            method: 'POST',
                            data: data,
                            success: function (response) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Success',
                                    text: 'Product added successfully!',
                                    confirmButtonColor: '#2ecc71',
                                    timer: 2000,
                                    timerProgressBar: true,
                                    showConfirmButton: false
                                });
                                fetchProducts();
                            },
                            error: function () {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: 'Failed to add product.',
                                    confirmButtonColor: '#2ecc71'
                                });
                            }
                        });
                    }
                });
            });

            // View Product
            $(document).on("click", ".viewProductBtn", function () {
                const productId = $(this).data("id");

                $.ajax({
                    url: "get_products.php",
                    type: "POST",
                    data: { product_id: productId },
                    success: function (response) {
                        const result = JSON.parse(response);

                        if (result.status === "success") {
                            const product = result.data;

                            let weightingType = '';
                            let weightingValue = '';
                            if (product.weighting_ml > 0) {
                                weightingType = 'ml';
                                weightingValue = product.weighting_ml;
                            } else if (product.weighting_kg > 0) {
                                weightingType = 'kg';
                                weightingValue = product.weighting_kg;
                            } else if (product.weighting_packs > 0) {
                                weightingType = 'packs';
                                weightingValue = product.weighting_packs;
                            }

                            // Create HTML for additional images
                            let additionalImagesHtml = '';
                            if (product.image1 || product.image2 || product.image3) {
                                additionalImagesHtml = '<div class="row mt-3">';
                                if (product.image1) {
                                    additionalImagesHtml += `
                                        <div class="col-md-4">
                                            <img src="${product.image1}" class="product-thumbnail" 
                                                 onclick="Swal.fire({
                                                    imageUrl: '${product.image1}',
                                                    imageAlt: 'Product Image 1',
                                                    showConfirmButton: false,
                                                    backdrop: 'rgba(0,0,0,0.8)'
                                                 })">
                                        </div>
                                    `;
                                }
                                if (product.image2) {
                                    additionalImagesHtml += `
                                        <div class="col-md-4">
                                            <img src="${product.image2}" class="product-thumbnail" 
                                                 onclick="Swal.fire({
                                                    imageUrl: '${product.image2}',
                                                    imageAlt: 'Product Image 2',
                                                    showConfirmButton: false,
                                                    backdrop: 'rgba(0,0,0,0.8)'
                                                 })">
                                        </div>
                                    `;
                                }
                                if (product.image3) {
                                    additionalImagesHtml += `
                                        <div class="col-md-4">
                                            <img src="${product.image3}" class="product-thumbnail" 
                                                 onclick="Swal.fire({
                                                    imageUrl: '${product.image3}',
                                                    imageAlt: 'Product Image 3',
                                                    showConfirmButton: false,
                                                    backdrop: 'rgba(0,0,0,0.8)'
                                                 })">
                                        </div>
                                    `;
                                }
                                additionalImagesHtml += '</div>';
                            }

                            Swal.fire({
                                title: `<span style="color: #2c3e50; font-weight: 600;">${product.product_name}</span>`,
                                html: `
                                    <div class="container-fluid">
                                        <div class="row">
                                            <div class="col-md-5">
                                                <img src="${product.image_url}" class="product-view-image">
                                            </div>
                                            <div class="col-md-7">
                                                <div class="detail-label">Description</div>
                                                <div class="detail-value">${product.description}</div>
                                                
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="detail-label">Price</div>
                                                        <div class="detail-value">$${product.price}</div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="detail-label">Quantity</div>
                                                        <div class="detail-value">${product.quantity}</div>
                                                    </div>
                                                </div>
                                                
                                                <div class="detail-label">Category ID</div>
                                                <div class="detail-value">${product.category_id}</div>
                                                
                                                ${weightingType ? `
                                                    <div class="detail-label">Weighting (${weightingType})</div>
                                                    <div class="detail-value">${weightingValue}</div>
                                                ` : ''}
                                                
                                                <div class="detail-label">Additional Images</div>
                                                ${additionalImagesHtml}
                                            </div>
                                        </div>
                                    </div>
                                `,
                                width: '900px',
                                showConfirmButton: true,
                                confirmButtonText: 'Close',
                                confirmButtonColor: '#3498db',
                                backdrop: 'rgba(0,0,0,0.4)',
                                padding: '2em'
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Failed to fetch product details.',
                                confirmButtonColor: '#2ecc71'
                            });
                        }
                    },
                    error: function () {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'An error occurred while fetching product details.',
                            confirmButtonColor: '#2ecc71'
                        });
                    },
                });
            });

            // Edit Product
            $(document).on("click", ".editProductBtn", function () {
                const productId = $(this).data("id");

                $.ajax({
                    url: "get_products.php",
                    type: "POST",
                    data: { product_id: productId },
                    success: function (response) {
                        const result = JSON.parse(response);

                        if (result.status === "success") {
                            const product = result.data;

                            let selectedWeightingType = '';
                            if (product.weighting_ml > 0) {
                                selectedWeightingType = 'ml';
                            } else if (product.weighting_kg > 0) {
                                selectedWeightingType = 'kg';
                            } else if (product.weighting_packs > 0) {
                                selectedWeightingType = 'packs';
                            }

                            Swal.fire({
                                title: '<span style="color: #2c3e50; font-weight: 600;">Edit Product</span>',
                                html: `
                                    <div class="form-group">
                                        <label class="swal2-file-label">Product Name</label>
                                        <input type="text" id="editProductName" class="swal2-input" 
                                               placeholder="Product Name" value="${product.product_name}">
                                    </div>
                                    <div class="form-group">
                                        <label class="swal2-file-label">Description</label>
                                        <textarea id="editDescription" class="swal2-input" 
                                                  placeholder="Description" style="height: 100px;">${product.description}</textarea>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="swal2-file-label">Price</label>
                                                <input type="number" id="editPrice" class="swal2-input" 
                                                       placeholder="Price" value="${product.price}">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="swal2-file-label">Quantity</label>
                                                <input type="number" id="editQuantity" class="swal2-input" 
                                                       placeholder="Quantity" value="${product.quantity}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="swal2-file-label">Category ID</label>
                                        <input type="number" id="editCategoryId" class="swal2-input" 
                                               placeholder="Category ID" value="${product.category_id}">
                                    </div>
                                    <div class="form-group">
                                        <label class="swal2-file-label">Main Image</label>
                                        <input type="file" id="editImageUrl" class="swal2-input" accept="image/*">
                                        <small class="swal2-file-hint">Current: ${product.image_url}</small>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="swal2-file-label">Image 1</label>
                                                <input type="file" id="editImage1" class="swal2-input" accept="image/*">
                                                <small class="swal2-file-hint">Current: ${product.image1 || 'N/A'}</small>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="swal2-file-label">Image 2</label>
                                                <input type="file" id="editImage2" class="swal2-input" accept="image/*">
                                                <small class="swal2-file-hint">Current: ${product.image2 || 'N/A'}</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="swal2-file-label">Image 3</label>
                                        <input type="file" id="editImage3" class="swal2-input" accept="image/*">
                                        <small class="swal2-file-hint">Current: ${product.image3 || 'N/A'}</small>
                                    </div>
                                    <div class="form-group">
                                        <label class="swal2-file-label">Weighting Type</label>
                                        <select id="weightingType" class="swal2-input">
                                            <option value="">Select Weighting Type</option>
                                            ${selectedWeightingType === 'ml' ? '<option value="ml" selected>Milliliters (ml)</option>' : '<option value="ml">Milliliters (ml)</option>'}
                                            ${selectedWeightingType === 'kg' ? '<option value="kg" selected>Kilograms (kg)</option>' : '<option value="kg">Kilograms (kg)</option>'}
                                            ${selectedWeightingType === 'packs' ? '<option value="packs" selected>Packs</option>' : '<option value="packs">Packs</option>'}
                                        </select>
                                    </div>
                                    <div id="weightingInput" style="display: ${selectedWeightingType ? 'block' : 'none'};">
                                        <label class="swal2-file-label">Weighting Value</label>
                                        <input type="number" id="editWeightingValue" class="swal2-input" 
                                               placeholder="Weighting Value" 
                                               value="${selectedWeightingType === 'ml' ? product.weighting_ml : selectedWeightingType === 'kg' ? product.weighting_kg : product.weighting_packs}">
                                    </div>
                                `,
                                showCancelButton: true,
                                confirmButtonText: 'Save Changes',
                                cancelButtonText: 'Cancel',
                                confirmButtonColor: '#2ecc71',
                                cancelButtonColor: '#6c757d',
                                width: '700px',
                                padding: '2em',
                                backdrop: 'rgba(0,0,0,0.4)',
                                preConfirm: () => {
                                    const weightingType = $('#weightingType').val();
                                    const weightingValue = $('#editWeightingValue').val();

                                    const getFileName = (fileInputId) => {
                                        const fileInput = document.getElementById(fileInputId);
                                        if (fileInput.files.length > 0) {
                                            const file = fileInput.files[0];
                                            return file.name;
                                        }
                                        return null;
                                    };

                                    const basePath = '/Farming_meds/Uploads/Products/';
                                    const image_url = getFileName('editImageUrl') ? basePath + getFileName('editImageUrl') : product.image_url;
                                    const image1 = getFileName('editImage1') ? basePath + getFileName('editImage1') : product.image1;
                                    const image2 = getFileName('editImage2') ? basePath + getFileName('editImage2') : product.image2;
                                    const image3 = getFileName('editImage3') ? basePath + getFileName('editImage3') : product.image3;

                                    const data = {
                                        product_id: productId,
                                        product_name: $('#editProductName').val(),
                                        description: $('#editDescription').val(),
                                        price: $('#editPrice').val(),
                                        quantity: $('#editQuantity').val(),
                                        category_id: $('#editCategoryId').val(),
                                        image_url: image_url,
                                        image1: image1,
                                        image2: image2,
                                        image3: image3,
                                        weighting_ml: weightingType === 'ml' ? weightingValue : 0,
                                        weighting_kg: weightingType === 'kg' ? weightingValue : 0,
                                        weighting_packs: weightingType === 'packs' ? weightingValue : 0,
                                    };

                                    return data;
                                },
                                didOpen: () => {
                                    $('#weightingType').on('change', function () {
                                        const weightingType = $(this).val();
                                        if (weightingType) {
                                            $('#weightingInput').show();
                                            $('#editWeightingValue').attr('placeholder', `Weighting (${weightingType})`);
                                        } else {
                                            $('#weightingInput').hide();
                                        }
                                    });
                                }
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    const data = result.value;

                                    // Validate required fields
                                    if (!data.product_name || !data.description || !data.price || !data.category_id) {
                                        Swal.fire({
                                            icon: 'error',
                                            title: 'Validation Error',
                                            text: 'Please fill all required fields.',
                                            confirmButtonColor: '#2ecc71'
                                        });
                                        return;
                                    }

                                    $.ajax({
                                        url: 'edit_products.php',
                                        method: 'POST',
                                        data: data,
                                        success: function (response) {
                                            Swal.fire({
                                                icon: 'success',
                                                title: 'Success',
                                                text: 'Product updated successfully!',
                                                confirmButtonColor: '#2ecc71',
                                                timer: 2000,
                                                timerProgressBar: true,
                                                showConfirmButton: false
                                            });
                                            fetchProducts();
                                        },
                                        error: function () {
                                            Swal.fire({
                                                icon: 'error',
                                                title: 'Error',
                                                text: 'Failed to update product.',
                                                confirmButtonColor: '#2ecc71'
                                            });
                                        }
                                    });
                                }
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Failed to fetch product details.',
                                confirmButtonColor: '#2ecc71'
                            });
                        }
                    },
                    error: function () {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'An error occurred while fetching product details.',
                            confirmButtonColor: '#2ecc71'
                        });
                    },
                });
            });

            // Delete Product
            $(document).on('click', '.deleteProductBtn', function () {
                const productId = $(this).data('id');
                
                Swal.fire({
                    title: '<span style="color: #2c3e50; font-weight: 600;">Confirm Deletion</span>',
                    html: `<p style="color: #7f8c8d;">Are you sure you want to delete this product? This action cannot be undone.</p>`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, delete it!',
                    cancelButtonText: 'Cancel',
                    confirmButtonColor: '#e74c3c',
                    cancelButtonColor: '#6c757d',
                    width: '500px',
                    backdrop: 'rgba(0,0,0,0.4)',
                    padding: '2em'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: 'delete_products.php',
                            method: 'POST',
                            data: { product_id: productId },
                            success: function (response) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Deleted!',
                                    text: 'Product has been deleted.',
                                    confirmButtonColor: '#2ecc71',
                                    timer: 2000,
                                    timerProgressBar: true,
                                    showConfirmButton: false
                                });
                                fetchProducts();
                            },
                            error: function () {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: 'Failed to delete product.',
                                    confirmButtonColor: '#2ecc71'
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