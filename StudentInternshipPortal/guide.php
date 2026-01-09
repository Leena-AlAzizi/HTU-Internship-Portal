<?php
session_start();
require '../db_connection.php';

if (!isset($_SESSION['student_id']) || $_SESSION['user_type'] !== 'student') {
    header('Location: ../login.php');
    exit();
}

$student_id = $_SESSION['student_id'];

$stmt = $conn->prepare("SELECT first_name, last_name, profile_image FROM students WHERE student_id = ?");
$stmt->bind_param("s", $student_id);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();
$stmt->close();

$deadline_key = 'INTERNSHIP_DEADLINE';
$student_deadline = null;

$stmt_deadline = $conn->prepare("SELECT setting_value FROM semester_settings WHERE setting_key = ?");
$stmt_deadline->bind_param("s", $deadline_key);
$stmt_deadline->execute();
$result_deadline = $stmt_deadline->get_result();
if ($row = $result_deadline->fetch_assoc()) {
    $student_deadline = $row['setting_value'];
}
$stmt_deadline->close();

$img_path = "../img/";
$profile_img_src = (!empty($student['profile_image']) && file_exists("../" . $student['profile_image'])) 
                   ? "../" . $student['profile_image'] 
                   : $img_path . "default.png";
?>

<!DOCTYPE html>
<html lang="en" class="h-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Guide</title>
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
                    <a class="navbar-brand mx-0 mb-2" href="#">
                        <img src="<?= $profile_img_src ?>" class="width-50px border-radius-50per height-50px img-cover" alt="Profile" id="left-nav-logo">
                        <label class="ms-2 font-size-14px font-weight-500">
                        Hello, <label class="color-B80000 ms-1"><?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) ?></label> 
                        </label>
                    </a>
                    <div class="collapse navbar-collapse w-100" id="navbarNav">
                        <ul class="navbar-nav flex-column justify-content-between h-100 w-100">
                            <div>
                                <li class="nav-item"><a href="dashboard.php" class="btn w-100 text-start"><i class="bi bi-house-door"></i> <label class="ms-2">Dashboard</label></a></li>
                                <li class="nav-item"><a href="offers.php" class="btn w-100 text-start"><i class="bi bi-file-text"></i> <label class="ms-2">Offers</label></a></li>
                                <li class="nav-item"><a href="profile.php" class="btn w-100 text-start"><i class="bi bi-person"></i> <label class="ms-2">Profile</label></a></li>
                                <li class="nav-item"><a href="guide.php" class="btn w-100 text-start bg-color-F5F0F0"><i class="bi bi-info-circle-fill"></i> <label class="ms-2">Portal Guide</label></a></li>
                            </div> 
                            <li class="nav-item mb-5">
                                <a href="../logout.php" class="btn w-100 text-start font-weight-500 color-B80000">
                                    <i class="bi bi-box-arrow-right"></i>
                                    <label class="ms-2">Logout</label>
                                </a>
                            </li>
                        </ul>
                    </div>
                </nav>
            </div>
            
            <div class="right-side col-md-10 p-4">
                <div class="d-flex flex-column">
                    <label class="font-weight-600 font-size-22px">Portal Guide & FAQ</label>
                    <label class="font-size-12px mt-3 color-5f5f5f">Find answers and instructions related to your internship journey.</label>

                    <div class="mt-4 p-4 bg-color-F5F0F0 border-radius-10px">
                        <h4 class="font-weight-600 color-B80000 mb-3">Our Vision for Your Future</h4>
                        <p class="font-size-13px text-muted">
                            To be the leading platform connecting HTU's talented students with elite industrial opportunities, ensuring a smooth transition from academia to professional life.
                        </p>
                    </div>
                    
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <h4 class="font-weight-600 font-size-22px mb-3">FAQ</h4>
                            
                            <div class="accordion" id="studentGuideAccordion">
                                
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="headingOne">
                                        <button class="accordion-button collapsed font-size-14px font-weight-500" type="button" 
                                                data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="false" aria-controls="collapseOne">
                                            <i class="bi bi-person-check me-2"></i> How to prepare my Profile & CV?
                                        </button>
                                    </h2>
                                    <div id="collapseOne" class="accordion-collapse collapse" aria-labelledby="headingOne" data-bs-parent="#studentGuideAccordion">
                                        <div class="accordion-body font-size-13px color-5f5f5f">
                                            Your profile is your first impression. Ensure all fields in the <strong>Profile Page</strong> are accurate and up-to-date. <br> Crucially, upload your latest CV file—companies will review this document directly.
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="headingTwo">
                                        <button class="accordion-button collapsed font-size-14px font-weight-500" type="button" 
                                                data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                            <i class="bi bi-search me-2"></i> How do I search and apply for offers?
                                        </button>
                                    </h2>
                                    <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#studentGuideAccordion">
                                        <div class="accordion-body font-size-13px color-5f5f5f">
                                            On this portal, companies offer training opportunities to students. To stay updated, go to the Offers Page.
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="headingThree">
                                        <button class="accordion-button collapsed font-size-14px font-weight-500" type="button" 
                                                data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                                            <i class="bi bi-clock me-2"></i> Tracking Application Status & Deadlines
                                        </button>
                                    </h2>
                                    <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#studentGuideAccordion">
                                        <div class="accordion-body font-size-13px color-5f5f5f">
                                            <p>All application statuses (Pending, Interview, Accepted, Rejected) are viewable on your <strong> Dashboard</strong> .</p>
                                            <p class="mb-0">
                                                <?php if ($student_deadline): ?>
                                                    The official deadline to secure a position this semester is <strong><?= date('F jS, Y', strtotime($student_deadline)) ?></strong> . It is crucial to find a placement before this date.
                                                <?php else: ?>
                                                    The official internship deadline has not been set yet. Please check the Dashboard regularly for updates.
                                                <?php endif; ?>
                                            </p>
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