<?php
session_start();
require '../db_connection.php'; 

// Security Check: Ensure user is logged in AND is a student
if (!isset($_SESSION['student_id']) || $_SESSION['user_type'] !== 'student') {
    header('Location: ../login.php'); 
    exit();
}

$student_id = $_SESSION['student_id'];

//Phase 1: Fetch Student Profile Information

$stmt = $conn->prepare("SELECT first_name, last_name, profile_image FROM students WHERE student_id = ?");
$stmt->bind_param("s", $student_id);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();
$stmt->close();

// Centralized Assets Paths
$upload_path = "../uploads/";
$img_path = "../img/";

// Logic to handle profile image path
$profile_img_src = (!empty($student['profile_image']) && file_exists("../" . $student['profile_image'])) 
                   ? "../" . $student['profile_image'] 
                   : $img_path . "default.png";

//Phase 2: Fetch Global Semester Settings (Deadline)
 
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

// Phase 3: Fetch Student's Internship Applications

$query = "
    SELECT 
        o.job_title,
        o.training_type,
        o.location,
        c.company_name,
        a.status,
        a.application_date
    FROM applications a
    JOIN offers o ON a.offer_id = o.offer_id
    JOIN companies c ON o.company_id = c.company_id
    WHERE a.student_id = ?
    ORDER BY a.application_date DESC
";

$stmt = $conn->prepare($query);
$stmt->bind_param("s", $student_id);
$stmt->execute();
$applications = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en" class="h-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
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
                                <a href="dashboard.php" class="btn bg-color-F5F0F0 w-100 text-start">
                                    <i class="bi bi-house-door-fill"></i>
                                    <label for="" class="ms-2">Dashboard</label>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="offers.php" class="btn w-100 text-start">
                                    <i class="bi bi-file-text"></i>
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
                <label for="" class="font-weight-600 font-size-22px">Dashboard</label>
                <?php if ($student_deadline): 
                        $deadline_formatted = date('F jS, Y', strtotime($student_deadline));
                    ?>
                        <div class="bg-color-F5F0F0  mt-3 mb-0 px-4 py-4 font-size-13px border-radius-8px" role="alert">
                            <i class="bi bi-clock-history me-1"></i>
                            <label for="">The deadline for finding an opportunity this semester : </label>
                            <strong class="color-B80000"><?= htmlspecialchars($deadline_formatted) ?></strong>
                        </div>
                    <?php endif; ?>
                <label for="" class="font-weight-600 font-size-14px mt-3">Potential Offers Status</label>
                <table class="table mb-0 font-size-12px border-radius-top-10px mt-3">
                    <thead>
                        <tr>
                            <th class="width-25per ">
                                <div class="px-3">Company</div>
                            </th>
                            <th class="width-25per">
                                <div class="px-3">Role</div>
                            </th>
                            <th class="width-25per">
                                <div class="px-3">Status</div>
                            </th>
                            <th class="width-25per">                                            
                                <div class="px-3">Date Applied</div>
                            </th>
                        </tr>
                    </thead>
                </table>
                <div class="scroll-y-axis max-h-450px">
                    <table class="table tablebody font-size-13px">
                        <tbody>
                            <?php if ($applications->num_rows > 0): ?>
                                <?php while ($row = $applications->fetch_assoc()): ?>
                                    <?php
                                        $status = $row['status'];
                                        $bgColor = '#F5F0F0';
                                        $color = '#000'; 

                                        switch ($status) {
                                            case 'Pending':
                                                $bgColor = '#fef2cfff';
                                                $color = '#c19103ff';
                                                break;
                                            case 'Interview':
                                                $bgColor = '#d6f2ffff';
                                                $color = '#02577eff';
                                                break;
                                            case 'Accepted':
                                                $bgColor = '#C8E6C9';
                                                $color = '#006b04ff';
                                                break;
                                            case 'Rejected':
                                                $bgColor = '#FFCDD2';
                                                $color = '#73000cff';
                                                break;
                                        }
                                    ?>
                                    <tr>
                                        <td class="width-25per">
                                            <div class="px-3"><?= htmlspecialchars($row['company_name']) ?></div>
                                        </td>
                                        <td class="width-25per">
                                            <div class="px-3"><?= htmlspecialchars($row['job_title']) ?></div>
                                        </td>
                                        <td class="width-25per">
                                            <div class="px-3 py-1 d-flex justify-content-center width-fit border-radius-10px font-weight-600"
                                                style="background-color: <?= $bgColor ?>; color:<?= $color ?>;">
                                                <?= htmlspecialchars($status) ?>
                                            </div>
                                        </td>
                                        <td class="width-25per">
                                            <div class="px-3">
                                                <?= date("M d, Y", strtotime($row['application_date'])) ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center py-3">No applications yet</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
              </div>
            </div>
        </div>
    </div>
</body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
</html>