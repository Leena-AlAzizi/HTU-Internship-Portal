<?php
session_start();
require '../db_connection.php'; 

if (!isset($_SESSION['company_id']) || $_SESSION['user_type'] !== 'company') {
    header("Location: ../login.php");
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

if (isset($_POST['add_offer'])) {
    $job_title = $_POST['job_title'];
    $location = $_POST['location'];
    $training_duration = $_POST['training_duration'];
    $training_type = $_POST['training_type'];
    $salary = $_POST['salary'];
    $application_deadline = $_POST['application_deadline'];
    $about_internship = $_POST['about_internship'];
    $responsibilities = $_POST['responsibilities'];
    $requirements = $_POST['requirements'];
    $max_applicants = $_POST['max_applicants'];
    $status = 'Pending'; 

    $stmt = $conn->prepare("
        INSERT INTO offers 
        (company_id, job_title, location, training_duration, training_type, salary, application_deadline, about_internship, responsibilities, requirements, status, max_applicants) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->bind_param("issssisssssi", 
        $company_id, 
        $job_title, 
        $location, 
        $training_duration, 
        $training_type, 
        $salary, 
        $application_deadline, 
        $about_internship, 
        $responsibilities, 
        $requirements, 
        $status,
        $max_applicants
    );

    if ($stmt->execute()) {
        echo "<script>alert('Offer added successfully!'); window.location.href='offers.php';</script>";
    } else {
        echo "<script>alert('Error adding offer. Please try again.');</script>";
    }
    $stmt->close();
}

$search_term = $_GET['search'] ?? '';
$status_filter = $_GET['status_filter'] ?? '';
$training_type_filter = $_GET['training_type_filter'] ?? '';

$where_conditions = ["o.company_id = ?"];
$bind_types = "i";
$bind_params = [$company_id];

if (!empty($search_term)) {
    $where_conditions[] = "o.job_title LIKE ?";
    $bind_types .= "s";
    $bind_params[] = "%" . $search_term . "%";
}

if (!empty($status_filter)) {
    $where_conditions[] = "o.status = ?";
    $bind_types .= "s";
    $bind_params[] = $status_filter;
}

if (!empty($training_type_filter)) {
    $where_conditions[] = "o.training_type = ?";
    $bind_types .= "s";
    $bind_params[] = $training_type_filter;
}

$where_clause = "WHERE " . implode(" AND ", $where_conditions);

$base_query = "
    SELECT o.*, 
           (SELECT COUNT(*) FROM applications a WHERE a.offer_id = o.offer_id) AS total_students
    FROM offers o
    {$where_clause}
    ORDER BY o.created_at DESC
";

$stmt = $conn->prepare($base_query);

$stmt->bind_param($bind_types, ...$bind_params);
$stmt->execute();
$offers_result = $stmt->get_result();

$current_search = htmlspecialchars($search_term);
$current_status_filter = htmlspecialchars($status_filter);
$current_type_filter = htmlspecialchars($training_type_filter);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Training Opportunities</title>
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
                    <a class="navbar-brand mx-0 mb-2 d-flex align-items-center" href="#">
                      <img src="<?php echo htmlspecialchars($company_image); ?>" 
                          class="width-50px height-50px border-radius-50per" 
                          alt="Company Logo" id="left-nav-logo">
                      <label for="" class="ms-2 font-size-14px font-weight-500">
                          <?php echo htmlspecialchars($company_name); ?>
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
                                    <i class="bi  bi-briefcase-fill"></i>
                                    <label for="" class="ms-2">Training Opportunities</label>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="students.php" class="btn w-100 text-start">
                                    <i class="bi bi-people"></i>
                                    <label for="" class="ms-2">Students</label>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="profile.php" class="btn w-100 text-start">
                                    <i class="bi bi-person"></i>
                                    <label for="" class="ms-2">Profile</label>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="company_guide.php" class="btn w-100 text-start">
                                    <i class="bi bi-info-circle"></i> 
                                    <label for="" class="ms-2">Portal Guide</label>
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
                    </div>
                  </nav>
            </div>
            <div class="right-side col-md-10 p-4">
              <div class="d-flex flex-column">
                <div class="d-flex justify-content-between">
                  <label for="" class="font-weight-600 font-size-22px">Training Opportunities</label>
                  <button type="button" class="btn font-size-12px btn-E51A1A" data-bs-toggle="offcanvas" data-bs-target="#offcanvasRight" aria-controls="offcanvasRight"><i class="bi bi-plus me-2"></i>Add New Training Opportunity</button>
                  <div class="offcanvas offcanvas-end width-600px" tabindex="-1" id="offcanvasRight" aria-labelledby="offcanvasRightLabel">
                    <div class="offcanvas-header">
                        <div class="d-flex flex-column px-4">
                        <label class="font-weight-600 font-size-22px">New Training Opportunity</label>
                        </div>
                    </div>

                    <div class="offcanvas-body pt-0">
                        <form method="POST" action="">
                            <div class="px-4">
                                
                                <div class="row mt-3">
                                    <div class="col-md-7 mb-3">
                                        <label class="form-label font-size-13px color-876363 font-weight-600">Job Title</label>
                                        <input type="text" name="job_title" class="form-control font-size-13px" placeholder="e.g. Graphic Designer" required>
                                    </div>
                                    <div class="col-md-5 mb-3">
                                        <label class="form-label font-size-13px color-876363 font-weight-600">Training Type</label>
                                        <select name="training_type" class="form-select font-size-13px" required>
                                            <option value="Full-time">Full-time</option>
                                            <option value="Part-time">Part-time</option>
                                            <option value="Remote">Remote</option>
                                            <option value="Hybrid">Hybrid</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-7 mb-3">
                                        <label class="form-label font-size-13px color-876363 font-weight-600">Location</label>
                                        <input type="text" name="location" class="form-control font-size-13px" placeholder="City, Area" required>
                                    </div>
                                    <div class="col-md-5 mb-3">
                                        <label class="form-label font-size-13px color-876363 font-weight-600">Duration</label>
                                        <input type="text" name="training_duration" class="form-control font-size-13px" placeholder="e.g. 3 Months" required>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label font-size-13px color-876363 font-weight-600">Salary?</label>
                                        <select name="salary" class="form-select font-size-13px" required>
                                            <option value="0">No (Unpaid)</option>
                                            <option value="1">Yes (Paid)</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label font-size-13px color-876363 font-weight-600">Max Applicants</label>
                                        <input type="number" name="max_applicants" class="form-control font-size-13px" placeholder="0" required>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label font-size-13px color-876363 font-weight-600">Deadline</label>
                                        <input type="date" name="application_deadline" class="form-control font-size-13px" required>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label font-size-13px color-876363 font-weight-600">About the Internship</label>
                                    <textarea name="about_internship" class="form-control font-size-13px" rows="3" placeholder="Briefly describe the internship program..." required></textarea>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label font-size-13px color-876363 font-weight-600">Responsibilities</label>
                                    <textarea name="responsibilities" class="form-control font-size-13px" rows="3" placeholder="List daily tasks and responsibilities..." required></textarea>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label font-size-13px color-876363 font-weight-600">Required Skills / Requirements</label>
                                    <textarea name="requirements" class="form-control font-size-13px" rows="3" placeholder="Soft skills, tools, or academic background..." required></textarea>
                                </div>

                                <div class="mt-4  d-flex justify-content-end pb-2">
                                    <button type="button" class="btn px-4 me-2 btn-E5E8EB font-size-12px" data-bs-dismiss="offcanvas">Cancel</button>
                                    <button type="submit" name="add_offer" class="btn px-4 btn-E51A1A font-size-12px text-white shadow-sm">Publish Training Opportunity</button>
                                </div>

                            </div>
                        </form>
                    </div>
                  </div>
                </div>
                <div class="d-flex mt-3">
                        <form method="GET" class="search d-flex bg-color-f4f4f4 border-radius-9px w-100">
                            <button class="btn pe-0" type="submit">
                                <i class="bi bi-search color-876363 font-size-13px"></i>
                            </button>
                            <input 
                                class="form-control me-2 bg-color-f4f4f4 font-size-13px border-none" 
                                type="text" 
                                name="search"
                                placeholder="Search Training Opportunity Title"
                                value="<?= $current_search ?>"
                            />
                            <input type="hidden" name="status_filter" value="<?= $current_status_filter ?>">
                            <input type="hidden" name="training_type_filter" value="<?= $current_type_filter ?>">
                        </form>
                        
                        <div class="position-relative">
                            <button class="btn mx-2" type="button" id="filterBtn">
                                <i class="bi bi-funnel-fill color-876363 font-size-19px"></i>
                            </button>

                            <div id="filterBox" 
                                class="p-3 shadow border bg-white position-absolute end-0 mt-2 border-radius-10px"
                                style="width: 260px; display: none; z-index: 1000;">

                                <form method="GET">
                                    <label class="font-size-13px mb-1">Status</label>
                                    <select name="status_filter" class="form-select font-size-13px mb-3">
                                        <option value="">All Statuses</option>
                                        <option value="Open" <?= ($current_status_filter == 'Open') ? 'selected' : '' ?>>Open</option>
                                        <option value="Pending" <?= ($current_status_filter == 'Pending') ? 'selected' : '' ?>>Pending</option>
                                        <option value="Expired" <?= ($current_status_filter == 'Expired') ? 'selected' : '' ?>>Expired</option>
                                    </select>
                                    
                                    <label class="font-size-13px mb-1">Training Type</label>
                                    <select name="training_type_filter" class="form-select font-size-13px mb-3">
                                        <option value="">All Types</option>
                                        <option value="Full-time" <?= ($current_type_filter == 'Full-time') ? 'selected' : '' ?>>Full-time</option>
                                        <option value="Part-time" <?= ($current_type_filter == 'Part-time') ? 'selected' : '' ?>>Part-time</option>
                                        <option value="Remote" <?= ($current_type_filter == 'Remote') ? 'selected' : '' ?>>Remote</option>
                                        <option value="Hybrid" <?= ($current_type_filter == 'Hybrid') ? 'selected' : '' ?>>Hybrid</option>
                                    </select>
                                    
                                    <input type="hidden" name="search" value="<?= $current_search ?>">
                                    <button class="btn btn-sm bg-color-F5F0F0 w-100 font-size-13px">Apply Filters</button>
                                </form>

                            </div>
                        </div>
                    </div>
                <table class="table mb-0 font-size-12px border-radius-top-10px mt-4 ">
                    <thead class="bg-color-F5F0F0">
                        <tr>
                            <th class="width-25per">
                                <div class="px-3">Title</div>
                            </th>
                            <th class="width-20per">
                                <div class="px-3">Location</div>
                            </th>
                            <th class="width-15per">
                                <div class="px-3">Students in Process</div>
                            </th>
                            <th class="width-20per">                                            
                                <div class="px-3">Status</div>
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
                            if ($offers_result && $offers_result->num_rows > 0):
                            while ($offer = $offers_result->fetch_assoc()): ?>
                            <?php
                                $status = $offer['status'];
                                                                $bgColor = '#F5F0F0';
                                                                $color = '#000'; 

                                                                switch ($status) {
                                                                    case 'Open':
                                                                        $bgColor = '#C8E6C9';   
                                                                        $color = '#006b04ff';   
                                                                        break;
                                                                    case 'Pending':
                                                                        $bgColor = '#fef2cfff'; 
                                                                        $color = '#c19103ff';   
                                                                        break;
                                                                    case 'Expired':
                                                                        $bgColor = '#FFCDD2';  
                                                                        $color = '#73000cff';  
                                                                        break;
                                                                }
                            ?>
                            <tr>
                                <td class="width-25per">
                                    <div class="px-3"><?= htmlspecialchars($offer['job_title']); ?></div>
                                </td>
                                <td class="width-20per">
                                    <div class="px-3"><?= htmlspecialchars($offer['location']); ?></div>
                                </td>
                                <td class="width-15per">
                                    <div class="px-3"><?= $offer['total_students']; ?></div>
                                </td>
                                <td class="width-20per">
                                    <div class="px-3 py-1 d-flex justify-content-center width-fit border-radius-12px font-weight-500 " style="background-color: <?= $bgColor ?>; color: <?= $color ?>;">
                                        <?= htmlspecialchars($offer['status']); ?>
                                    </div>
                                </td>
                                <td class="width-15per">
                                    <div class="px-3">
                                        <button type="button" class="btn btn-sm color-876363" title="View" data-bs-toggle="offcanvas" data-bs-target="#viewOffer<?= $offer['offer_id']; ?>">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm color-876363" title="Edit" data-bs-toggle="offcanvas" data-bs-target="#editOffer<?= $offer['offer_id']; ?>">
                                            <i class="bi bi-pen"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <!-- View Offer Offcanvas -->
                            <div class="offcanvas offcanvas-end width-600px" tabindex="-1" id="viewOffer<?= $offer['offer_id']; ?>" aria-labelledby="viewOfferLabel<?= $offer['offer_id']; ?>">
                                <div class="offcanvas-header">
                                    <div class="d-flex flex-column px-4">
                                        <label class="font-weight-600 font-size-22px">Training Opportunity Details</label>
                                    </div>
                                </div>
                                <div class="offcanvas-body">
                                    <div class="d-flex flex-column px-4">
                                        <label class="font-size-28px mt-1 font-weight-500 color-876363"><?= htmlspecialchars($offer['job_title']); ?></label>
                                        <label class="font-size-13px mt-3 color-876363">Location</label>
                                        <label class="font-size-13px mt-2 font-weight-500"><?= htmlspecialchars($offer['location']); ?></label>
                                        <label class="font-size-13px mt-3 color-876363">Training Duration</label>
                                        <label class="font-size-13px mt-2 font-weight-500"><?= htmlspecialchars($offer['training_duration']); ?></label>
                                        <label class="font-size-13px mt-3 color-876363">Type of Training</label>
                                        <label class="font-size-13px mt-2 font-weight-500"><?= htmlspecialchars($offer['training_type']); ?></label>
                                        <label class="font-size-13px mt-3 color-876363">About the Internship</label>
                                        <label class="font-size-13px mt-2 font-weight-500"><?= nl2br(htmlspecialchars($offer['about_internship'])); ?></label>
                                        <label class="font-size-13px mt-3 color-876363">Responsibilities</label>
                                        <label class="font-size-13px mt-2 font-weight-500"><?= nl2br(htmlspecialchars($offer['responsibilities'])); ?></label>
                                        <label class="font-size-13px mt-3 color-876363">Required Skills</label>
                                        <label class="font-size-13px mt-2 font-weight-500"><?= nl2br(htmlspecialchars($offer['requirements'])); ?></label>
                                        <label class="font-size-13px mt-3 color-876363">Salary</label>
                                        <label class="font-size-13px mt-2 font-weight-500"><?= $offer['salary'] ? 'Yes' : 'No'; ?></label>
                                        <label class="font-size-13px mt-3 color-876363">Application Deadline</label>
                                        <label class="font-size-13px mt-2 font-weight-500"><?= htmlspecialchars($offer['application_deadline']); ?></label>
                                        <label class="font-size-13px mt-3 color-876363">Max Applicants</label>
                                        <label class="font-size-13px mt-2 font-weight-500"><?= htmlspecialchars($offer['max_applicants']); ?></label>
                                    </div>
                                </div>
                            </div>

                            <!-- Edit Offer Offcanvas -->
                            <div class="offcanvas offcanvas-end width-600px" tabindex="-1" id="editOffer<?= $offer['offer_id']; ?>" aria-labelledby="editOfferLabel<?= $offer['offer_id']; ?>">
                                <div class="offcanvas-header ">
                                    <div class="d-flex flex-column px-4">
                                        <label class="font-weight-600 font-size-22px">Edit Training Opportunity</label>
                                    </div>
                                </div>
                                
                                <div class="offcanvas-body pt-0">
                                    <form method="POST" action="update_offer.php">
                                        <input type="hidden" name="offer_id" value="<?= $offer['offer_id']; ?>">
                                        
                                        <div class="px-4 mt-2">
                                            <div class="row">
                                                <div class="col-md-7 mb-3">
                                                    <label class="form-label font-size-13px color-876363 font-weight-600">Job Title</label>
                                                    <input type="text" name="job_title" value="<?= htmlspecialchars($offer['job_title']); ?>" class="form-control font-size-13px" required>
                                                </div>
                                                <div class="col-md-5 mb-3">
                                                    <label class="form-label font-size-13px color-876363 font-weight-600">Training Type</label>
                                                    <select name="training_type" class="form-select font-size-13px" required>
                                                        <option value="Full-time" <?= $offer['training_type'] == 'Full-time' ? 'selected' : '' ?>>Full-time</option>
                                                        <option value="Part-time" <?= $offer['training_type'] == 'Part-time' ? 'selected' : '' ?>>Part-time</option>
                                                        <option value="Remote" <?= $offer['training_type'] == 'Remote' ? 'selected' : '' ?>>Remote</option>
                                                        <option value="Hybrid" <?= $offer['training_type'] == 'Hybrid' ? 'selected' : '' ?>>Hybrid</option>
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-7 mb-3">
                                                    <label class="form-label font-size-13px color-876363 font-weight-600">Location</label>
                                                    <input type="text" name="location" value="<?= htmlspecialchars($offer['location']); ?>" class="form-control font-size-13px" required>
                                                </div>
                                                <div class="col-md-5 mb-3">
                                                    <label class="form-label font-size-13px color-876363 font-weight-600">Duration</label>
                                                    <input type="text" name="training_duration" value="<?= htmlspecialchars($offer['training_duration']); ?>" class="form-control font-size-13px" required>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-4 mb-3">
                                                    <label class="form-label font-size-13px color-876363 font-weight-600">Salary?</label>
                                                    <select name="salary" class="form-select font-size-13px" required>
                                                        <option value="0" <?= $offer['salary'] == 0 ? 'selected' : '' ?>>No (Unpaid)</option>
                                                        <option value="1" <?= $offer['salary'] == 1 ? 'selected' : '' ?>>Yes (Paid)</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-4 mb-3">
                                                    <label class="form-label font-size-13px color-876363 font-weight-600">Max Applicants</label>
                                                    <input type="number" name="max_applicants" value="<?= htmlspecialchars($offer['max_applicants']); ?>" class="form-control font-size-13px" placeholder="0">
                                                </div>
                                                <div class="col-md-4 mb-3">
                                                    <label class="form-label font-size-13px color-876363 font-weight-600">Deadline</label>
                                                    <input type="date" name="application_deadline" value="<?= htmlspecialchars($offer['application_deadline']); ?>" class="form-control font-size-13px" required>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label font-size-13px color-876363 font-weight-600">About the Internship</label>
                                                <textarea name="about_internship" class="form-control font-size-13px" rows="3" required><?= htmlspecialchars($offer['about_internship']); ?></textarea>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label font-size-13px color-876363 font-weight-600">Responsibilities</label>
                                                <textarea name="responsibilities" class="form-control font-size-13px" rows="3" required><?= htmlspecialchars($offer['responsibilities']); ?></textarea>
                                            </div>

                                            <div class="mb-4">
                                                <label class="form-label font-size-13px color-876363 font-weight-600">Required Skills</label>
                                                <textarea name="requirements" class="form-control font-size-13px" rows="3" required><?= htmlspecialchars($offer['requirements']); ?></textarea>
                                            </div>

                                            <div class="mt-4  d-flex justify-content-end pb-2">
                                                <button type="button" class="btn px-4 me-2 btn-E5E8EB font-size-12px" data-bs-dismiss="offcanvas">Cancel</button>
                                                <button type="submit" name="update_offer" class="btn px-4 btn-E51A1A font-size-12px text-white shadow-sm">Save Changes</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="5" class="text-center py-4">No Training Opportunities found matching your criteria.</td></tr>
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
<script>
        // JS logic for filter box (visibility toggle)
        const filterBtn = document.getElementById('filterBtn');
        const filterBox = document.getElementById('filterBox');
        if (filterBtn && filterBox) {
            filterBtn.addEventListener('click', () => {
                filterBox.style.display = filterBox.style.display === 'none' ? 'block' : 'none';
            });
            document.addEventListener('click', function(e) {
                if (!filterBtn.contains(e.target) && !filterBox.contains(e.target)) {
                    filterBox.style.display = 'none';
                }
            });
        }
    </script>
</html>