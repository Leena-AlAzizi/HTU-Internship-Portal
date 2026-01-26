<?php
session_start();
require '../db_connection.php';

if (!isset($_SESSION['student_id']) || $_SESSION['user_type'] !== 'student') {
    header('Location: ../login.php');
    exit();
}

$student_id = $_SESSION['student_id'];

//  Get student data 
$stmt_student = $conn->prepare("SELECT first_name, last_name, profile_image FROM students WHERE student_id = ?");
$stmt_student->bind_param("s", $student_id);
$stmt_student->execute();
$result = $stmt_student->get_result();
$student = $result->fetch_assoc();
$stmt_student->close();

$img_path = "../img/";
$profile_img_src = (!empty($student['profile_image']) && file_exists("../" . $student['profile_image'])) 
                   ? "../" . $student['profile_image'] 
                   : $img_path . "default.png";

//  Get all pending offers for this student 
$query_pending = "
    SELECT 
        o.offer_id, 
        o.job_title, 
        o.location, 
        o.training_duration, 
        o.training_type, 
        o.salary, 
        o.application_deadline, 
        o.about_internship, 
        o.responsibilities, 
        o.requirements, 
        c.company_name, 
        c.image 
    FROM applications a
    JOIN offers o ON a.offer_id = o.offer_id
    JOIN companies c ON o.company_id = c.company_id
    WHERE a.student_id = ? AND a.status = 'Pending'
    ORDER BY a.application_date DESC
";
$stmt_pending = $conn->prepare($query_pending);
$stmt_pending->bind_param("s", $student_id);
$stmt_pending->execute();
$pending_offers = $stmt_pending->get_result();
$stmt_pending->close();

//  Get all offers in Interview status 
$query_interview = "
    SELECT 
        o.offer_id, 
        o.job_title, 
        o.location, 
        o.training_duration, 
        o.training_type, 
        o.salary, 
        o.application_deadline, 
        o.about_internship, 
        o.responsibilities, 
        o.requirements, 
        c.company_name, 
        c.image 
    FROM applications a
    JOIN offers o ON a.offer_id = o.offer_id
    JOIN companies c ON o.company_id = c.company_id
    WHERE a.student_id = ? AND a.status = 'Interview'
    ORDER BY a.application_date DESC
";
$stmt_interview = $conn->prepare($query_interview);
$stmt_interview->bind_param("s", $student_id);
$stmt_interview->execute();
$interview_offers = $stmt_interview->get_result();
$stmt_interview->close();

//  Get all offers in Rejected status 
$query_rejected = "
    SELECT 
        o.offer_id, 
        o.job_title, 
        o.location, 
        o.training_duration, 
        o.training_type, 
        o.salary, 
        o.application_deadline, 
        o.about_internship, 
        o.responsibilities, 
        o.requirements, 
        c.company_name, 
        c.image 
    FROM applications a
    JOIN offers o ON a.offer_id = o.offer_id
    JOIN companies c ON o.company_id = c.company_id
    WHERE a.student_id = ? AND a.status = 'Rejected'
    ORDER BY a.application_date DESC
";
$stmt_rejected = $conn->prepare($query_rejected);
$stmt_rejected->bind_param("s", $student_id);
$stmt_rejected->execute();
$rejected_offers = $stmt_rejected->get_result();
$stmt_rejected->close();

?>

<!DOCTYPE html>
<html lang="en" class="h-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Offers</title>
    <link rel="icon" type="image/x-icon" href="../img/htu_logo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.0/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <!--font-->
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
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                      <span class="navbar-toggler-icon"></span>
                    </button>
                    <div class="collapse navbar-collapse w-100" id="navbarNav">
                      <ul class="navbar-nav flex-column justify-content-between h-100 w-100">
                        <div>
                            <li class="nav-item">
                                <a href="dashboard.php" class="btn w-100 text-start">
                                    <i class="bi bi-house-door"></i>
                                    <label for="" class="ms-2">Dashboard</label>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="offers.php" class="btn w-100 text-start bg-color-F5F0F0">
                                    <i class="bi bi-file-text-fill"></i>
                                    <label for="" class="ms-2">Offers</label>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="profile.php" class="btn w-100 text-start">
                                    <i class="bi bi-person"></i>
                                    <label for="" class="ms-2">Profile</label>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="guide.php" class="btn w-100 text-start">
                                    <i class="bi bi-info-circle"></i> 
                                    <label for="" class="ms-2">Portal Guide</label>
                                </a>
                            </li>
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
                <div class="d-flex justify-content-between">
                  <label for="" class="font-weight-600 font-size-22px">Offers</label>
                  <button type="button" class="btn font-size-12px btn-E51A1A" data-bs-toggle="offcanvas" data-bs-target="#offcanvasRight" aria-controls="offcanvasRight"><i class="bi bi-plus me-2"></i>Add External Offer</button>
                    <div class="offcanvas offcanvas-end width-600px" tabindex="-1" id="offcanvasRight" aria-labelledby="offcanvasRightLabel">
                        <div class="offcanvas-header">
                        <div class="d-flex flex-column px-4">
                            <label for="" class="font-weight-600 font-size-22px">External Offer</label>
                            <label for="" class="font-size-12px mt-2 color-876363">
                                This form was made so you can insert an external offer you have received so that the university can approve it
                            </label>
                        </div>
                        </div>

                        <div class="offcanvas-body">
                            <form method="POST" action="submit_external_offer.php" enctype="multipart/form-data">
                                <div class="px-4">
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label font-size-13px color-876363">Company Name</label>
                                            <input type="text" name="company_name" class="form-control font-size-13px" placeholder="e.g. Google" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label font-size-13px color-876363">Position / Role</label>
                                            <input type="text" name="position_title" class="form-control font-size-13px" placeholder="e.g. Software Trainee" required>
                                        </div>
                                        <div class="col-md-12 mb-3">
                                            <label class="form-label font-size-13px color-876363">Location</label>
                                            <input type="text" name="location" class="form-control font-size-13px" placeholder="City, Country" required>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label font-size-13px color-876363">Starting Date</label>
                                            <input type="date" name="start_date" class="form-control font-size-13px" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label font-size-13px color-876363">Duration</label>
                                            <input type="text" name="duration" class="form-control font-size-13px" placeholder="e.g. 3 Months" required>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-12 mb-3">
                                            <label class="form-label font-size-13px color-876363">Description of Job</label>
                                            <textarea name="offer_description" class="form-control font-size-13px" rows="3" placeholder="Describe your main tasks..." required></textarea>
                                        </div>
                                        
                                        <div class="col-md-12 mb-4">
                                            <label class="form-label font-size-13px color-876363">Upload Offer Document (Optional)</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-white"><i class="bi bi-file-earmark-arrow-up"></i></span>
                                                <input type="file" name="document" accept=".pdf,.jpg,.png" class="form-control font-size-13px">
                                            </div>
                                            <small class="text-muted font-size-11px mt-1 d-block">Upload your acceptance letter or contract (PDF, JPG, PNG)</small>
                                        </div>
                                    </div>

                                    <div class="mt-4 mb-2 d-flex justify-content-end pb-5">
                                        <button type="button" class="btn px-4 me-2 btn-E5E8EB font-size-12px" data-bs-dismiss="offcanvas">Cancel</button>
                                        <button type="submit" class="btn px-4 btn-E51A1A font-size-12px text-white shadow-sm">Submit Offer</button>
                                    </div>

                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <label for="" class="font-weight-600 font-size-14px mt-4"><i class="bi bi-stars me-1"></i> New Offers</label>
                <div class = "row">
                    <?php if ($pending_offers->num_rows > 0): ?>
                    <?php while ($offer = $pending_offers->fetch_assoc()): ?>
                        <div class="d-flex margin-top-30px col-md-6">
                            <img src="<?= (!empty($offer['image']) && file_exists('../' . $offer['image'])) ? '../' . $offer['image'] : '../img/default_company.png' ?>"
                                alt="" 
                                class="width-15per me-4 border-radius-50per img-fit">

                            <div class="d-flex flex-column">
                                <label class="font-size-14px font-weight-600">
                                    <?= htmlspecialchars($offer['job_title']) ?>
                                </label>

                                <label class="font-size-13px color-5d5d5d">
                                    <?= htmlspecialchars($offer['company_name']) ?> - <?= htmlspecialchars($offer['location']) ?>
                                </label>

                                <label class="font-size-13px color-876363">
                                    Application deadline : <?= date('d/m/Y', strtotime($offer['application_deadline'])) ?>
                                </label>

                                <button class="font-size-11px border-none bg-color-F5F0F0 border-radius-15px px-3 py-2 mt-2 width-fit"
                                        data-bs-toggle="offcanvas" 
                                        data-bs-target="#offcanvasOffer<?= $offer['offer_id'] ?>"
                                        aria-controls="offcanvasOffer<?= $offer['offer_id'] ?>">
                                    View Offer
                                </button>

                                <!--  Offer Details Offcanvas  -->
                                <div class="offcanvas offcanvas-end width-600px" tabindex="-1" 
                                    id="offcanvasOffer<?= $offer['offer_id'] ?>" 
                                    aria-labelledby="offcanvasOfferLabel<?= $offer['offer_id'] ?>">
                                    <div class="offcanvas-header">
                                        <div class="d-flex flex-column px-4">
                                            <label class="font-weight-600 font-size-22px">Offer Details</label>
                                        </div>
                                    </div>
                                    <div class="offcanvas-body">
                                        <div class="d-flex flex-column px-4">
                                            <div class="d-flex mb-1">
                                                <img src="<?= (!empty($offer['image']) && file_exists('../' . $offer['image'])) ? '../' . $offer['image'] : '../img/default_company.png' ?>" 
                                                    alt="" 
                                                    class="width-12per border-radius-50per img-fit">
                                                <div class="d-flex flex-column ms-3">
                                                    <label class="font-size-18px mt-2 font-weight-500 color-B80000">
                                                        <?= htmlspecialchars($offer['company_name']) ?>
                                                    </label>
                                                    <label class="font-size-14px mt-1 font-weight-500">
                                                        <?= htmlspecialchars($offer['job_title']) ?>
                                                    </label>
                                                </div>
                                            </div>

                                            <div class="d-flex flex-column mx-3">
                                                <label class="font-size-13px mt-3 color-876363">Location</label>
                                                <label class="font-size-12px mt-2 font-weight-500 width-70per">
                                                    <?= htmlspecialchars($offer['location']) ?>
                                                </label>

                                                <label class="font-size-13px mt-3 color-876363">Training Duration</label>
                                                <label class="font-size-12px mt-2 font-weight-500 width-70per">
                                                    <?= htmlspecialchars($offer['training_duration']) ?>
                                                </label>

                                                <label class="font-size-13px mt-3 color-876363">Type of training</label>
                                                <label class="font-size-12px mt-2 font-weight-500 width-70per">
                                                    <?= htmlspecialchars($offer['training_type']) ?>
                                                </label>

                                                <label class="font-size-13px mt-3 color-876363">About the internship</label>
                                                <label class="font-size-12px mt-2 font-weight-500 width-70per">
                                                    <?= nl2br(htmlspecialchars($offer['about_internship'])) ?>
                                                </label>

                                                <label class="font-size-13px mt-3 color-876363">Responsibilities</label>
                                                <label class="font-size-12px mt-2 font-weight-500 width-70per">
                                                    <?= nl2br(htmlspecialchars($offer['responsibilities'])) ?>
                                                </label>

                                                <label class="font-size-13px mt-3 color-876363">Requirements</label>
                                                <label class="font-size-12px mt-2 font-weight-500 width-70per">
                                                    <?= nl2br(htmlspecialchars($offer['requirements'])) ?>
                                                </label>

                                                <label class="font-size-13px mt-3 color-876363">Is there a salary?</label>
                                                <label class="font-size-12px mt-2 font-weight-500 width-70per">
                                                    <?= $offer['salary'] ? 'Yes' : 'No' ?>
                                                </label>

                                                <label class="font-size-13px mt-3 color-876363">Application deadline</label>
                                                <label class="font-size-12px mt-2 font-weight-500 width-70per">
                                                    <?= date('d/m/Y', strtotime($offer['application_deadline'])) ?>
                                                </label>

                                                <div class="my-2 d-flex justify-content-end">
                                                    <button type="button" class="btn px-3 me-3 btn-E5E8EB font-size-12px btn-decline" data-offer="<?= $offer['offer_id'] ?>">Decline</button>
                                                    <button type="button" class="btn px-3 btn-E51A1A font-size-12px btn-continue" data-offer="<?= $offer['offer_id'] ?>">Continue the Process</button>
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- ________________ -->
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p class="font-size-12px color-876363 mt-3 ms-4"><i class="bi bi-bell-slash me-1"></i>No new pending offers.</p>
                <?php endif; ?>
                </div>
                <label for="" class="font-weight-600 font-size-14px mt-5"><i class="bi bi-hourglass-split me-2"></i>In-Process Offers</label>
                <div class="row">
                    <?php if($interview_offers->num_rows > 0): ?>
                    <?php while($offer = $interview_offers->fetch_assoc()): ?>
                        <div class="col-md-6">
                            <div class="d-flex margin-top-30px">
                                <img src="<?= (!empty($offer['image']) && file_exists('../' . $offer['image'])) ? '../' . $offer['image'] : '../img/default_company.png' ?>" 
                                    alt="" class="width-15per me-4 border-radius-50per img-fit">

                                <div class="d-flex flex-column">
                                    <label class="font-size-14px font-weight-600">
                                        <?= htmlspecialchars($offer['job_title']) ?>
                                    </label>
                                    <label class="font-size-13px color-876363">
                                        <?= htmlspecialchars($offer['company_name']) ?> - <?= htmlspecialchars($offer['location']) ?>
                                    </label>

                                    <button class="font-size-11px border-none bg-color-F5F0F0 border-radius-15px px-3 py-2 mt-2 width-fit"
                                            data-bs-toggle="offcanvas" 
                                            data-bs-target="#offcanvasOffer<?= $offer['offer_id'] ?>"
                                            aria-controls="offcanvasOffer<?= $offer['offer_id'] ?>">
                                        View Offer
                                    </button>

                                    <div class="offcanvas offcanvas-end width-600px" tabindex="-1" 
                                        id="offcanvasOffer<?= $offer['offer_id'] ?>" 
                                        aria-labelledby="offcanvasOfferLabel<?= $offer['offer_id'] ?>">
                                        <div class="offcanvas-header">
                                            <div class="d-flex px-4">
                                                <label class="font-weight-600 font-size-22px">Offer Details</label>
                                                <button class="font-size-11px border-none bg-color-d6f2ffff color-02577eff border-radius-15px px-3 ms-3">Interview</button>
                                            </div>
                                        </div>
                                        <div class="offcanvas-body">
                                            <div class="d-flex flex-column px-4">
                                                <div class="d-flex">
                                                    <img src="<?= (!empty($offer['image']) && file_exists('../' . $offer['image'])) ? '../' . $offer['image'] : '../img/default_company.png' ?>" 
                                                        alt="" 
                                                        class="width-12per border-radius-50per img-fit">
                                                    <div class="d-flex flex-column ms-3">
                                                        <label class="font-size-18px mt-2 font-weight-500 color-B80000">
                                                            <?= htmlspecialchars($offer['company_name']) ?>
                                                        </label>
                                                        <label class="font-size-14px mt-1 font-weight-500">
                                                            <?= htmlspecialchars($offer['job_title']) ?>
                                                        </label>
                                                    </div>
                                                </div>

                                                <div class="d-flex flex-column mx-3">
                                                    <label class="font-size-13px mt-3 color-876363">Location</label>
                                                    <label class="font-size-12px mt-2 font-weight-500 width-70per">
                                                        <?= htmlspecialchars($offer['location']) ?>
                                                    </label>

                                                    <label class="font-size-13px mt-3 color-876363">Training Duration</label>
                                                    <label class="font-size-12px mt-2 font-weight-500 width-70per">
                                                        <?= htmlspecialchars($offer['training_duration']) ?>
                                                    </label>

                                                    <label class="font-size-13px mt-3 color-876363">Type of training</label>
                                                    <label class="font-size-12px mt-2 font-weight-500 width-70per">
                                                        <?= htmlspecialchars($offer['training_type']) ?>
                                                    </label>

                                                    <label class="font-size-13px mt-3 color-876363">About the internship</label>
                                                    <label class="font-size-12px mt-2 font-weight-500 width-70per">
                                                        <?= nl2br(htmlspecialchars($offer['about_internship'])) ?>
                                                    </label>

                                                    <label class="font-size-13px mt-3 color-876363">Responsibilities</label>
                                                    <label class="font-size-12px mt-2 font-weight-500 width-70per">
                                                        <?= nl2br(htmlspecialchars($offer['responsibilities'])) ?>
                                                    </label>

                                                    <label class="font-size-13px mt-3 color-876363">Requirements</label>
                                                    <label class="font-size-12px mt-2 font-weight-500 width-70per">
                                                        <?= nl2br(htmlspecialchars($offer['requirements'])) ?>
                                                    </label>

                                                    <label class="font-size-13px mt-3 color-876363">Is there a salary?</label>
                                                    <label class="font-size-12px mt-2 font-weight-500 width-70per">
                                                        <?= $offer['salary'] ? 'Yes' : 'No' ?>
                                                    </label>

                                                    <label class="font-size-13px mt-3 color-876363">Application deadline</label>
                                                    <label class="font-size-12px mt-2 font-weight-500 width-70per">
                                                        <?= date('d/m/Y', strtotime($offer['application_deadline'])) ?>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                    <?php else: ?>
                        <p class="font-size-13px color-876363 mt-3 ms-4"><i class="bi bi-bell-slash me-1"></i>No in-process offers.</p>
                    <?php endif; ?>
                </div>

                <label for="" class="font-weight-600 font-size-14px margin-top-70px"><i class="bi bi-x-circle me-2"></i>Rejection Received</label>
                <div class="row">
                    <?php if($rejected_offers->num_rows > 0): ?>
                    <?php while($offer = $rejected_offers->fetch_assoc()): ?>
                        <div class="d-flex margin-top-30px col-md-6">
                            <img src="<?= (!empty($offer['image']) && file_exists('../' . $offer['image'])) ? '../' . $offer['image'] : '../img/default_company.png' ?>" 
                                alt="" class="width-15per me-4 border-radius-50per img-fit">

                            <div class="d-flex flex-column">
                                <label class="font-size-14px font-weight-600">
                                    <?= htmlspecialchars($offer['job_title']) ?>
                                </label>
                                <label class="font-size-13px color-876363">
                                    <?= htmlspecialchars($offer['company_name']) ?> - <?= htmlspecialchars($offer['location']) ?>
                                </label>

                                <button class="font-size-11px border-none bg-color-F5F0F0 border-radius-15px px-3 py-2 mt-2 width-fit"
                                        data-bs-toggle="offcanvas" 
                                        data-bs-target="#offcanvasOffer<?= $offer['offer_id'] ?>"
                                        aria-controls="offcanvasOffer<?= $offer['offer_id'] ?>">
                                    View Offer
                                </button>

                                <div class="offcanvas offcanvas-end width-600px" tabindex="-1" 
                                    id="offcanvasOffer<?= $offer['offer_id'] ?>" 
                                    aria-labelledby="offcanvasOfferLabel<?= $offer['offer_id'] ?>">
                                    <div class="offcanvas-header">
                                        <div class="d-flex px-4 align-items-center">
                                            <label class="font-weight-600 font-size-22px">Offer Details</label>
                                            <button class="font-size-11px border-none bg-color-FFCDD2 color-73000cff border-radius-15px px-3 py-1 ms-3">
                                                Rejected
                                            </button>
                                        </div>
                                    </div>
                                    <div class="offcanvas-body">
                                        <div class="d-flex flex-column px-4">
                                            <div class="d-flex">
                                                <img src="<?= (!empty($offer['image']) && file_exists('../' . $offer['image'])) ? '../' . $offer['image'] : '../img/default_company.png' ?>" 
                                                    alt="" class="width-12per border-radius-50per img-fit">
                                                <div class="d-flex flex-column ms-3">
                                                    <label class="font-size-18px mt-2 font-weight-500 color-876363">
                                                        <?= htmlspecialchars($offer['company_name']) ?>
                                                    </label>
                                                    <label class="font-size-14px mt-1 font-weight-500">
                                                        <?= htmlspecialchars($offer['job_title']) ?>
                                                    </label>
                                                </div>
                                            </div>

                                            <div class="d-flex flex-column mx-3">
                                                <label class="font-size-13px mt-3 color-876363">Location</label>
                                                <label class="font-size-12px mt-2 font-weight-500 width-70per">
                                                    <?= htmlspecialchars($offer['location']) ?>
                                                </label>

                                                <label class="font-size-13px mt-3 color-876363">Training Duration</label>
                                                <label class="font-size-12px mt-2 font-weight-500 width-70per">
                                                    <?= htmlspecialchars($offer['training_duration']) ?>
                                                </label>

                                                <label class="font-size-13px mt-3 color-876363">Type of training</label>
                                                <label class="font-size-12px mt-2 font-weight-500 width-70per">
                                                    <?= htmlspecialchars($offer['training_type']) ?>
                                                </label>

                                                <label class="font-size-13px mt-3 color-876363">About the internship</label>
                                                <label class="font-size-12px mt-2 font-weight-500 width-70per">
                                                    <?= nl2br(htmlspecialchars($offer['about_internship'])) ?>
                                                </label>

                                                <label class="font-size-13px mt-3 color-876363">Responsibilities</label>
                                                <label class="font-size-12px mt-2 font-weight-500 width-70per">
                                                    <?= nl2br(htmlspecialchars($offer['responsibilities'])) ?>
                                                </label>

                                                <label class="font-size-13px mt-3 color-876363">Requirements</label>
                                                <label class="font-size-12px mt-2 font-weight-500 width-70per">
                                                    <?= nl2br(htmlspecialchars($offer['requirements'])) ?>
                                                </label>

                                                <label class="font-size-13px mt-3 color-876363">Is there a salary?</label>
                                                <label class="font-size-12px mt-2 font-weight-500 width-70per">
                                                    <?= $offer['salary'] ? 'Yes' : 'No' ?>
                                                </label>

                                                <label class="font-size-13px mt-3 color-876363">Application deadline</label>
                                                <label class="font-size-12px mt-2 font-weight-500 width-70per">
                                                    <?= date('d/m/Y', strtotime($offer['application_deadline'])) ?>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p class="font-size-13px color-876363 mt-3 ms-4"><i class="bi bi-bell-slash me-1"></i>No rejected offers.</p>
                <?php endif; ?>
                </div>
              </div>
            </div>
        </div>
    </div>
</body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    // Decline btn
    $('.btn-decline').click(function() {
        let offerId = $(this).data('offer');
        $.post('update_offer_status.php', {offer_id: offerId, status: 'Rejected'}, function(response) {
            if (response && response.status === 'success') {
                alert('Offer declined successfully');
                location.reload(); 
            } else {
                console.error('Server response:', response);
                alert('Something went wrong');
            }
        }, 'json').fail(function(jqXHR, textStatus, errorThrown) {
            console.error('AJAX error:', textStatus, errorThrown, jqXHR.responseText);
            alert('Something went wrong (network)');
        });
    });

    // Continue the Process
    $('.btn-continue').click(function() {
        let offerId = $(this).data('offer');
        $.post('update_offer_status.php', {offer_id: offerId, status: 'Interview'}, function(response) {
            if (response && response.status === 'success') {
                //alert('Status updated to Interview');
                location.reload();
            } else {
                console.error('Server response:', response);
                alert('Something went wrong');
            }
        }, 'json').fail(function(jqXHR, textStatus, errorThrown) {
            console.error('AJAX error:', textStatus, errorThrown, jqXHR.responseText);
            alert('Something went wrong (network)');
        });
    });
});
</script>
</html>