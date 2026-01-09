<?php
session_start();
require '../db_connection.php';

if (!isset($_SESSION['company_id']) || $_SESSION['user_type'] !== 'company') {
    header('Location: ../login.php');
    exit();
}

$company_id = $_SESSION['company_id'];

$stmt = $conn->prepare("SELECT company_name, image FROM companies WHERE company_id = ?");
$stmt->bind_param("i", $company_id);
$stmt->execute();
$result = $stmt->get_result();
$company = $result->fetch_assoc();
$stmt->close();

$company_name = $company['company_name'];
$company_image = (!empty($company['image']) && file_exists("../" . $company['image'])) 
                 ? "../" . $company['image'] 
                 : '../img/default_company.png';

$open_offers_count = $conn->query("SELECT COUNT(offer_id) AS total FROM offers WHERE company_id = '{$company_id}' AND status = 'Open'")->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html lang="en" class="h-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Company Guide</title>
    <link rel="icon" type="image/x-icon" href="../img/htu_logo.png">
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
                    <a class="navbar-brand mx-0 mb-2 d-flex align-items-center" href="#">
                        <img src="<?= htmlspecialchars($company_image) ?>" class="width-50px border-radius-50per height-50px img-cover" alt="Company Logo" id="left-nav-logo">
                        <label class="ms-2 font-size-14px font-weight-500 ">
                            <?= htmlspecialchars($company_name) ?>
                        </label>
                    </a>
                    <div class="collapse navbar-collapse w-100" id="navbarNav">
                        <ul class="navbar-nav flex-column justify-content-between h-100 w-100">
                            <div>
                                <li class="nav-item"><a href="dashboard.php" class="btn w-100 text-start"><i class="bi bi-house-door"></i> <label class="ms-2">Dashboard</label></a></li>
                                <li class="nav-item"><a href="offers.php" class="btn w-100 text-start"><i class="bi bi-briefcase"></i> <label class="ms-2">Offers</label></a></li>
                                <li class="nav-item"><a href="students.php" class="btn w-100 text-start"><i class="bi bi-people"></i> <label class="ms-2">Students</label></a></li>
                                <li class="nav-item"><a href="profile.php" class="btn w-100 text-start"><i class="bi bi-person"></i> <label class="ms-2">Profile</label></a></li>
                                <li class="nav-item"><a href="company_guide.php" class="btn w-100 text-start bg-color-F5F0F0"><i class="bi bi-info-circle-fill"></i> <label class="ms-2">Portal Guide</label></a></li>
                            </div> 
                            <li class="nav-item mb-5"><a href="../logout.php" class="btn w-100 text-start font-weight-500 color-876363"><i class="bi bi-box-arrow-right"></i> <label class="ms-2">Logout</label></a></li>
                        </ul>
                    </div>
                </nav>
            </div>
            
            <div class="right-side col-md-10 p-4">
                <div class="d-flex flex-column">
                    <label class="font-weight-600 font-size-22px">Portal Guide & FAQ</label>
                    <label class="font-size-12px mt-3 color-5f5f5f">Your quick reference for recruiting and offer management.</label>

                    <div class="mt-4 p-4 bg-color-f4f4f4 border-radius-10px">
                        <h4 class="font-weight-600 color-876363 mb-3">Partnership Vision</h4>
                        <p class="font-size-13px text-muted">
                             We strive to streamline your access to top HTU technical talent, ensuring efficient and quality recruitment that meets your specific industrial needs.
                        </p>
                    </div>
                    
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <h4 class="font-weight-600 font-size-22px mb-3">Recruitment FAQ</h4>
                            
                            <div class="accordion" id="companyGuideAccordion">
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="headingC1">
                                        <button class="accordion-button collapsed font-size-14px font-weight-500" type="button" 
                                                data-bs-toggle="collapse" data-bs-target="#collapseC1">
                                            <i class="bi bi-briefcase me-2"></i> How do I post a new internship offer?
                                        </button>
                                    </h2>
                                    <div id="collapseC1" class="accordion-collapse collapse" data-bs-parent="#companyGuideAccordion">
                                        <div class="accordion-body font-size-13px color-5f5f5f">
                                            Go to the <strong>Offers Page</strong>. Click the <strong>"Add New Offer"</strong> button. Fill in all required details. Once submitted, the offer will initially be listed as <strong>Pending</strong> review by the university staff.
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="headingC2">
                                        <button class="accordion-button collapsed font-size-14px font-weight-500" type="button" 
                                                data-bs-toggle="collapse" data-bs-target="#collapseC2">
                                            <i class="bi bi-people me-2"></i> How do I review applications and student profiles?
                                        </button>
                                    </h2>
                                    <div id="collapseC2" class="accordion-collapse collapse" data-bs-parent="#companyGuideAccordion">
                                        <div class="accordion-body font-size-13px color-5f5f5f">
                                            All applications are managed on the <strong>Students Page</strong>. You can view detailed profiles, including CVs, by clicking the <strong>View</strong> button.
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="headingC3">
                                        <button class="accordion-button collapsed font-size-14px font-weight-500" type="button" 
                                                data-bs-toggle="collapse" data-bs-target="#collapseC3">
                                            <i class="bi bi-headset me-2"></i> Who should I contact for support?
                                        </button>
                                    </h2>
                                    <div id="collapseC3" class="accordion-collapse collapse" data-bs-parent="#companyGuideAccordion">
                                        <div class="accordion-body font-size-13px color-5f5f5f">
                                            For technical support or account issues, please contact the Internship Coordinator at the university.
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>