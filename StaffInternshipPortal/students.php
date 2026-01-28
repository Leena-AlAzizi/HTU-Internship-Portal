<?php
session_start();
require '../db_connection.php';

if (!isset($_SESSION['staff_id']) || $_SESSION['user_type'] !== 'staff') {
    header("Location: ../login.php");
    exit();
}

$query = "
SELECT 
    s.student_id,
    CONCAT(s.first_name, ' ', s.last_name) AS student_name,
    s.academic_year,
    s.email,
    s.phone_number,
    s.place_of_residence,
    s.cv_file,
    s.profile_image,
    s.department_id,
    m.major_name,
    o.job_title AS offer_title
FROM students s
LEFT JOIN majors m ON s.major_id = m.major_id
LEFT JOIN departments d ON m.department_id = d.department_id
LEFT JOIN applications a ON a.student_id = s.student_id AND a.status = 'Accepted'
LEFT JOIN offers o ON o.offer_id = a.offer_id
WHERE s.status = 'Found Opportunity'
";

if (!empty($_GET['search'])) {
    $search = $_GET['search'];
    $query .= " AND (s.first_name LIKE '%$search%' OR s.last_name LIKE '%$search%')";
}

if (!empty($_GET['department'])) {
    $query .= " AND d.department_id = " . $_GET['department'];
}

if (!empty($_GET['major'])) {
    $query .= " AND m.major_id = " . $_GET['major'];
}

if (!empty($_GET['year'])) {
    $query .= " AND s.academic_year = '" . $_GET['year'] . "'";
}

$result = mysqli_query($conn, $query);

// Not Offered Students 

$not_offered_query = "
SELECT 
    s.student_id,
    CONCAT(s.first_name, ' ', s.last_name) AS student_name,
    s.academic_year,
    s.phone_number,
    s.email,
    s.place_of_residence,
    s.cv_file,
    s.profile_image,
    s.department_id,
    m.major_name
FROM students s
LEFT JOIN majors m ON s.major_id = m.major_id
LEFT JOIN departments d ON m.department_id = d.department_id
WHERE s.status = 'Not Found'
";

if (!empty($_GET['search'])) {
    $search = mysqli_real_escape_string($conn, $_GET['search']);
    $not_offered_query .= " AND (s.first_name LIKE '%$search%' OR s.last_name LIKE '%$search%')";
}

if (!empty($_GET['department'])) {
    $dep = mysqli_real_escape_string($conn, $_GET['department']);
    $not_offered_query .= " AND d.department_id = " . $dep;
}

if (!empty($_GET['major'])) {
    $maj = mysqli_real_escape_string($conn, $_GET['major']);
    $not_offered_query .= " AND m.major_id = " . $maj;
}

if (!empty($_GET['year'])) {
    $year = mysqli_real_escape_string($conn, $_GET['year']);
    $not_offered_query .= " AND s.academic_year = '" . $year . "'";
}

$not_offered_result = mysqli_query($conn, $not_offered_query);

$in_process_query = "
SELECT 
    s.student_id,
    CONCAT(s.first_name, ' ', s.last_name) AS student_name,
    s.academic_year,
    s.cv_file,
    s.phone_number,
    s.place_of_residence,
    s.profile_image,
    s.department_id,
    s.email,
    m.major_name,
    COUNT(a.application_id) AS offers_received
FROM students s
LEFT JOIN majors m ON s.major_id = m.major_id
LEFT JOIN departments d ON m.department_id = d.department_id
LEFT JOIN applications a 
    ON a.student_id = s.student_id 
    AND a.status IN ('Pending', 'Under Review', 'Interview', 'Sent')
WHERE s.status IN ('In Process', 'Interviewing')
";

// search
if (!empty($_GET['search'])) {
    $search = mysqli_real_escape_string($conn, $_GET['search']);
    $in_process_query .= " AND (s.first_name LIKE '%$search%' OR s.last_name LIKE '%$search%')";
}

// department
if (!empty($_GET['department'])) {
    $dep = mysqli_real_escape_string($conn, $_GET['department']);
    $in_process_query .= " AND d.department_id = $dep";
}

// major
if (!empty($_GET['major'])) {
    $maj = mysqli_real_escape_string($conn, $_GET['major']);
    $in_process_query .= " AND m.major_id = $maj";
}

// year
if (!empty($_GET['year'])) {
    $year = mysqli_real_escape_string($conn, $_GET['year']);
    $in_process_query .= " AND s.academic_year = '$year'";
}

$in_process_query .= " GROUP BY s.student_id ORDER BY s.first_name ASC";

$in_process_result = mysqli_query($conn, $in_process_query);

$active_tab = $_GET['tab'] ?? 'not_offered';

$current_filters = http_build_query(array_filter($_GET, fn($key) => $key !== 'tab', ARRAY_FILTER_USE_KEY));
$filter_separator = !empty($current_filters) ? '&' : '';

?>
<!DOCTYPE html>
<html lang="en" class="h-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Students</title>
    <link rel="icon" type="image/x-icon" href="../img\htu_logo.png">
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
            <!-- LEFT MENU -->
            <div class="left-side col-md-2 ps-1 h-100">
                <nav class="navbar navbar-expand-lg navbar-light flex-column h-100">
                    <a class="navbar-brand mx-0 mb-2" href="#">
                      <label class="ms-2 font-size-14px font-weight-500 color-876363">
                        <label class="font-weight-400 color-000">Hello, </label> <?= $_SESSION['username'] ?? "User" ?>
                      </label>
                    </a>
                    <ul class="navbar-nav flex-column justify-content-between h-100 w-100">
                      <div>
                        <li class="nav-item">
                          <a href="dashboard.php" class="btn w-100 text-start">
                            <i class="bi bi-house-door"></i>
                            <label class="ms-2">Dashboard</label>
                          </a>
                        </li>
                        <li class="nav-item">
                          <a href="students.php" class="btn w-100 text-start bg-color-F5F0F0">
                            <i class="bi bi-people-fill"></i>
                            <label class="ms-2">Students</label>
                          </a>
                        </li>
                        <li class="nav-item">
                          <a href="offers.php" class="btn w-100 text-start">
                            <i class="bi bi-briefcase"></i>
                            <label class="ms-2">Offers</label>
                          </a>
                        </li>
                        <li class="nav-item">
                          <a href="external_offer.php" class="btn w-100 text-start">
                            <i class="bi bi-person-plus"></i>
                            <label for="" class="ms-2">External Offers</label>
                          </a>
                        </li>
                        <li class="nav-item">
                          <a href="companies.php" class="btn w-100 text-start">
                            <i class="bi bi-buildings"></i>
                            <label class="ms-2">Companies</label>
                          </a>
                        </li>
                        <li class="nav-item">
                            <a href="staff_guide.php" class="btn w-100 text-start">
                                <i class="bi bi-info-circle"></i> 
                                <label class="ms-2">Portal Guide</label>
                            </a>
                        </li>
                      </div>
                        <li class="nav-item mb-5">
                          <a href="../logout.php" class="btn w-100 text-start font-weight-500 color-876363">
                            <i class="bi bi-box-arrow-right"></i>
                            <label class="ms-2">Logout</label>
                          </a>
                        </li>
                    </ul>
                </nav>
            </div>
            <div class="right-side col-md-10 p-4">
              <div class="d-flex flex-column">
                <div class="d-flex flex-column">
                    <label for="" class="font-weight-600 font-size-22px">Students</label>
                </div>
                        <div class="d-flex mt-3">
                            <form method="GET" class="search d-flex bg-color-F5F0F0 border-radius-9px w-100">
                                <button class="btn pe-0" type="submit">
                                    <i class="bi bi-search color-876363 font-size-13px"></i>
                                </button>
                                <input 
                                    class="form-control me-2 bg-color-F5F0F0 font-size-13px border-none shadow-none" 
                                    type="text" 
                                    name="search"
                                    placeholder="Search Student"
                                    value="<?= htmlspecialchars($_GET['search'] ?? '') ?>"
                                />
                                <input type="hidden" name="tab" value="<?= $active_tab ?>">
                                
                                <input type="hidden" name="department" value="<?= htmlspecialchars($_GET['department'] ?? '') ?>">
                                <input type="hidden" name="major" value="<?= htmlspecialchars($_GET['major'] ?? '') ?>">
                                <input type="hidden" name="year" value="<?= htmlspecialchars($_GET['year'] ?? '') ?>">
                            </form>
                            <div class="position-relative">

                                <!-- Filter Button -->
                                <button class="btn mx-2" type="button" id="filterBtn">
                                    <i class="bi bi-funnel-fill color-876363 font-size-19px"></i>
                                </button>

                                <!-- Filter Box (hidden by default) -->
                                <div id="filterBox" 
                                    class="p-3 shadow border bg-white position-absolute end-0 mt-2 border-radius-10px"
                                    style="width: 260px; display: none; z-index: 1000;">

                                    <form method="GET">

                                        <label class="font-size-13px mb-1">Department</label>
                                        <select name="department" id="departmentSelect" class="form-select font-size-13px mb-3">
                                            <option value="">All Departments</option>
                                            <?php
                                            $departments_query = "SELECT * FROM departments";
                                            $departments_result = mysqli_query($conn, $departments_query);
                                            while($dep = mysqli_fetch_assoc($departments_result)) { ?>
                                                <option value="<?= $dep['department_id'] ?>" <?= (isset($_GET['department']) && $_GET['department'] == $dep['department_id']) ? 'selected' : '' ?>>
                                                    <?= $dep['department_name'] ?>
                                                </option>
                                            <?php } ?>
                                        </select>

                                        <label class="font-size-13px mb-1">Major</label>
                                        <select name="major" id="majorSelect" class="form-select font-size-13px mb-3">
                                            <option value="">All Majors</option>
                                            <?php
                                            $majors_query = "SELECT * FROM majors";
                                            $majors_result = mysqli_query($conn, $majors_query);
                                            while($maj = mysqli_fetch_assoc($majors_result)) { ?>
                                                <option value="<?= $maj['major_id'] ?>" 
                                                        data-dept="<?= $maj['department_id'] ?>"
                                                        <?= (isset($_GET['major']) && $_GET['major'] == $maj['major_id']) ? 'selected' : '' ?>>
                                                    <?= $maj['major_name'] ?>
                                                </option>
                                            <?php } ?>
                                        </select>

                                        <label class="font-size-13px mb-1">Year</label>
                                        <select name="year" class="form-select font-size-13px mb-3">
                                            <option value="">All</option>
                                            <?php
                                            $years_q = mysqli_query($conn, "SELECT DISTINCT academic_year FROM students");
                                            while($yr = mysqli_fetch_assoc($years_q)) { ?>
                                                <option value="<?= $yr['academic_year'] ?>"
                                                    <?= (isset($_GET['year']) && $_GET['year'] == $yr['academic_year']) ? 'selected' : '' ?>>
                                                    <?= $yr['academic_year'] ?>
                                                </option>
                                            <?php } ?>
                                        </select>
                                        <input type="hidden" name="tab" value="<?= $active_tab ?>">
                                        <button class="btn btn-sm bg-color-F5F0F0 w-100 font-size-13px">Apply Filters</button>

                                    </form>

                                </div>
                            </div>

                        </div>
                <ul class="nav nav-pills mb-3 mt-4 border-bottom-color-876363" id="pills-tab" role="tablist">
                    <li class="nav-item">
                        <a href="?tab=not_offered<?= $filter_separator . $current_filters ?>" 
                        class="nav-link <?= ($active_tab == 'not_offered') ? 'active' : '' ?>">
                        Not Offered Students
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="?tab=in_process<?= $filter_separator . $current_filters ?>" 
                        class="nav-link <?= ($active_tab == 'in_process') ? 'active' : '' ?>">
                        In Process Students
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="?tab=offered<?= $filter_separator . $current_filters ?>" 
                        class="nav-link <?= ($active_tab == 'offered') ? 'active' : '' ?>">
                        Offered Students
                        </a>
                    </li>
                </ul>
                <div class="tab-content" id="pills-tabContent">
                    <div class="tab-pane fade <?= ($active_tab == 'offered') ? 'show active' : '' ?>" id="pills-home">
                        
                        <table class="table mb-0 font-size-12px border-radius-top-10px mt-2">
                            <thead class="bg-color-F5F0F0">
                                <tr>
                                    <th class="width-25per">
                                        <div class="px-3">Name</div>
                                    </th>
                                    <th class="width-20per">                                            
                                        <div class="px-3">Major</div>
                                    </th>
                                    <th class="width-15per">                                            
                                        <div class="px-3">Year</div>
                                    </th>
                                    <th class="width-25per">
                                        <div class="px-3">Offer Title</div>
                                    </th>
                                    <th class="width-15per">                                            
                                        <div class="px-3">Actions</div>
                                    </th>
                                </tr>
                            </thead>
                        </table>
                        <div class="scroll-y-axis max-h-450px">
                            <table class="table tablebody font-size-13px col-5-color-876363">
                                <tbody>
                                    <?php

                                    while ($row = mysqli_fetch_assoc($result)) {
                                    ?>
                                        <tr>
                                            <td class="width-25per">
                                                <div class="px-3"><?= $row['student_name'] ?></div>
                                            </td>
                                            <td class="width-20per">
                                                <div class="px-3"><?= $row['major_name'] ?></div>
                                            </td>
                                            <td class="width-15per">
                                                <div class="px-3"><?= $row['academic_year'] ?></div>
                                            </td>
                                            <td class="width-25per">
                                                <div class="px-3"><?= $row['offer_title'] ?? '—' ?></div>
                                            </td>
                                            <td class="width-15per">
                                                <div class="px-3">
                                                    <button type="button" 
                                                            class="btn btn-sm color-876363" 
                                                            title="View"
                                                            data-bs-toggle="offcanvas" 
                                                            data-bs-target="#offcanvas_<?= $row['student_id'] ?>">
                                                        <i class="bi bi-eye"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        <div class="offcanvas offcanvas-end width-650px" tabindex="-1" id="offcanvas_<?= $row['student_id'] ?>">
                                            <div class="offcanvas-body pt-0">
                                                <div class="row">
                                                    <?php $cover_image = "../img/eng.jpg";

                                                    if (isset($row['department_id']) && $row['department_id'] == 2) {
                                                        $cover_image = "../img/it1.jpg"; 
                                                    } else if (isset($row['department_id']) && $row['department_id'] == 3) {
                                                        $cover_image = "../img/eng2.jpg"; 
                                                    }
                                                    ?>

                                                    <img src="<?= $cover_image ?>" alt="" class="w-100 height-175px px-0 img-cover opacity-half">
                                                    <div class="d-flex justify-content-center flex-column align-items-center">
                                                        <img src="<?= $row['profile_image'] ?>" class="px-0 border-radius-50per width-130px height-130px img-cover margin-top--60px index-one border-color-fff">
                                                        <label for="" class="mt-2 font-weight-600 font-size-25px"><?= $row['student_name'] ?></label>
                                                        <label for="" class="mt-1 font-size-12px color-5f5f5f"><?= $row['major_name'] ?></label>
                                                    </div>
                                                </div>
                                                <div class="row mx-2 px-3 pt-2 pb-4 mt-4 bg-color-f4f4f4 border-radius-10px">
                                                    <div class="d-flex flex-column ">
                                                        <div class="row">
                                                            <div class="col-md-6 d-flex flex-column mt-4">
                                                                <label for="" class="font-size-12px color-5f5f5f">Academic Year </label>
                                                                <label for="" class="mt-1 font-size-14px"><?= $row['academic_year'] ?></label>
                                                            </div>
                                                            <div class="col-md-6 d-flex flex-column mt-4">
                                                                <label for="" class="font-size-12px color-5f5f5f">Email</label>
                                                                <label for="" class="mt-1 font-size-14px"><?= $row['email'] ?></label>
                                                            </div>
                                                            <div class="col-md-6 d-flex flex-column mt-4">
                                                                <label for="" class="font-size-12px color-5f5f5f">Phone Number</label>
                                                                <label for="" class="mt-1 font-size-14px"><?= $row['phone_number'] ?></label>
                                                            </div>
                                                            <div class="col-md-6 d-flex flex-column mt-4">
                                                                <label for="" class="font-size-12px color-5f5f5f">Place of Residence</label>
                                                                <label for="" class="mt-1 font-size-14px"><?= $row['place_of_residence'] ?></label>
                                                            </div>
                                                            <div class="col-md-12 d-flex flex-column mt-4">
                                                                <?php
                                                                if (!empty($row['cv_file'])) {
                                                                ?>
                                                                    <a href="<?= $row['cv_file'] ?>" target="_blank" class="btn font-size-12px bg-color-e8343f color-fff border-radius-9px px-3 py-1 width-fit">
                                                                        <i class="bi bi-file-earmark-person me-1"></i> View Student CV
                                                                    </a>
                                                                <?php
                                                                } else {
                                                                // echo '<label class="font-size-12px color-5f5f5f">CV is not available</label>';
                                                                }
                                                                ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="tab-pane fade <?= ($active_tab == 'not_offered') ? 'show active' : '' ?>" id="pills-profile">
                        <table class="table mb-0 font-size-12px border-radius-top-10px mt-2 tablehead">
                            <thead class="bg-color-F5F0F0">
                                <tr>
                                    <th class="width-30per">
                                        <div class="px-3">Name</div>
                                    </th>
                                    <th class="width-25per">
                                        <div class="px-3">Major</div>
                                    </th>
                                    <th class="width-25per">
                                        <div class="px-3">Year</div>
                                    </th>
                                    <th class="width-20per">
                                        <div class="px-3">Actions</div>
                                    </th>
                                </tr>
                            </thead>
                        </table>
                        <div class="scroll-y-axis max-h-450px">
                            <table class="table tablebody font-size-13px col-5-color-876363">
                                <tbody>
                                    <?php
                                    if ($not_offered_result && mysqli_num_rows($not_offered_result) > 0) {
                                        while ($row = mysqli_fetch_assoc($not_offered_result)) {
                                    ?>
                                            <tr>
                                                <td class="width-30per">
                                                    <div class="px-3"><?= $row['student_name'] ?></div>
                                                </td>
                                                <td class="width-25per">
                                                    <div class="px-3"><?= $row['major_name'] ?></div>
                                                </td>
                                                <td class="width-25per">
                                                    <div class="px-3"><?= $row['academic_year'] ?></div>
                                                </td>
                                                <td class="width-20per">
                                                    <div class="px-3">
                                                        <button type="button" 
                                                            class="btn btn-sm color-876363" 
                                                            title="View"
                                                            data-bs-toggle="offcanvas" 
                                                            data-bs-target="#offcanvas_<?= $row['student_id'] ?>">
                                                            <i class="bi bi-eye"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                            <div class="offcanvas offcanvas-end width-650px" tabindex="-1" id="offcanvas_<?= $row['student_id'] ?>">
                                                <div class="offcanvas-body pt-0">
                                                    <div class="row">
                                                        <?php $cover_image = "../img/eng.jpg";

                                                        if (isset($row['department_id']) && $row['department_id'] == 2) {
                                                            $cover_image = "../img/it1.jpg"; 
                                                        } else if (isset($row['department_id']) && $row['department_id'] == 3) {
                                                            $cover_image = "../img/eng2.jpg"; 
                                                        }
                                                        ?>

                                                        <img src="<?= $cover_image ?>" alt="" class="w-100 height-175px px-0 img-cover opacity-half">
                                                        <div class="d-flex justify-content-center flex-column align-items-center">
                                                            <img src="<?= $row['profile_image'] ?>" class="px-0 border-radius-50per width-130px height-130px img-cover margin-top--60px index-one border-color-fff">
                                                            <label for="" class="mt-2 font-weight-600 font-size-25px"><?= $row['student_name'] ?></label>
                                                            <label for="" class="mt-1 font-size-12px color-5f5f5f"><?= $row['major_name'] ?></label>
                                                        </div>
                                                    </div>
                                                    <div class="row mx-2 px-3 pt-2 pb-4 mt-4 bg-color-f4f4f4 border-radius-10px">
                                                        <div class="d-flex flex-column ">
                                                            <div class="row">
                                                                <div class="col-md-6 d-flex flex-column mt-4">
                                                                    <label for="" class="font-size-12px color-5f5f5f">Academic Year </label>
                                                                    <label for="" class="mt-1 font-size-14px"><?= $row['academic_year'] ?></label>
                                                                </div>
                                                                <div class="col-md-6 d-flex flex-column mt-4">
                                                                    <label for="" class="font-size-12px color-5f5f5f">Email</label>
                                                                    <label for="" class="mt-1 font-size-14px"><?= $row['email'] ?></label>
                                                                </div>
                                                                <div class="col-md-6 d-flex flex-column mt-4">
                                                                    <label for="" class="font-size-12px color-5f5f5f">Phone Number</label>
                                                                    <label for="" class="mt-1 font-size-14px"><?= $row['phone_number'] ?></label>
                                                                </div>
                                                                <div class="col-md-6 d-flex flex-column mt-4">
                                                                    <label for="" class="font-size-12px color-5f5f5f">Place of Residence</label>
                                                                    <label for="" class="mt-1 font-size-14px"><?= $row['place_of_residence'] ?></label>
                                                                </div>
                                                                <div class="col-md-12 d-flex flex-column mt-4">
                                                                    <?php
                                                                    if (!empty($row['cv_file'])) {
                                                                    ?>
                                                                        <a href="<?= $row['cv_file'] ?>" target="_blank" class="btn font-size-12px bg-color-e8343f color-fff border-radius-9px px-3 py-1 width-fit">
                                                                            <i class="bi bi-file-earmark-person me-1"></i> View Student CV
                                                                        </a>
                                                                    <?php
                                                                    } else {
                                                                    // echo '<label class="font-size-12px color-5f5f5f">CV is not available</label>';
                                                                    }
                                                                    ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                    <?php
                                        }
                                    } else {
                                        echo '<tr><td colspan="4" class="text-center py-4">No students found with status "Not Offered".</td></tr>';
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="tab-pane fade <?= ($active_tab == 'in_process') ? 'show active' : '' ?>" id="pills-process">
                        <table class="table mb-0 font-size-12px border-radius-top-10px mt-2 tablehead">
                            <thead class="bg-color-F5F0F0">
                                <tr>
                                    <th class="width-25per">
                                        <div class="px-3">Name</div>
                                    </th>
                                    <th class="width-25per">                                            
                                        <div class="px-3">Major</div>
                                    </th>
                                    <th class="width-15per">                                            
                                        <div class="px-3">Year</div>
                                    </th>
                                    <th class="width-20per">                                            
                                        <div class="px-3">Offers Received</div>
                                    </th>
                                    <th class="width-15per">                                            
                                        <div class="px-3">Actions</div>
                                    </th>
                                </tr>
                            </thead>
                        </table>
                        <div class="scroll-y-axis max-h-450px">
                            <table class="table tablebody font-size-13px col-5-color-876363">
                                <tbody>
                                <?php
                                if ($in_process_result && mysqli_num_rows($in_process_result) > 0) {
                                    while ($row = mysqli_fetch_assoc($in_process_result)) {
                                ?>
                                        <tr>
                                            <td class="width-25per">
                                                <div class="px-3"><?= $row['student_name'] ?></div>
                                            </td>
                                            <td class="width-25per">
                                                <div class="px-3"><?= $row['major_name'] ?></div>
                                            </td>
                                            <td class="width-15per">
                                                <div class="px-3"><?= $row['academic_year'] ?></div>
                                            </td>
                                            <td class="width-20per">
                                                <div class="px-3"><?= $row['offers_received'] ?></div>
                                            </td>
                                            <td class="width-15per">
                                                <div class="px-3">
                                                    <button type="button" 
                                                            class="btn btn-sm color-876363" 
                                                            title="View"
                                                            data-bs-toggle="offcanvas" 
                                                            data-bs-target="#offcanvas_off_<?= $row['student_id'] ?>"> <i class="bi bi-eye"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                            <div class="offcanvas offcanvas-end width-650px" tabindex="-1" id="offcanvas_off_<?= $row['student_id'] ?>">
                                                <div class="offcanvas-body pt-0">
                                                    <div class="row">
                                                        <?php $cover_image = "../img/eng.jpg";

                                                        if (isset($row['department_id']) && $row['department_id'] == 2) {
                                                            $cover_image = "../img/it1.jpg"; 
                                                        } else if (isset($row['department_id']) && $row['department_id'] == 3) {
                                                            $cover_image = "../img/eng2.jpg"; 
                                                        }
                                                        ?>

                                                        <img src="<?= $cover_image ?>" alt="" class="w-100 height-175px px-0 img-cover opacity-half">
                                                        <div class="d-flex justify-content-center flex-column align-items-center">
                                                            <img src="<?= $row['profile_image'] ?>" class="px-0 border-radius-50per width-130px height-130px img-cover margin-top--60px index-one border-color-fff">
                                                            <label for="" class="mt-2 font-weight-600 font-size-25px"><?= $row['student_name'] ?></label>
                                                            <label for="" class="mt-1 font-size-12px color-5f5f5f"><?= $row['major_name'] ?></label>
                                                        </div>
                                                    </div>
                                                    <div class="row mx-2 px-3 pt-2 pb-4 mt-4 bg-color-f4f4f4 border-radius-10px">
                                                        <div class="d-flex flex-column ">
                                                            <div class="row">
                                                                <div class="col-md-6 d-flex flex-column mt-4">
                                                                    <label for="" class="font-size-12px color-5f5f5f">Academic Year </label>
                                                                    <label for="" class="mt-1 font-size-14px"><?= $row['academic_year'] ?></label>
                                                                </div>
                                                                <div class="col-md-6 d-flex flex-column mt-4">
                                                                    <label for="" class="font-size-12px color-5f5f5f">Email</label>
                                                                    <label for="" class="mt-1 font-size-14px"><?= $row['email'] ?></label>
                                                                </div>
                                                                <div class="col-md-6 d-flex flex-column mt-4">
                                                                    <label for="" class="font-size-12px color-5f5f5f">Phone Number</label>
                                                                    <label for="" class="mt-1 font-size-14px"><?= $row['phone_number'] ?></label>
                                                                </div>
                                                                <div class="col-md-6 d-flex flex-column mt-4">
                                                                    <label for="" class="font-size-12px color-5f5f5f">Place of Residence</label>
                                                                    <label for="" class="mt-1 font-size-14px"><?= $row['place_of_residence'] ?></label>
                                                                </div>
                                                                <div class="col-md-12 d-flex flex-column mt-4">
                                                                    <?php
                                                                    if (!empty($row['cv_file'])) {
                                                                    ?>
                                                                        <a href="<?= $row['cv_file'] ?>" target="_blank" class="btn font-size-12px bg-color-e8343f color-fff border-radius-9px px-3 py-1 width-fit">
                                                                            <i class="bi bi-file-earmark-person me-1"></i> View Student CV
                                                                        </a>
                                                                    <?php
                                                                    } else {
                                                                    // echo '<label class="font-size-12px color-5f5f5f">CV is not available</label>';
                                                                    }
                                                                    ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                <?php 
                                    }
                                } else {
                                    echo '<tr><td colspan="5" class="text-center py-4">No In Process students found.</td></tr>';
                                }
                                ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
              </div>
            </div>
        </div>
    </div>
</body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
<script>
    const btn = document.getElementById('filterBtn');
    const box = document.getElementById('filterBox');

    btn.addEventListener('click', () => {
        box.style.display = box.style.display === 'none' ? 'block' : 'none';
    });

    // Close filter box if user clicks outside
    document.addEventListener('click', function(e) {
        if (!btn.contains(e.target) && !box.contains(e.target)) {
            box.style.display = 'none';
        }
    });
</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const deptSelect = document.getElementById('departmentSelect');
    const majorSelect = document.getElementById('majorSelect');
    const allMajors = Array.from(majorSelect.options);

    function filterMajors() {
        const selectedDept = deptSelect.value;
        
        majorSelect.innerHTML = '';
        
        majorSelect.appendChild(allMajors[0]);

        allMajors.forEach((option, index) => {
            if (index === 0) return; 

            if (selectedDept === "" || option.getAttribute('data-dept') === selectedDept) {
                majorSelect.appendChild(option);
            }
        });
    }

    deptSelect.addEventListener('change', filterMajors);
    
    if(deptSelect.value !== "") {
        filterMajors();
    }
});
</script>
</html>