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
    <title>Manage Experts</title>
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
   <style>
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
<style>
    /* Enhanced Popup Styles */
    .swal2-popup {
        border-radius: 12px !important;
        overflow: hidden;
    }
    
    .swal2-title {
        position: relative;
        padding-bottom: 15px;
        color: #2c3e50;
    }
    
    .swal2-title:after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 50%;
        transform: translateX(-50%);
        width: 80px;
        height: 3px;
        background: linear-gradient(90deg, #3498db, #9b59b6);
        border-radius: 3px;
    }
    
    .swal2-content {
        padding: 0 20px 20px !important;
    }
    
    .swal2-input, .swal2-textarea, .swal2-file {
        border: 2px solid #e0e0e0 !important;
        border-radius: 8px !important;
        transition: all 0.3s ease !important;
        box-shadow: none !important;
    }
    
    .swal2-input:focus, .swal2-textarea:focus {
        border-color: #3498db !important;
        box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2) !important;
    }
    
    .swal2-file {
        padding: 8px 12px !important;
    }
    
    .swal2-actions {
        margin: 20px 0 0 !important;
    }
    
    .swal2-confirm {
        background: linear-gradient(135deg, #3498db, #9b59b6) !important;
        border: none !important;
        border-radius: 8px !important;
        padding: 10px 24px !important;
        transition: all 0.3s ease !important;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1) !important;
    }
    
    .swal2-confirm:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 8px rgba(0, 0, 0, 0.15) !important;
    }
    
    .swal2-cancel {
        border-radius: 8px !important;
        padding: 10px 24px !important;
        transition: all 0.3s ease !important;
    }
    
    .swal2-cancel:hover {
        background-color: #f1f1f1 !important;
    }
    
    /* Expert View Popup */
    .expert-view-container {
        padding: 15px;
    }
    
    .expert-view-img {
        border-radius: 10px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease;
        margin-bottom: 15px;
    }
    
    .expert-view-img:hover {
        transform: scale(1.03);
    }
    
    .expert-details {
        padding: 15px;
        background: #f9f9f9;
        border-radius: 10px;
        margin-top: 15px;
    }
    
    .expert-details p {
        margin-bottom: 10px;
        position: relative;
        padding-left: 25px;
    }
    
    .expert-details p strong {
        color: #2c3e50;
    }
    
    .expert-details p:before {
        content: '';
        position: absolute;
        left: 0;
        top: 7px;
        width: 15px;
        height: 15px;
        background: #3498db;
        border-radius: 50%;
    }
    
    /* Loading Animation */
    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.05); }
        100% { transform: scale(1); }
    }
    
    .swal2-loading {
        animation: pulse 1.5s infinite ease-in-out;
    }
    
    /* Success Animation */
    .swal2-success {
        position: relative;
    }
    
    .swal2-success:before {
        content: '';
        position: absolute;
        width: 60px;
        height: 60px;
        background: rgba(46, 204, 113, 0.2);
        border-radius: 50%;
        animation: ripple 1.5s infinite;
    }
    
    @keyframes ripple {
        0% { transform: scale(0.8); opacity: 1; }
        100% { transform: scale(1.5); opacity: 0; }
    }
</style>


</head>
<body>
    <!-- Top Bar -->
    <div class="top-bar">
        <h4>Manage Experts</h4>
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
                <a href="manage_experts.php" class="nav-link active">
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
            <button class="btn btn-success mb-4" id="addExpertBtn"><i class="fas fa-plus"></i> Add Expert</button>
            <input type="text" id="searchExpert" class="form-control mb-4" placeholder="Search experts...">

            <table class="table table-hover table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Specialization</th>
                        <th>Description</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="expertList">
                    <!-- Experts will be loaded here via AJAX -->
                </tbody>
            </table>
            <div class="d-flex justify-content-center mt-4">
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
    </div>
    <!-- Pagination Controls -->



    

    <script>

    let currentPage = 1;
    const expertsPerPage = 4;

    // Fetch experts with pagination
    function fetchExperts(searchQuery = '', page = 1) {
        $.ajax({
            url: "fetch_experts.php",
            type: "GET",
            data: { search: searchQuery, page: page, limit: expertsPerPage },
            dataType: "json",
            success: function (response) {
                if (response.status === "success") {
                    const experts = response.data;
                    const totalExperts = response.total;
                    const totalPages = Math.ceil(totalExperts / expertsPerPage);

                    // Update table content
                    let html = "";
                    if (experts.length > 0) {
                        experts.forEach((expert, index) => {
                            html += `
                                <tr>
                                    <td>${(page - 1) * expertsPerPage + index + 1}</td>
                                    <td>
                                        <img src="${expert.image_url}" 
                                             alt="Expert Image" 
                                             style="width: 50px; height: auto;">
                                    </td>
                                    <td>${expert.name}</td>
                                    <td>${expert.specialization}</td>
                                    <td>${expert.description.split(' ').slice(0, 20).join(' ') + (expert.description.split(' ').length > 20 ? '...' : '')}</td>
                                    <td>
                                        <button class="btn btn-info btn-sm viewExpertBtn" data-id="${expert.expert_id}">
                                            <i class="fas fa-eye"></i> View
                                        </button>
                                        <button class="btn btn-warning btn-sm editExpertBtn" data-id="${expert.expert_id}">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        <button class="btn btn-danger btn-sm deleteExpertBtn" data-id="${expert.expert_id}">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </td>
                                </tr>
                            `;
                        });
                    } else {
                        html = '<tr><td colspan="6" class="text-center">No experts found.</td></tr>';
                    }
                    $("#expertList").html(html);

                    // Update pagination controls
                    $("#pageInfo").text(`Page ${page} of ${totalPages}`);
                    $("#prevPageBtn").prop("disabled", page <= 1);
                    $("#nextPageBtn").prop("disabled", page >= totalPages);
                } else {
                    alert("Failed to fetch experts.");
                }
            },
            error: function () {
                alert("An error occurred while fetching experts.");
            },
        });
    }

    // Call fetchExperts on page load
    fetchExperts();

    // Search experts
    $("#searchExpert").on("input", function () {
        const searchQuery = $(this).val();
        currentPage = 1; // Reset to first page on new search
        fetchExperts(searchQuery, currentPage);
    });

    // Previous page button
    $("#prevPageBtn").click(function () {
        if (currentPage > 1) {
            currentPage--;
            fetchExperts($("#searchExpert").val(), currentPage);
        }
    });

    // Next page button
    $("#nextPageBtn").click(function () {
        currentPage++;
        fetchExperts($("#searchExpert").val(), currentPage);
    });

            // Add Expert button click handler
            document.getElementById('addExpertBtn').addEventListener('click', function() {
                const modalId = 'addExpertModal-' + Date.now();
                
                const modalHTML = `
                    <div id="${modalId}" style="text-align:left;">
                        <div style="margin-bottom:15px;">
                            <label style="display:block;margin-bottom:5px;color:#555;font-weight:500;">Name*</label>
                            <input type="text" id="expertName" class="swal2-input" placeholder="Expert name" required>
                        </div>
                        <div style="margin-bottom:15px;">
                            <label style="display:block;margin-bottom:5px;color:#555;font-weight:500;">Email</label>
                            <input type="email" id="expertEmail" class="swal2-input" placeholder="Expert email">
                        </div>
                        <div style="margin-bottom:15px;">
                            <label style="display:block;margin-bottom:5px;color:#555;font-weight:500;">Contact Number</label>
                            <input type="tel" id="expertContact" class="swal2-input" placeholder="Contact number">
                        </div>
                        <div style="margin-bottom:15px;">
                            <label style="display:block;margin-bottom:5px;color:#555;font-weight:500;">Specialization*</label>
                            <input type="text" id="specialization" class="swal2-input" placeholder="Area of expertise" required>
                        </div>
                        <div style="margin-bottom:15px;">
                            <label style="display:block;margin-bottom:5px;color:#555;font-weight:500;">Description*</label>
                            <textarea id="description" class="swal2-input" placeholder="Expert bio" style="height:100px;" required></textarea>
                        </div>
                        <div>
                            <label style="display:block;margin-bottom:5px;color:#555;font-weight:500;">Profile Image*</label>
                            <div id="imageUploadArea" style="border:2px dashed #ddd;border-radius:8px;padding:15px;text-align:center;cursor:pointer;">
                                <i class="fas fa-cloud-upload-alt" style="font-size:24px;color:#3498db;margin-bottom:5px;"></i>
                                <p style="margin:0;color:#777;">Click to upload image</p>
                                <input type="file" id="expertImage" style="display:none;" accept="image/*" required>
                            </div>
                            <div id="fileNameDisplay" style="margin-top:5px;font-size:12px;color:#777;"></div>
                        </div>
                    </div>
                `;

                Swal.fire({
                    title: 'Add New Expert',
                    html: modalHTML,
                    showCancelButton: true,
                    confirmButtonText: 'Add Expert',
                    cancelButtonText: 'Cancel',
                    showLoaderOnConfirm: true,
                    didOpen: () => {
                        const uploadArea = document.querySelector(`#${modalId} #imageUploadArea`);
                        const fileInput = document.querySelector(`#${modalId} #expertImage`);
                        const fileNameDisplay = document.querySelector(`#${modalId} #fileNameDisplay`);

                        uploadArea.addEventListener('click', function(e) {
                            e.stopPropagation();
                            fileInput.click();
                        });

                        fileInput.addEventListener('change', function() {
                            if (this.files[0]) {
                                // Validate file type
                                const validTypes = ['image/jpeg', 'image/png', 'image/webp'];
                                if (!validTypes.includes(this.files[0].type)) {
                                    Swal.showValidationMessage('Only JPG, PNG, or WEBP images are allowed');
                                    return;
                                }
                                fileNameDisplay.textContent = `Selected: ${this.files[0].name}`;
                            }
                        });
                    },
                    preConfirm: () => {
                        const fileInput = document.querySelector(`#${modalId} #expertImage`);
                        const file = fileInput.files[0];
                        
                        // Validate required fields
                        const name = document.querySelector(`#${modalId} #expertName`).value.trim();
                        const specialization = document.querySelector(`#${modalId} #specialization`).value.trim();
                        const description = document.querySelector(`#${modalId} #description`).value.trim();
                        
                        if (!file) {
                            Swal.showValidationMessage('Profile image is required');
                            return false;
                        }
                        if (!name) {
                            Swal.showValidationMessage('Name is required');
                            return false;
                        }
                        if (!specialization) {
                            Swal.showValidationMessage('Specialization is required');
                            return false;
                        }
                        if (!description) {
                            Swal.showValidationMessage('Description is required');
                            return false;
                        }

                        // Construct image path
                        const fileName = file.name;
                        const fileExt = fileName.split('.').pop().toLowerCase();
                        const baseName = fileName.substring(0, fileName.lastIndexOf('.')).replace(/[^a-zA-Z0-9_-]/g, '_');
                        const imagePath = `\\Farming_meds/Uploads/Experts/${baseName}.${fileExt}`;

                        return {
                            name: name,
                            email: document.querySelector(`#${modalId} #expertEmail`).value.trim(),
                            contact_no: document.querySelector(`#${modalId} #expertContact`).value.trim(),
                            specialization: specialization,
                            description: description,
                            image_path: imagePath
                        };
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        const loadingSwal = Swal.fire({
                            title: 'Adding Expert...',
                            allowOutsideClick: false,
                            didOpen: () => Swal.showLoading()
                        });

                        fetch('add_experts.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify(result.value)
                        })
                        .then(async response => {
                            const data = await response.json();
                            if (!response.ok) {
                                throw new Error(data.message || 'Failed to add expert');
                            }
                            return data;
                        })
                        .then(data => {
                            loadingSwal.close();
                            Swal.fire({
                                title: 'Success!',
                                text: data.message,
                                icon: 'success',
                                timer: 2000,
                                showConfirmButton: false
                            });
                            fetchExperts();
                        })
                        .catch(error => {
                            loadingSwal.close();
                            Swal.fire({
                                title: 'Error!',
                                text: error.message,
                                icon: 'error'
                            });
                            console.error('Error:', error);
                        });
                    }
                });
            });


    // View Expert Details - Enhanced
    $(document).on("click", ".viewExpertBtn", function () {
        const expertId = $(this).data("id");
        
        Swal.fire({
            title: 'Loading Expert Details...',
            html: 'Please wait while we fetch the details',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        $.ajax({
            url: "get_experts.php",
            type: "POST",
            data: { expert_id: expertId },
            success: function (response) {
                const result = JSON.parse(response);

                if (result.status === "success") {
                    const expert = result.data;
                    
                    Swal.fire({
                        title: `<div style="display:flex;align-items:center;justify-content:center;">
                                    <i class="fas fa-user-tie" style="font-size:24px;margin-right:10px;color:#9b59b6;"></i>
                                    <span>${expert.name}</span>
                                </div>`,
                        html: `
                            <div class="expert-view-container">
                                <div class="row">
                                    <div class="col-md-5">
                                        <img src="${expert.image_url}" 
                                             alt="Expert Image" 
                                             class="expert-view-img"
                                             style="width:100%;height:auto;object-fit:cover;">
                                    </div>
                                    <div class="col-md-7">
                                        <div class="expert-details">
                                            <p><strong>Specialization:</strong> ${expert.specialization}</p>
                                            <p><strong>Description:</strong> ${expert.description}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `,
                        showConfirmButton: false,
                        showCloseButton: true,
                        width: '800px',
                        customClass: {
                            container: 'animated fadeIn'
                        }
                    });
                } else {
                    Swal.fire({
                        title: '<i class="fas fa-exclamation-triangle" style="color:#f39c12;"></i> Error',
                        text: "Failed to fetch expert details.",
                        icon: "error"
                    });
                }
            },
            error: function () {
                Swal.fire({
                    title: '<i class="fas fa-exclamation-triangle" style="color:#f39c12;"></i> Error',
                    text: "An error occurred while fetching expert details.",
                    icon: "error"
                });
            },
        });
    });

       // Edit Expert button handler
    // Edit Expert button handler
document.addEventListener('click', function(e) {
    if (e.target.closest('.editExpertBtn')) {
        e.preventDefault();
        const expertId = e.target.closest('.editExpertBtn').dataset.id;
        
        // Show loading indicator
        const loadingSwal = Swal.fire({
            title: 'Loading Expert Data...',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });
        
        // Fetch expert data
        fetch('get_experts.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `expert_id=${expertId}`
        })
        .then(async response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            
            // Try to parse as JSON, fall back to text if needed
            try {
                return await response.json();
            } catch (e) {
                const text = await response.text();
                throw new Error(text || 'Invalid server response');
            }
        })
        .then(data => {
            loadingSwal.close();
            
            if (data.status !== 'success') {
                throw new Error(data.message || 'Failed to fetch expert data');
            }
            
            // Ensure all required fields exist
            const expert = {
                expert_id: data.data.expert_id || expertId,
                name: data.data.name || '',
                Experts_email: data.data.Experts_email || '',
                Contact_no: data.data.Contact_no || '',
                specialization: data.data.specialization || '',
                description: data.data.description || '',
                image_url: data.data.image_url || ''
            };
            
            showEditExpertModal(expert);
        })
        .catch(error => {
            loadingSwal.close();
            Swal.fire({
                title: 'Error!',
                text: error.message,
                icon: 'error'
            });
            console.error('Error:', error);
        });
    }
});

function showEditExpertModal(expert) {
    const modalId = 'editExpertModal-' + Date.now();
    
    const modalHTML = `
        <div id="${modalId}" style="text-align:left;">
            <input type="hidden" id="editExpertId" value="${expert.expert_id}">
            <input type="hidden" id="currentImage" value="${expert.image_url}">
            
            <div style="margin-bottom:15px;">
                <label style="display:block;margin-bottom:5px;color:#555;font-weight:500;">Name*</label>
                <input type="text" id="editExpertName" class="swal2-input" value="${expert.name}" required>
            </div>
            <div style="margin-bottom:15px;">
                <label style="display:block;margin-bottom:5px;color:#555;font-weight:500;">Email</label>
                <input type="email" id="editExpertEmail" class="swal2-input" value="${expert.Experts_email || ''}">
            </div>
            <div style="margin-bottom:15px;">
                <label style="display:block;margin-bottom:5px;color:#555;font-weight:500;">Contact Number</label>
                <input type="tel" id="editExpertContact" class="swal2-input" value="${expert.Contact_no || ''}">
            </div>
            <div style="margin-bottom:15px;">
                <label style="display:block;margin-bottom:5px;color:#555;font-weight:500;">Specialization*</label>
                <input type="text" id="editSpecialization" class="swal2-input" value="${expert.specialization}" required>
            </div>
            <div style="margin-bottom:15px;">
                <label style="display:block;margin-bottom:5px;color:#555;font-weight:500;">Description*</label>
                <textarea id="editDescription" class="swal2-input" style="height:100px;" required>${expert.description}</textarea>
            </div>
            <div>
                <label style="display:block;margin-bottom:5px;color:#555;font-weight:500;">Current Image</label>
                <img src="${expert.image_url}" style="max-width:100px;max-height:100px;display:block;margin-bottom:10px;" onerror="this.style.display='none'">
                
                <label style="display:block;margin-bottom:5px;color:#555;font-weight:500;">Update Image (Optional)</label>
                <div id="editImageUploadArea" style="border:2px dashed #ddd;border-radius:8px;padding:15px;text-align:center;cursor:pointer;">
                    <i class="fas fa-sync-alt" style="font-size:24px;color:#3498db;margin-bottom:5px;"></i>
                    <p style="margin:0;color:#777;">Click to change image</p>
                    <input type="file" id="editExpertImage" style="display:none;" accept="image/*">
                </div>
                <div id="editFileNameDisplay" style="margin-top:5px;font-size:12px;color:#777;"></div>
            </div>
        </div>
    `;
    
    Swal.fire({
        title: 'Edit Expert',
        html: modalHTML,
        showCancelButton: true,
        confirmButtonText: 'Save Changes',
        cancelButtonText: 'Cancel',
        showLoaderOnConfirm: true,
        didOpen: () => {
            const uploadArea = document.querySelector(`#${modalId} #editImageUploadArea`);
            const fileInput = document.querySelector(`#${modalId} #editExpertImage`);
            const fileNameDisplay = document.querySelector(`#${modalId} #editFileNameDisplay`);
            
            uploadArea.addEventListener('click', function(e) {
                e.stopPropagation();
                fileInput.click();
            });
            
            fileInput.addEventListener('change', function() {
                if (this.files[0]) {
                    const validTypes = ['image/jpeg', 'image/png', 'image/webp'];
                    if (!validTypes.includes(this.files[0].type)) {
                        Swal.showValidationMessage('Only JPG, PNG, or WEBP images are allowed');
                        return;
                    }
                    fileNameDisplay.textContent = `Selected: ${this.files[0].name}`;
                }
            });
        },
        preConfirm: () => {
            const name = document.querySelector(`#${modalId} #editExpertName`).value.trim();
            const specialization = document.querySelector(`#${modalId} #editSpecialization`).value.trim();
            const description = document.querySelector(`#${modalId} #editDescription`).value.trim();
            
            if (!name) {
                Swal.showValidationMessage('Name is required');
                return false;
            }
            if (!specialization) {
                Swal.showValidationMessage('Specialization is required');
                return false;
            }
            if (!description) {
                Swal.showValidationMessage('Description is required');
                return false;
            }
            
            const data = {
                expert_id: document.querySelector(`#${modalId} #editExpertId`).value,
                name: name,
                email: document.querySelector(`#${modalId} #editExpertEmail`).value.trim(),
                contact_no: document.querySelector(`#${modalId} #editExpertContact`).value.trim(),
                specialization: specialization,
                description: description,
                current_image: document.querySelector(`#${modalId} #currentImage`).value
            };
            
            const fileInput = document.querySelector(`#${modalId} #editExpertImage`);
            if (fileInput.files[0]) {
                const file = fileInput.files[0];
                const fileName = file.name;
                const fileExt = fileName.split('.').pop().toLowerCase();
                const baseName = fileName.substring(0, fileName.lastIndexOf('.')).replace(/[^a-zA-Z0-9_-]/g, '_');
                data.image_path = `/Farming_meds/Uploads/Experts/${baseName}.${fileExt}`;
            }
            
            return data;
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const loadingSwal = Swal.fire({
                title: 'Updating Expert...',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });
            
            fetch('edit_experts.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(result.value)
            })
            .then(async response => {
                let data;
                try {
                    data = await response.json();
                } catch {
                    const text = await response.text();
                    throw new Error(text || 'Invalid server response');
                }
                
                if (!response.ok) {
                    throw new Error(data.message || 'Failed to update expert');
                }
                return data;
            })
            .then(data => {
                loadingSwal.close();
                Swal.fire({
                    title: 'Success!',
                    text: data.message,
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false
                });
                fetchExperts();
            })
            .catch(error => {
                loadingSwal.close();
                Swal.fire({
                    title: 'Error!',
                    text: error.message,
                    icon: 'error'
                });
                console.error('Error:', error);
            });
        }
    });
}

    // Delete Expert - Enhanced
    $(document).on('click', '.deleteExpertBtn', function () {
        const expertId = $(this).data('id');
        
        Swal.fire({
            title: '<div style="display:flex;align-items:center;justify-content:center;"><i class="fas fa-exclamation-triangle" style="font-size:24px;margin-right:10px;color:#e74c3c;"></i><span>Confirm Deletion</span></div>',
            html: `<div style="text-align:center;padding:15px;">
                     <i class="fas fa-trash-alt" style="font-size:48px;color:#e74c3c;margin-bottom:15px;"></i>
                     <p>Are you sure you want to delete this expert? This action cannot be undone.</p>
                  </div>`,
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-trash"></i> Yes, delete it!',
            cancelButtonText: '<i class="fas fa-times"></i> Cancel',
            confirmButtonColor: '#e74c3c',
            reverseButtons: true,
            customClass: {
                confirmButton: 'animated pulse'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Deleting Expert...',
                    html: 'Please wait while we remove the expert',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                $.ajax({
                    url: 'delete_experts.php',
                    method: 'POST',
                    data: { expert_id: expertId },
                    success: function (response) {
                        Swal.fire({
                            title: '<i class="fas fa-check-circle" style="color:#2ecc71;"></i> Deleted!',
                            html: 'The expert has been deleted.',
                            timer: 2000,
                            timerProgressBar: true,
                            showConfirmButton: false
                        });
                        fetchExperts();
                    },
                    error: function () {
                        Swal.fire({
                            title: '<i class="fas fa-exclamation-circle" style="color:#e74c3c;"></i> Error!',
                            text: 'Failed to delete expert',
                            icon: 'error'
                        });
                    }
                });
            }
        });
    });
    </script>
</body>
</html>