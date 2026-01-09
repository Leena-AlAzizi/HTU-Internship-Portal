<?php
session_start();
require '../db_connection.php';

if (!isset($_SESSION['staff_id']) || $_SESSION['user_type'] !== 'staff') {
    header("Location: ../login.php");
    exit();
}

// Get basic staff data for the sidebar
// (We assume a simple staff table structure based on previous examples)
$username = $_SESSION['username'] ?? 'Staff User'; 
?>

<!DOCTYPE html>
<html lang="en" class="h-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Guide</title>
    <link rel="icon" type="image/x-icon" href="../img\htu_logo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.0/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300..800;1,300..800&display=swap" rel="stylesheet">
</head>
<body class="h-100">
    <div class="container-fluid h-100">
        <div class="row h-100">
            <div class="left-side col-md-2 ps-1">
                <nav class="navbar navbar-expand-lg navbar-light flex-column h-100">
                    <a class="navbar-brand mx-0 mb-2" href="#">
                        <label class="ms-2 font-size-14px font-weight-500 color-876363">
                            <label class="font-weight-400 color-000">Hello, </label> <?= htmlspecialchars($username) ?>
                        </label>
                    </a>
                    <div class="collapse navbar-collapse w-100" id="navbarNav">
                        <ul class="navbar-nav flex-column justify-content-between h-100 w-100">
                            <div>
                                <li class="nav-item"><a href="dashboard.php" class="btn w-100 text-start"><i class="bi bi-house-door"></i> <label class="ms-2">Dashboard</label></a></li>
                                <li class="nav-item"><a href="students.php" class="btn w-100 text-start"><i class="bi bi-people"></i> <label class="ms-2">Students</label></a></li>
                                <li class="nav-item"><a href="offers.php" class="btn w-100 text-start"><i class="bi bi-briefcase"></i> <label class="ms-2">Offers</label></a></li>
                                <li class="nav-item"><a href="external_offer.php" class="btn w-100 text-start"><i class="bi bi-person-plus"></i> <label class="ms-2">External Offers</label></a></li>
                                <li class="nav-item"><a href="companies.php" class="btn w-100 text-start"><i class="bi bi-buildings"></i> <label class="ms-2">Companies</label></a></li>
                                <li class="nav-item"><a href="staff_guide.php" class="btn w-100 text-start bg-color-F5F0F0"><i class="bi bi-info-circle-fill"></i> <label class="ms-2">Portal Guide</label></a></li>
                            </div> 
                            <li class="nav-item mb-5"><a href="../logout.php" class="btn w-100 text-start font-weight-500 color-876363"><i class="bi bi-box-arrow-right"></i> <label class="ms-2">Logout</label></a></li>
                        </ul>
                    </div>
                </nav>
            </div>
            
            <div class="right-side col-md-10 p-4">
                <div class="d-flex flex-column">
                    <label for="" class="font-weight-600 font-size-22px">Staff Portal Guide</label>
                    <label for="" class="font-size-12px mt-3 color-5f5f5f">Comprehensive instructions for administrative oversight and portal management.</label>

                    <div class="mt-4 p-4 bg-color-f4f4f4 border-radius-10px">
                        <h4 class="font-weight-600 color-876363 mb-3">Administrative Focus</h4>
                        <p class="font-size-13px text-muted">
                            Mission: To ensure a fair, transparent, and efficient internship coordination process by providing advanced analytical and managerial tools for effective decision-making.
                        </p>
                    </div>
                    
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <h4 class="font-weight-600 font-size-22px mb-3">Supervisory FAQ</h4>
                            
                            <div class="accordion" id="staffGuideAccordion">
                                
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="headingS1">
                                        <button class="accordion-button collapsed font-size-14px font-weight-500" type="button" 
                                                data-bs-toggle="collapse" data-bs-target="#collapseS1" aria-expanded="false" aria-controls="collapseS1">
                                            <i class="bi bi-calendar-range me-2"></i> How do I set the official internship deadline?
                                        </button>
                                    </h2>
                                    <div id="collapseS1" class="accordion-collapse collapse " aria-labelledby="headingS1" data-bs-parent="#staffGuideAccordion">
                                        <div class="accordion-body font-size-13px color-5f5f5f">
                                            The official deadline for students to secure an internship can be set on the <strong>Dashboard</strong> page. This date is critical as it is displayed to all students and affects final placement tracking.
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="headingS2">
                                        <button class="accordion-button collapsed font-size-14px font-weight-500" type="button" 
                                                data-bs-toggle="collapse" data-bs-target="#collapseS2" aria-expanded="false" aria-controls="collapseS2">
                                            <i class="bi bi-person-plus me-2"></i> How to approve or reject External Offers?
                                        </button>
                                    </h2>
                                    <div id="collapseS2" class="accordion-collapse collapse" aria-labelledby="headingS2" data-bs-parent="#staffGuideAccordion">
                                        <div class="accordion-body font-size-13px color-5f5f5f">
                                            All submitted External Offers (offers students bring from external companies) are handled on the <strong>External Offers Page</strong>. Use the actions buttons next to each submission to review the details and confirm or reject the offer based on university criteria.
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="headingS3">
                                        <button class="accordion-button collapsed font-size-14px font-weight-500" type="button" 
                                                data-bs-toggle="collapse" data-bs-target="#collapseS3" aria-expanded="false" aria-controls="collapseS3">
                                            <i class="bi bi-graph-up me-2"></i> How do I analyze student placement status?
                                        </button>
                                    </h2>
                                    <div id="collapseS3" class="accordion-collapse collapse" aria-labelledby="headingS3" data-bs-parent="#staffGuideAccordion">
                                        <div class="accordion-body font-size-13px color-5f5f5f">
                                            The <strong>Dashboard</strong> contains key analytical charts, such as the <strong>Overall Student Status Distribution</strong> and <strong>Top Companies by Offers</strong>. These tools help you quickly identify students who need support and determine the most active corporate partners.
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="headingS4">
                                        <button class="accordion-button collapsed font-size-14px font-weight-500" type="button" 
                                                data-bs-toggle="collapse" data-bs-target="#collapseS4" aria-expanded="false" aria-controls="collapseS4">
                                            <i class="bi bi-buildings me-2"></i> How to manage Company Accounts?
                                        </button>
                                    </h2>
                                    <div id="collapseS4" class="accordion-collapse collapse" aria-labelledby="headingS4" data-bs-parent="#staffGuideAccordion">
                                        <div class="accordion-body font-size-13px color-5f5f5f">
                                            The <strong>Companies Page</strong> provides a complete list of all registered partners. You can view their profiles, contact details, and change their account status (e.g., suspend or activate) if necessary.
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                    
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
</body>
</html>