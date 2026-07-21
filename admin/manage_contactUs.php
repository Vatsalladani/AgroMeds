<?php
session_start();

// Redirect to login page if the admin is not logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: adminlogin.php");
    exit();
}

// Include Composer's autoloader
require 'vendor/autoload.php'; // Adjust the path as needed

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Database connection parameters (UPDATE THESE WITH YOUR ACTUAL CREDENTIALS)
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "agriculture"; // Assuming the same database

// Create a new MySQLi connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check for connection errors
if ($conn->connect_error) {
    die("Connection failed: " . htmlspecialchars($conn->connect_error));
}

// Initialize variables
$contact_id = isset($_GET['contact_id']) ? intval($_GET['contact_id']) : 0;
$contact = null;
$search_term = isset($_GET['search_term']) ? $_GET['search_term'] : '';
$search_results = [];

// Search contacts logic
if (!empty($search_term)) {
    $searchQuery = "SELECT contact_id, user_id, first_name, last_name, email, phone, subject, query, created_at FROM contactus WHERE 
                    first_name LIKE ? OR 
                    last_name LIKE ? OR 
                    email LIKE ? OR 
                    subject LIKE ? OR 
                    query LIKE ?";
    if ($stmt = $conn->prepare($searchQuery)) {
        $search_term_param = "%" . $search_term . "%";
        $stmt->bind_param("sssss", $search_term_param, $search_term_param, $search_term_param, $search_term_param, $search_term_param);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $search_results[] = $row;
        }
        $stmt->close();
    } else {
        die("Error preparing the search statement.");
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Contact Us</title>
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

</head>
<body>
    <!-- Top Bar -->
    <div class="top-bar">
        <h4>Manage Contact Us</h4>
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
                <a href="manage_consultations.php" class="nav-link ">
                    <i class="fas fa-calendar-check"></i> Consultations
                </a>
                <a href="manage_contactUs.php" class="nav-link  active">
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
            <input type="text" id="searchContact" class="form-control mb-4" placeholder="Search contacts...">

            <table class="table table-hover table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Subject</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="contactList">

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
    <!-- JavaScript -->
    <script>
        $(document).ready(function () {
    let currentPage = 1;
    const contactsPerPage = 10;
    let contactsData = []; // Store all contacts data
    let totalPages = 0; // Store total number of pages

    // Fetch contacts with pagination and search
    function fetchContacts(searchQuery = '', page = 1) {
        $.ajax({
            url: "fetch_contacts.php",
            type: "GET",
            data: { search: searchQuery, page: page, limit: contactsPerPage },
            dataType: "json",
            success: function (response) {
                if (response.status === "success") {
                    contactsData = response.data; // Store all contacts data
                    const totalContacts = response.total;
                    totalPages = Math.ceil(totalContacts / contactsPerPage);

                    // Update table content
                    let html = "";
                    if (contactsData.length > 0) {
                        contactsData.forEach((contact, index) => {
                            const serialNumber = (page - 1) * contactsPerPage + index + 1; // Calculate serial number

                            html += `
                                <tr>
                                    <td>${serialNumber}</td>
                                    <td>${contact.first_name} ${contact.last_name}</td>
                                    <td>${contact.email}</td>
                                    <td>${contact.subject}</td>
                                    <td>
                                        <button type="button" class="btn btn-info btn-sm viewContactBtn"
                                                data-contact-id="${contact.contact_id}"
                                                data-first-name="${contact.first_name}"
                                                data-last-name="${contact.last_name}"
                                                data-email="${contact.email}"
                                                data-subject="${contact.subject}"
                                                data-query="${contact.query}">
                                            <i class="fas fa-eye"></i> View
                                        </button>
                                        <button type="button" class="btn btn-success btn-sm replyContactBtn"
                                                data-contact-id="${contact.contact_id}"
                                                data-first-name="${contact.first_name}"
                                                data-last-name="${contact.last_name}"
                                                data-email="${contact.email}"
                                                data-subject="${contact.subject}">
                                            <i class="fas fa-reply"></i> Reply
                                        </button>
                                        <button class="btn btn-danger btn-sm deleteContactBtn" data-id="${contact.contact_id}">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </td>
                                </tr>
                            `;
                        });
                    } else {
                        html = '<tr><td colspan="5" class="text-center">No contacts found.</td></tr>';
                    }
                    $("#contactList").html(html);

                    // Update pagination controls
                    $("#pageInfo").text(`Page ${page} of ${totalPages}`);
                    $("#prevPageBtn").prop("disabled", page <= 1);
                    $("#nextPageBtn").prop("disabled", page >= totalPages);
                } else {
                    Swal.fire("Error", "Failed to fetch contacts.", "error");
                }
            },
            error: function () {
                Swal.fire("Error", "An error occurred while fetching contacts.", "error");
            },
        });
    }

    // Call fetchContacts on page load
    fetchContacts();

    // Search contacts
    $("#searchContact").on("input", function () {
        const searchQuery = $(this).val();
        currentPage = 1; // Reset to first page on new search
        fetchContacts(searchQuery, currentPage);
    });

    // Previous page button
    $("#prevPageBtn").click(function () {
        if (currentPage > 1) {
            currentPage--;
            fetchContacts($("#searchContact").val(), currentPage);
        }
    });

    // Next page button
    $("#nextPageBtn").click(function () {
        if (currentPage < totalPages) {
            currentPage++;
            fetchContacts($("#searchContact").val(), currentPage);
        }
    });

    // View Contact Details
    $(document).on("click", ".viewContactBtn", function () {
        const contactId = $(this).data("contact-id");

        // Fetch contact details
        $.ajax({
            url: "get_contacts.php",
            type: "POST",
            data: { contact_id: contactId },
            success: function (response) {
                const result = JSON.parse(response);

                if (result.status === "success") {
                    const contact = result.data;

                    // Display contact details in a SweetAlert2 modal
                    Swal.fire({
                        title: `<h3>${contact.first_name} ${contact.last_name}</h3>`,
                        html: `
                            <div class="container text-start">
                                <div class="row">
                                    <div class="col-md-12">
                                        <p><strong>Email:</strong> ${contact.email}</p>
                                        <p><strong>Subject:</strong> ${contact.subject}</p>
                                        <p><strong>Query (Plain Text):</strong> <pre>${contact.query}</pre></p>
                                        <p><strong>Query (JSON):</strong> <pre>${JSON.stringify(contact.query, null, 2)}</pre></p>
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
                    Swal.fire("Error", "Failed to fetch contact details.", "error");
                }
            },
            error: function () {
                Swal.fire("Error", "An error occurred while fetching contact details.", "error");
            },
        });
    });

    $(document).on("click", ".deleteContactBtn", function () {
        const contactId = $(this).data("id");

        // Display SweetAlert confirmation dialog
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
                // Perform AJAX request to delete the contact
                $.ajax({
                    url: "delete_contact.php",
                    type: "POST",
                    data: { contact_id: contactId },
                    success: function (response) {
                        const result = JSON.parse(response);
                        if (result.status === "success") {
                            Swal.fire(
                                'Deleted!',
                                'Contact has been deleted.',
                                'success'
                            ).then(() => {
                                // Refresh the contact list
                                fetchContacts($("#searchContact").val(), currentPage);
                            });
                        } else {
                            Swal.fire("Error", "Failed to delete contact.", "error");
                        }
                    },
                    error: function () {
                        Swal.fire("Error", "An error occurred while deleting the contact.", "error");
                    }
                });
            }
        });
    });

    // Reply Contact
    $(document).on("click", ".replyContactBtn", function () {
        const contactId = $(this).data("contact-id");
        const firstName = $(this).data("first-name");
        const lastName = $(this).data("last-name");
        const email = $(this).data("email");
        const subject = $(this).data("subject");

        Swal.fire({
            title: `Reply to ${firstName} ${lastName}`,
            html: `
                <div class="form-group">
                    <label for="replyMessage">Message:</label>
                    <textarea class="form-control" id="replyMessage" rows="5"></textarea>
                </div>
            `,
            confirmButtonText: 'Send Reply',
            showCancelButton: true,
            cancelButtonText: 'Cancel',
            preConfirm: () => {
                const replyMessage = Swal.getPopup().querySelector('#replyMessage').value;
                if (!replyMessage) {
                    Swal.showValidationMessage('Please enter a reply message');
                }
                return { replyMessage: replyMessage };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                const replyMessage = result.value.replyMessage;
                // Perform AJAX request to send the reply
                $.ajax({
                    url: "send_reply.php",
                    type: "POST",
                    data: {
                        contact_id: contactId,
                        first_name: firstName,
                        last_name: lastName,
                        email: email,
                        subject: subject,
                        reply_message: replyMessage
                    },
                    success: function (response) {
                        const result = JSON.parse(response);
                        if (result.status === "success") {
                            Swal.fire(
                                'Sent!',
                                'Reply has been sent.',
                                'success'
                            );
                        } else {
                            Swal.fire("Error", "Failed to send reply.", "error");
                        }
                    },
                    error: function () {
                        Swal.fire("Error", "An error occurred while sending the reply.", "error");
                    }
                });
            }
        });
    });

    function encodeHTMLEntities(text) {
        var encodedText = document.createElement("textarea");
        encodedText.innerText = text;
        return encodedText.innerHTML;
    }
});

    </script>
</body>
</html>
