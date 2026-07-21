<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Testimonials</title>
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
        .star-rating {
            color: gold;
            font-size: 1.2em;
        }
        .table img {
            max-width: 50px;
            height: auto;
            border-radius: 50%;
        }
    </style>
</head>
<body>
    <!-- Top Bar -->
    <div class="top-bar">
        <h4>Manage Testimonials</h4>
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
                <a href="manage_testimonials.php" class="nav-link active">
                    <i class="fas fa-quote-left"></i> Testimonials
                </a>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="content">
            <input type="text" id="searchTestimonial" class="form-control mb-4" placeholder="Search testimonials...">

            <table class="table table-hover table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Client Image</th>
                        <th>Client Name</th>
                        <th>Expert</th>
                        <th>Profession</th>
                        <th>Rating</th>
                        <th>Testimonial</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="testimonialList">
                    <!-- Testimonials will be loaded here via AJAX -->
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

    <!-- Add/Edit Testimonial Modal -->
    <div class="modal fade" id="testimonialModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Add New Testimonial</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="testimonialForm">
                        <input type="hidden" id="testimonialId">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="expertId" class="form-label">Expert</label>
                                <select class="form-select" id="expertId" required>
                                    <option value="">Select Expert</option>
                                    <!-- Options will be populated via JavaScript -->
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="clientName" class="form-label">Client Name</label>
                                <input type="text" class="form-control" id="clientName" required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="profession" class="form-label">Profession</label>
                                <input type="text" class="form-control" id="profession" required>
                            </div>
                            <div class="col-md-6">
                                <label for="city" class="form-label">City</label>
                                <input type="text" class="form-control" id="city" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="clientImage" class="form-label">Client Image URL</label>
                            <input type="text" class="form-control" id="clientImage" placeholder="Enter image URL">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Rating</label>
                            <div class="star-rating">
                                <i class="fas fa-star" data-rating="1"></i>
                                <i class="fas fa-star" data-rating="2"></i>
                                <i class="fas fa-star" data-rating="3"></i>
                                <i class="far fa-star" data-rating="4"></i>
                                <i class="far fa-star" data-rating="5"></i>
                                <input type="hidden" id="rating" value="3">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="testimonialText" class="form-label">Testimonial</label>
                            <textarea class="form-control" id="testimonialText" rows="5" required></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="saveTestimonialBtn">Save Testimonial</button>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    $(document).ready(function () {
        let currentPage = 1;
        const testimonialsPerPage = 7;
        const testimonialModal = new bootstrap.Modal(document.getElementById('testimonialModal'));
        let isEditMode = false;

        // Function to handle AJAX errors
        function handleAjaxError(xhr, status, error) {
            console.error("AJAX Error:", status, error);
            let errorMsg = "An error occurred while processing your request.";
            
            try {
                const response = JSON.parse(xhr.responseText);
                if (response.message) {
                    errorMsg = response.message;
                }
            } catch (e) {
                // Not JSON response
                errorMsg = xhr.responseText || errorMsg;
            }
            
            Swal.fire("Error", errorMsg, "error");
        }

        // Fetch testimonials with pagination and search
        function fetchTestimonials(searchQuery = '', page = 1) {
            $.ajax({
                url: "fetch_testimonials.php",
                type: "GET",
                data: { 
                    search: searchQuery, 
                    page: page, 
                    limit: testimonialsPerPage 
                },
                dataType: "json",
                success: function (response) {
                    if (response && response.status === "success") {
                        const testimonials = response.data;
                        const totalTestimonials = response.total;
                        const totalPages = Math.ceil(totalTestimonials / testimonialsPerPage);

                        renderTestimonials(testimonials, page);
                        
                        // Update pagination controls
                        $("#pageInfo").text(`Page ${page} of ${totalPages}`);
                        $("#prevPageBtn").toggleClass("disabled", page <= 1);
                        $("#nextPageBtn").toggleClass("disabled", page >= totalPages);
                    } else {
                        Swal.fire("Error", response ? response.message : "Failed to fetch testimonials", "error");
                    }
                },
                error: handleAjaxError
            });
        }

        // Render testimonials to the table
        function renderTestimonials(testimonials, page) {
            let html = "";
            
            if (testimonials.length > 0) {
                testimonials.forEach((testimonial, index) => {
                    const serialNumber = (page - 1) * testimonialsPerPage + index + 1;
                    const stars = '★'.repeat(testimonial.rating) + '☆'.repeat(5 - testimonial.rating);
                    const shortTestimonial = testimonial.testimonial.length > 50 ? 
                        testimonial.testimonial.substring(0, 50) + '...' : 
                        testimonial.testimonial;
                    const clientImage = testimonial.client_image || 'https://ui-avatars.com/api/?name=' + encodeURIComponent(testimonial.client_name) + '&background=random';

                    html += `
                        <tr>
                            <td>${serialNumber}</td>
                            <td><img src="${clientImage}" alt="${testimonial.client_name}" class="img-thumbnail"></td>
                            <td>${testimonial.client_name}</td>
                            <td>${testimonial.expert_name || 'N/A'}</td>
                            <td>${testimonial.profession}</td>
                            <td class="star-rating">${stars}</td>
                            <td>${shortTestimonial}</td>
                            <td>${new Date(testimonial.created_at).toLocaleDateString()}</td>
                            <td class="buttons">
                                <button class="btn btn-info btn-sm viewTestimonialBtn" data-id="${testimonial.id}">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="btn btn-warning btn-sm editTestimonialBtn" data-id="${testimonial.id}">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-danger btn-sm deleteTestimonialBtn" data-id="${testimonial.id}">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    `;
                });
            } else {
                html = '<tr><td colspan="9" class="text-center">No testimonials found.</td></tr>';
            }
            
            $("#testimonialList").html(html);
        }

        // Fetch experts for dropdown
        function fetchExperts() {
            $.ajax({
                url: "fetch_expertsss.php",
                type: "GET",
                dataType: "json",
                success: function (response) {
                    if (response && response.status === "success") {
                        let options = '<option value="">Select Expert</option>';
                        response.experts.forEach(expert => {
                            options += `<option value="${expert.expert_id}">${expert.name}</option>`;
                        });
                        $("#expertId").html(options);
                    }
                },
                error: handleAjaxError
            });
        }

        // Initialize star rating
        function initStarRating() {
            $(".star-rating i").click(function() {
                const rating = $(this).data("rating");
                $("#rating").val(rating);
                $(".star-rating i").removeClass("fas").addClass("far");
                $(".star-rating i").slice(0, rating).removeClass("far").addClass("fas");
            });
        }

        // Call fetchTestimonials on page load
        fetchTestimonials();
        fetchExperts();
        initStarRating();

        // Search testimonials
        $("#searchTestimonial").on("input", function () {
            currentPage = 1;
            fetchTestimonials($(this).val(), currentPage);
        });

        // Previous page button
        $("#prevPageBtn").click(function (e) {
            e.preventDefault();
            if (currentPage > 1) {
                currentPage--;
                fetchTestimonials($("#searchTestimonial").val(), currentPage);
            }
        });

        // Next page button
        $("#nextPageBtn").click(function (e) {
            e.preventDefault();
            currentPage++;
            fetchTestimonials($("#searchTestimonial").val(), currentPage);
        });

        // Add Testimonial button
        $("#addTestimonialBtn").click(function () {
            isEditMode = false;
            $("#modalTitle").text("Add New Testimonial");
            $("#testimonialForm")[0].reset();
            $("#testimonialId").val("");
            $("#rating").val(3);
            $(".star-rating i").removeClass("fas").addClass("far");
            $(".star-rating i").slice(0, 3).removeClass("far").addClass("fas");
            testimonialModal.show();
        });

        // Save Testimonial
        $("#saveTestimonialBtn").click(function () {
            const formData = {
                id: $("#testimonialId").val(),
                expert_id: $("#expertId").val(),
                client_name: $("#clientName").val(),
                profession: $("#profession").val(),
                city: $("#city").val(),
                client_image: $("#clientImage").val(),
                rating: $("#rating").val(),
                testimonial: $("#testimonialText").val()
            };

            // Validate form
            if (!formData.expert_id || !formData.client_name || !formData.profession || 
                !formData.city || !formData.testimonial) {
                Swal.fire("Error", "Please fill in all required fields.", "error");
                return;
            }

            const url = isEditMode ? "update_testimonial.php" : "add_testimonial.php";
            const method = isEditMode ? "PUT" : "POST";

            $.ajax({
                url: url,
                type: method,
                data: formData,
                dataType: "json",
                success: function (response) {
                    if (response && response.status === "success") {
                        Swal.fire(
                            'Success!',
                            isEditMode ? 'Testimonial updated successfully.' : 'Testimonial added successfully.',
                            'success'
                        ).then(() => {
                            testimonialModal.hide();
                            fetchTestimonials($("#searchTestimonial").val(), currentPage);
                        });
                    } else {
                        Swal.fire("Error", response ? response.message : "Operation failed", "error");
                    }
                },
                error: handleAjaxError
            });
        });

        // Edit Testimonial
        $(document).on("click", ".editTestimonialBtn", function () {
            isEditMode = true;
            const testimonialId = $(this).data("id");

            // Show loading indicator
            Swal.fire({
                title: 'Loading...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: "get_testimonial.php",
                type: "GET",
                data: { id: testimonialId },
                dataType: "json",
                success: function (response) {
                    Swal.close();
                    
                    if (response && response.status === "success") {
                        const testimonial = response.testimonial;
                        
                        $("#modalTitle").text("Edit Testimonial");
                        $("#testimonialId").val(testimonial.id);
                        $("#expertId").val(testimonial.expert_id);
                        $("#clientName").val(testimonial.client_name);
                        $("#profession").val(testimonial.profession);
                        $("#city").val(testimonial.city);
                        $("#clientImage").val(testimonial.client_image || '');
                        $("#rating").val(testimonial.rating);
                        $("#testimonialText").val(testimonial.testimonial);
                        
                        // Set star rating
                        $(".star-rating i").removeClass("fas").addClass("far");
                        $(".star-rating i").slice(0, testimonial.rating).removeClass("far").addClass("fas");
                        
                        testimonialModal.show();
                    } else {
                        Swal.fire("Error", response ? response.message : "Failed to fetch testimonial", "error");
                    }
                },
                error: handleAjaxError
            });
        });

        // View Testimonial
        $(document).on("click", ".viewTestimonialBtn", function () {
            const testimonialId = $(this).data("id");

            // Show loading indicator
            Swal.fire({
                title: 'Loading...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: "get_testimonial.php",
                type: "GET",
                data: { id: testimonialId },
                dataType: "json",
                success: function (response) {
                    Swal.close();
                    
                    if (response && response.status === "success") {
                        const testimonial = response.testimonial;
                        const stars = '★'.repeat(testimonial.rating) + '☆'.repeat(5 - testimonial.rating);
                        const clientImage = testimonial.client_image || 'https://ui-avatars.com/api/?name=' + encodeURIComponent(testimonial.client_name) + '&background=random';
                        
                        Swal.fire({
                            title: `<h3>Testimonial Details</h3>`,
                            html: `
                                <div class="container text-start">
                                    <div class="row mb-4">
                                        <div class="col-md-4 text-center">
                                            <img src="${clientImage}" alt="${testimonial.client_name}" class="img-thumbnail mb-3" style="max-width: 150px;">
                                            <h5>${testimonial.client_name}</h5>
                                            <p>${testimonial.profession}, ${testimonial.city}</p>
                                        </div>
                                        <div class="col-md-8">
                                            <h5>About Expert: ${testimonial.expert_name || 'N/A'}</h5>
                                            <p><strong>Rating:</strong> <span class="star-rating">${stars}</span></p>
                                            <p><strong>Date:</strong> ${new Date(testimonial.created_at).toLocaleDateString()}</p>
                                            <p><strong>Testimonial:</strong></p>
                                            <div class="card p-3 bg-light">
                                                <p>${testimonial.testimonial}</p>
                                            </div>
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
                        Swal.fire("Error", response ? response.message : "Failed to fetch testimonial", "error");
                    }
                },
                error: handleAjaxError
            });
        });

        // Delete Testimonial
        $(document).on("click", ".deleteTestimonialBtn", function () {
            const testimonialId = $(this).data("id");

            Swal.fire({
                title: 'Are you sure?',
                text: 'You won\'t be able to revert this!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "delete_testimonial.php",
                        type: "DELETE",
                        data: { id: testimonialId },
                        dataType: "json",
                        success: function (response) {
                            if (response && response.status === "success") {
                                Swal.fire(
                                    'Deleted!',
                                    'Testimonial has been deleted.',
                                    'success'
                                ).then(() => {
                                    fetchTestimonials($("#searchTestimonial").val(), currentPage);
                                });
                            } else {
                                Swal.fire("Error", response ? response.message : "Failed to delete testimonial", "error");
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