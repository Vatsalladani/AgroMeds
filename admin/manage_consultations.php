<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Consultations</title>
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
        .status-approved {
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
        .communication-whatsapp {
            color: #25D366;
            font-weight: bold;
        }
        .communication-phone {
            color: #007bff;
            font-weight: bold;
        }
        .communication-video_call {
            color: #6f42c1;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <!-- Top Bar -->
    <div class="top-bar">
        <h4>Manage Consultations</h4>
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
                <a href="manage_consultations.php" class="nav-link active">
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
            <input type="text" id="searchConsultation" class="form-control mb-4" placeholder="Search consultations...">

            <table class="table table-hover table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Consultation ID</th>
                        <th>User Name</th>
                        <th>Expert</th>
                        <th>Problem Description</th>
                        <th>Preferred Date/Time</th>
                        <th>Communication Method</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="consultationList">
                    <!-- Consultations will be loaded here via AJAX -->
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
    const consultationsPerPage = 8;

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

    function fetchConsultations(searchQuery = '', page = 1) {
        $.ajax({
            url: "fetch_consultations.php",
            type: "GET",
            data: { 
                search: searchQuery, 
                page: page, 
                limit: consultationsPerPage 
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
                    const consultations = responseData.data;
                    const totalConsultations = responseData.total;
                    const totalPages = Math.ceil(totalConsultations / consultationsPerPage);

                    // Update table content
                    let html = "";
                    if (consultations.length > 0) {
                        consultations.forEach((consultation, index) => {
                            // Determine status class
                            let statusClass = '';
                            if (consultation.status === 'pending') statusClass = 'status-pending';
                            else if (consultation.status === 'approved') statusClass = 'status-approved';
                            else if (consultation.status === 'rejected') statusClass = 'status-rejected';

                            // Determine communication method class
                            let commClass = '';
                            if (consultation.communication_method === 'whatsapp') commClass = 'communication-whatsapp';
                            else if (consultation.communication_method === 'phone') commClass = 'communication-phone';
                            else if (consultation.communication_method === 'video_call') commClass = 'communication-video_call';

                            // Format date and time
                            const prefDate = new Date(consultation.preferred_date + 'T' + consultation.preferred_time);
                            const formattedDateTime = prefDate.toLocaleDateString() + ' ' + prefDate.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});

                            // Truncate problem description if too long
                            const problemDesc = consultation.problem_description.length > 50 ? 
                                consultation.problem_description.substring(0, 50) + '...' : 
                                consultation.problem_description;

                            html += `
                                <tr>
                                    <td>${(page - 1) * consultationsPerPage + index + 1}</td>
                                    <td>${consultation.id}</td>
                                    <td>${consultation.user_name}</td>
                                    <td>${consultation.expert_name || 'Not assigned'}</td>
                                    <td>${problemDesc}</td>
                                    <td>${formattedDateTime}</td>
                                    <td class="${commClass}">${consultation.communication_method.replace('_', ' ').toUpperCase()}</td>
                                    <td class="${statusClass}">${consultation.status.toUpperCase()}</td>
                                    <td class="buttons">
                                        <button class="btn btn-info btn-sm viewConsultationBtn" data-id="${consultation.id}">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-warning btn-sm editConsultationBtn" data-id="${consultation.id}">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-danger btn-sm deleteConsultationBtn" data-id="${consultation.id}">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            `;
                        });
                    } else {
                        html = '<tr><td colspan="9" class="text-center">No consultations found.</td></tr>';
                    }
                    $("#consultationList").html(html);

                    // Update pagination controls
                    $("#pageInfo").text(`Page ${page} of ${totalPages}`);
                    $("#prevPageBtn").prop("disabled", page <= 1);
                    $("#nextPageBtn").prop("disabled", page >= totalPages);
                } else {
                    Swal.fire("Error", responseData.message || "Failed to fetch consultations", "error");
                }
            },
            error: handleAjaxError
        });
    }

    // Call fetchConsultations on page load
    fetchConsultations();

    // Search consultations
    $("#searchConsultation").on("input", function () {
        const searchQuery = $(this).val();
        currentPage = 1;
        fetchConsultations(searchQuery, currentPage);
    });

    // Previous page button
    $("#prevPageBtn").click(function () {
        if (currentPage > 1) {
            currentPage--;
            fetchConsultations($("#searchConsultation").val(), currentPage);
        }
    });

    // Next page button
    $("#nextPageBtn").click(function () {
        currentPage++;
        fetchConsultations($("#searchConsultation").val(), currentPage);
    });

    // View Consultation
    $(document).on("click", ".viewConsultationBtn", function () {
        const consultationId = $(this).data("id");

        // Show loading indicator
        Swal.fire({
            title: 'Loading...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        // Fetch consultation details
        $.ajax({
            url: "get_consultation_details.php",
            type: "POST",
            data: { consultation_id: consultationId },
            dataType: "json",
            success: function (response) {
                Swal.close();
                
                const result = response;

                if (result.status === "success") {
                    const consultation = result.consultation;

                    // Format date and time
                    const prefDate = new Date(consultation.preferred_date + 'T' + consultation.preferred_time);
                    const formattedDateTime = prefDate.toLocaleDateString() + ' ' + prefDate.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
                    const createdDate = new Date(consultation.created_at);
                    const formattedCreatedDate = createdDate.toLocaleDateString() + ' ' + createdDate.toLocaleTimeString();

                    // Determine status class
                    let statusClass = '';
                    if (consultation.status === 'pending') statusClass = 'status-pending';
                    else if (consultation.status === 'approved') statusClass = 'status-approved';
                    else if (consultation.status === 'rejected') statusClass = 'status-rejected';

                    // Determine communication method class
                    let commClass = '';
                    if (consultation.communication_method === 'whatsapp') commClass = 'communication-whatsapp';
                    else if (consultation.communication_method === 'phone') commClass = 'communication-phone';
                    else if (consultation.communication_method === 'video_call') commClass = 'communication-video_call';

                    // Display consultation details in a SweetAlert2 modal
                    Swal.fire({
                        title: `<h3>Consultation #${consultation.id}</h3>`,
                        html: `
                            <div class="container text-start">
                                <div class="row mb-4">
                                    <div class="col-md-6">
                                        <h5>User Details</h5>
                                        <p><strong>Name:</strong> ${consultation.user_name}</p>
                                        <p><strong>Email:</strong> ${consultation.user_email}</p>
                                        <p><strong>Phone:</strong> ${consultation.user_phone}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <h5>Consultation Information</h5>
                                        <p><strong>Expert:</strong> ${consultation.expert_name || 'Not assigned'}</p>
                                        <p><strong>Status:</strong> <span class="${statusClass}">${consultation.status.toUpperCase()}</span></p>
                                        <p><strong>Preferred Date/Time:</strong> ${formattedDateTime}</p>
                                        <p><strong>Communication Method:</strong> <span class="${commClass}">${consultation.communication_method.replace('_', ' ').toUpperCase()}</span></p>
                                        <p><strong>Submitted:</strong> ${formattedCreatedDate}</p>
                                    </div>
                                </div>
                                
                                <h5>Problem Description</h5>
                                <div class="card p-3 mb-4 bg-light">
                                    ${consultation.problem_description}
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
                    Swal.fire("Error", result.message || "Failed to fetch consultation details.", "error");
                }
            },
            error: function (xhr, status, error) {
                Swal.close();
                let errorMsg = "An error occurred while fetching consultation details.";
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

    // Edit Consultation - Modified version
$(document).on("click", ".editConsultationBtn", function () {
    const consultationId = $(this).data("id");

    // Show loading indicator
    Swal.fire({
        title: 'Loading...',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    // Fetch consultation details for editing
    $.ajax({
        url: "get_consultation_details.php",
        type: "POST",
        data: { consultation_id: consultationId },
        dataType: "json",
        success: function (response) {
            Swal.close();
            
            if (response.status === "success" && response.consultation) {
                const consultation = response.consultation;

                // Also fetch experts list for the dropdown
                $.ajax({
                    url: "fetch_expertsss.php",
                    type: "GET",
                    dataType: "json",
                    success: function (expertsResponse) {
                        let expertsOptions = '<option value="">-- Select Expert --</option>';
                        
                        // Check if expertsResponse is valid and has experts array
                        if (expertsResponse && expertsResponse.status === "success" && expertsResponse.experts && expertsResponse.experts.length > 0) {
                            expertsResponse.experts.forEach(expert => {
                                expertsOptions += `<option value="${expert.id}" ${expert.id == consultation.expert_id ? 'selected' : ''}>${expert.name}</option>`;
                            });
                        } else {
                            expertsOptions += '<option value="">No experts available</option>';
                        }

                        // Display edit form in a SweetAlert2 modal
                        Swal.fire({
                            title: 'Edit Consultation',
                            html: `
                                <div class="container text-start">
                                    <div class="mb-3">
                                        <label for="editStatus" class="form-label">Status</label>
                                        <select id="editStatus" class="form-select">
                                            <option value="pending" ${consultation.status === 'pending' ? 'selected' : ''}>PENDING</option>
                                            <option value="approved" ${consultation.status === 'approved' ? 'selected' : ''}>APPROVED</option>
                                            <option value="rejected" ${consultation.status === 'rejected' ? 'selected' : ''}>REJECTED</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="editExpert" class="form-label">Assign Expert</label>
                                        <select id="editExpert" class="form-select">
                                            ${expertsOptions}
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="editNotes" class="form-label">Admin Notes (will be included in email)</label>
                                        <textarea id="editNotes" class="form-control" rows="3" placeholder="Enter any notes for the user...">${consultation.admin_notes || ''}</textarea>
                                    </div>
                                    <div class="form-check mb-3">
                                        <input class="form-check-input" type="checkbox" id="sendEmail" checked>
                                        <label class="form-check-label" for="sendEmail">
                                            Send email notification to user
                                        </label>
                                    </div>
                                </div>
                            `,
                            showCancelButton: true,
                            confirmButtonText: 'Update',
                            preConfirm: () => {
                                return {
                                    consultation_id: consultationId,
                                    status: $('#editStatus').val(),
                                    expert_id: $('#editExpert').val(),
                                    admin_notes: $('#editNotes').val(),
                                    send_email: $('#sendEmail').is(':checked'),
                                    user_email: consultation.user_email,
                                    user_name: consultation.user_name
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
                                    url: 'update_consultation.php',
                                    method: 'POST',
                                    data: data,
                                    dataType: 'json',
                                    success: function (response) {
                                        Swal.close();
                                        if (response && response.status === 'success') {
                                            Swal.fire('Success', 'Consultation updated successfully!', 'success');
                                            fetchConsultations();
                                        } else {
                                            Swal.fire('Error', (response && response.message) || 'Failed to update consultation.', 'error');
                                        }
                                    },
                                    error: function (xhr, status, error) {
                                        Swal.close();
                                        let errorMsg = "An error occurred while updating the consultation.";
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
                    },
                    error: function(xhr, status, error) {
                        Swal.close();
                        let errorMsg = "Failed to load experts list.";
                        try {
                            const response = JSON.parse(xhr.responseText);
                            errorMsg = response.message || errorMsg;
                        } catch (e) {
                            errorMsg = xhr.responseText || errorMsg;
                        }
                        Swal.fire("Error", errorMsg, "error");
                    }
                });
            } else {
                Swal.fire("Error", (response && response.message) || "Failed to fetch consultation details.", "error");
            }
        },
        error: function (xhr, status, error) {
            Swal.close();
            let errorMsg = "An error occurred while fetching consultation details.";
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

    // Delete Consultation
    $(document).on('click', '.deleteConsultationBtn', function () {
        const consultationId = $(this).data('id');
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: 'delete_consultation.php',
                    method: 'POST',
                    data: { consultation_id: consultationId },
                    success: function (response) {
                        const res = JSON.parse(response);
                        if (res.status === 'success') {
                            Swal.fire('Deleted!', 'Consultation has been deleted.', 'success');
                            fetchConsultations();
                        } else {
                            Swal.fire('Error', res.message || 'Failed to delete consultation.', 'error');
                        }
                    },
                    error: function () {
                        Swal.fire('Error', 'An error occurred while deleting the consultation.', 'error');
                    }
                });
            }
        });
    });
});
    </script>
</body>
</html>