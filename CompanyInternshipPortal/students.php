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

$company_name = htmlspecialchars($company['company_name']);
$company_image = (!empty($company['image']) && file_exists("../" . $company['image'])) 
                 ? "../" . $company['image'] 
                 : '../img/default_company.png';

$search_term = $_GET['search'] ?? '';
$major_filter = $_GET['major'] ?? '';
$year_filter = $_GET['year'] ?? '';
$active_tab = $_GET['tab'] ?? 'pills-home'; 
$conditions = [];
$bind_types = "";
$bind_params = [];

if (!empty($search_term)) {
    $conditions[] = "CONCAT(s.first_name, ' ', s.last_name) LIKE ?";
    $bind_types .= "s";
    $bind_params[] = "%" . $search_term . "%";
}

if (!empty($major_filter)) {
    $conditions[] = "m.major_id = ?";
    $bind_types .= "i";
    $bind_params[] = (int)$major_filter;
}

if (!empty($year_filter)) {
    $conditions[] = "s.academic_year = ?";
    $bind_types .= "s";
    $bind_params[] = $year_filter;
}

$global_where = empty($conditions) ? "" : " AND " . implode(" AND ", $conditions);
function execute_student_query($conn, $company_id, $status_condition, $global_where, $bind_types, $bind_params) {
    $query = "
        SELECT 
            a.application_id, a.status, s.student_id, s.first_name, s.last_name, s.profile_image, 
            s.academic_year, s.email, s.phone_number, s.place_of_residence, s.cv_file,s.department_id, 
            m.major_name, o.job_title
        FROM applications a
        JOIN students s ON a.student_id = s.student_id
        JOIN majors m ON s.major_id = m.major_id
        JOIN offers o ON a.offer_id = o.offer_id
        WHERE o.company_id = ? 
        AND {$status_condition}
        {$global_where}
        ORDER BY a.application_date DESC
    ";
    
    $final_bind_types = "i" . $bind_types;
    $final_bind_params = array_merge([$company_id], $bind_params);

    $stmt = $conn->prepare($query);
    if ($stmt) {
        $stmt->bind_param($final_bind_types, ...$final_bind_params);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result;
    }
    return false;
}

$accepted_results = execute_student_query($conn, $company_id, "a.status = 'Accepted'", $global_where, $bind_types, $bind_params);

$in_process_results = execute_student_query($conn, $company_id, "a.status IN ('Pending','Interview')", $global_where, $bind_types, $bind_params);

$looking_for_query = "
    SELECT 
        s.student_id,
        s.first_name,
        s.last_name,
        s.profile_image,
        s.academic_year,
        s.email,
        s.phone_number,
        s.place_of_residence,
        s.department_id,
        s.cv_file,
        m.major_name
    FROM students s
    LEFT JOIN majors m ON s.major_id = m.major_id
    WHERE s.status IN ('Not Found','Interviewing') 
    {$global_where}
    ORDER BY s.first_name ASC
";
$nf_bind_types = $bind_types;
$nf_bind_params = $bind_params;

$stmt_nf = $conn->prepare($looking_for_query);
if ($stmt_nf) {
    if (!empty($nf_bind_types)) {
        $stmt_nf->bind_param($nf_bind_types, ...$nf_bind_params);
    }
    $stmt_nf->execute();
    $resultNF = $stmt_nf->get_result();
    $stmt_nf->close();
} else {
    $resultNF = false;
}

$current_filters_except_tab = http_build_query(array_filter($_GET, fn($key) => $key !== 'tab', ARRAY_FILTER_USE_KEY));
$link_separator = !empty($current_filters_except_tab) ? '&' : '';

$active_tab = $_GET['tab'] ?? 'offered';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Students</title>
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
                                <a href="offers.php" class="btn w-100 text-start">
                                    <i class="bi  bi-briefcase"></i>
                                    <label for="" class="ms-2">Training Opportunities</label>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="students.php" class="btn w-100 text-start bg-color-F5F0F0">
                                    <i class="bi bi-people-fill"></i>
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
                <div class="d-flex flex-column">
                  <label for="" class="font-weight-600 font-size-22px">Students</label>
                  <label for="" class="font-size-12px mt-1 pe-5 me-3 color-5f5f5f">Students are shown in three tabs. Offered Students tab shows the students you have accepted into your company. Not offered students tab shows the students who have not been offered a position yet. In-Process students tab shows students that have been accepted to the interviews phase.</label>
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
                            placeholder="Search Student Name"
                            value="<?= htmlspecialchars($search_term) ?>"
                        />
                        <input type="hidden" name="tab" value="<?= $active_tab ?>">
                        <input type="hidden" name="major" value="<?= htmlspecialchars($major_filter) ?>">
                        <input type="hidden" name="year" value="<?= htmlspecialchars($year_filter) ?>">
                    </form>
                    
                    <div class="position-relative">
                        <button class="btn mx-2" type="button" id="filterBtn">
                            <i class="bi bi-funnel-fill color-876363 font-size-19px"></i>
                        </button>

                        <div id="filterBox" 
                            class="p-3 shadow border bg-white position-absolute end-0 mt-2 border-radius-10px"
                            style="width: 260px; display: none; z-index: 1000;">

                            <form method="GET">
                                
                                <label class="font-size-13px mb-1">Major</label>
                                <select name="major" class="form-select font-size-13px mb-3">
                                    <option value="">All</option>
                                    <?php 
                                    $majors_query = "SELECT major_id, major_name FROM majors";
                                    $majors_result = $conn->query($majors_query);
                                    if ($majors_result) while($maj = $majors_result->fetch_assoc()): ?>
                                        <option value="<?= $maj['major_id'] ?>" <?= ($major_filter == $maj['major_id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($maj['major_name']) ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>

                                <label class="font-size-13px mb-1">Year</label>
                                <select name="year" class="form-select font-size-13px mb-3">
                                    <option value="">All</option>
                                    <?php 
                                    $years_q = $conn->query("SELECT DISTINCT academic_year FROM students ORDER BY academic_year DESC");
                                    if ($years_q) while($yr = $years_q->fetch_assoc()): ?>
                                        <option value="<?= $yr['academic_year'] ?>" <?= ($year_filter == $yr['academic_year']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($yr['academic_year']) ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                                
                                <input type="hidden" name="search" value="<?= htmlspecialchars($search_term) ?>">
                                <input type="hidden" name="tab" value="<?= $active_tab ?>">
                                <button class="btn btn-sm bg-color-F5F0F0 w-100 font-size-13px">Apply Filters</button>
                            </form>
                        </div>
                    </div>
                </div>
                <ul class="nav nav-pills mb-3 mt-4 border-bottom-color-876363" id="pills-tab" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link <?= ($active_tab == 'offered') ? 'active' : '' ?>" 
                        href="?tab=offered<?= $link_separator . $current_filters_except_tab ?>">Offered Students</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= ($active_tab == 'in_process') ? 'active' : '' ?>" 
                        href="?tab=in_process<?= $link_separator . $current_filters_except_tab ?>">In-Process Students</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= ($active_tab == 'looking') ? 'active' : '' ?>" 
                        href="?tab=looking<?= $link_separator . $current_filters_except_tab ?>">Looking for Training Opportunity</a>
                    </li>
                </ul>
                <div class="tab-content" id="pills-tabContent">
                    <div class="tab-pane fade <?= ($active_tab == 'offered') ? 'show active' : '' ?>" id="pills-home" role="tabpanel">
                        <table class="table mb-0 font-size-12px border-radius-top-10px mt-4 tablehead">
                            <thead>
                                <tr class="bg-color-F5F0F0">
                                    <th class="width-30per">
                                        <div class="px-3">Name</div>
                                    </th>
                                    <th class="width-30per">
                                        <div class="px-3">Training Opportunity Title</div>
                                    </th>
                                    <th class="width-25per">                                            
                                        <div class="px-3">Major</div>
                                    </th>
                                    <th class="width-15per">                                            
                                        <div class="px-3">Process</div>
                                    </th>
                                </tr>
                            </thead>
                        </table>
                        <div class="scroll-y-axis max-h-450px">
                            <table class="table tablebody font-size-13px col-5-color-876363">
                                <tbody>
                                    <?php 
                                        if ($accepted_results && $accepted_results->num_rows > 0):
                                        while ($row = $accepted_results->fetch_assoc()) {
                                            $fullName = $row['first_name'] . ' ' . $row['last_name'];
                                        ?>
                                        <tr>
                                            <td class="width-30per">
                                                <div class="px-3"><?= htmlspecialchars($fullName) ?></div>
                                            </td>

                                            <td class="width-30per">
                                                <div class="px-3"><?= htmlspecialchars($row['job_title']) ?></div>
                                            </td>

                                            <td class="width-25per">
                                                <div class="px-3"><?= htmlspecialchars($row['major_name']) ?></div>
                                            </td>

                                            <td class="width-15per">
                                                <div class="px-3">
                                                    <button type="button" class="btn btn-sm color-876363" 
                                                            data-bs-toggle="offcanvas" 
                                                            data-bs-target="#viewStudent<?= $row['application_id'] ?>">
                                                        <i class="bi bi-eye"></i>
                                                    </button>
                                                </div>
                                            </td>
                                            <div class="offcanvas offcanvas-end width-650px" tabindex="-1" id="viewStudent<?= $row['application_id'] ?>">
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
                                                            <img src="../<?= htmlspecialchars($row['profile_image']) ?>" class="px-0 border-radius-50per width-130px height-130px img-cover margin-top--60px index-one border-color-fff shadow">                                                            <label for="" class="mt-2 font-weight-600 font-size-25px"><?= htmlspecialchars($fullName) ?></label>
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
                                        </tr>
                                    <?php } ?>
                                    <?php else: ?>
                                        <tr><td colspan="4" class="text-center py-4">No Student found matching your criteria.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="tab-pane fade <?= ($active_tab == 'in_process') ? 'show active' : '' ?>" id="pills-profile" role="tabpanel">
                        <table class="table mb-0 font-size-12px border-radius-top-10px mt-4 tablehead">
                            <thead class="bg-color-F5F0F0">
                                <tr>
                                    <th class="width-30per">
                                        <div class="px-3">Name</div>
                                    </th>
                                    <th class="width-30per">                                            
                                        <div class="px-3">Major</div>
                                    </th>
                                    <th class="width-25per">
                                        <div class="px-3">Intern Title</div>
                                    </th>
                                    <th class="width-15per">                                            
                                        <div class="px-3">Information</div>
                                    </th>
                                </tr>
                            </thead>
                        </table>
                        <div class="scroll-y-axis max-h-450px">
                            <table class="table tablebody font-size-13px col-5-color-876363">
                                <tbody>
                                <?php 
                                if ($in_process_results && $in_process_results->num_rows > 0):
                                while ($row = $in_process_results->fetch_assoc()): ?>
                                    <?php 
                                        $fullName = $row['first_name'] . " " . $row['last_name'];
                                        $offId = "viewStudent" . $row['application_id'];
                                    ?>
                                    <tr>
                                        <td class="width-30per">
                                            <div class="px-3"><?= htmlspecialchars($fullName) ?></div>
                                        </td>

                                        <td class="width-30per">
                                            <div class="px-3"><?= htmlspecialchars($row['major_name']) ?></div>
                                        </td>

                                        <td class="width-25per">
                                            <div class="px-3"><?= htmlspecialchars($row['job_title']) ?></div>
                                        </td>

                                        <td class="width-15per">
                                            <div class="px-3">
                                                <button type="button"  
                                                        class="btn btn-sm color-876363"
                                                        data-bs-toggle="offcanvas" 
                                                        data-bs-target="#<?= $offId ?>">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                                <?php else: ?>
                                    <tr><td colspan="4" class="text-center py-4">No Student found matching your criteria.</td></tr>
                                <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php 
                        mysqli_data_seek($in_process_results, 0); 

                        while ($row = $in_process_results->fetch_assoc()): 
                            $fullName = $row['first_name'] . " " . $row['last_name'];
                            $offId = "viewStudent" . $row['application_id'];
                        ?>
                        <div class="offcanvas offcanvas-end width-700px" tabindex="-1" id="<?= $offId ?>">
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
                                                            <img src="../<?= htmlspecialchars($row['profile_image']) ?>" class="px-0 border-radius-50per width-130px height-130px img-cover margin-top--60px index-one border-color-fff shadow">                                                            <label for="" class="mt-2 font-weight-600 font-size-25px"><?= htmlspecialchars($fullName) ?></label>
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
                                                    <div class="my-2 mt-4 d-flex justify-content-end align-items-center">
                                                        <?php if ($row['status'] === 'Interview'): ?>
                                                            <a href="reject.php?id=<?= $row['application_id'] ?>" 
                                                            class="btn px-3 me-3 btn-E5E8EB font-size-12px"
                                                            onclick="return confirm('Are you sure you want to reject this student?')">Reject Student</a>

                                                            <a href="accept.php?id=<?= $row['application_id'] ?>" 
                                                            class="btn px-3 btn-E51A1A font-size-12px text-white">Accept Student</a>
                                                        <?php else: ?>
                                                            <div class="color-5f5f5f font-size-12px ">
                                                                <i class="bi bi-hourglass-split me-1"></i>
                                                                Waiting for the student's response ...
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                        </div>
                        <?php endwhile; ?>
                    </div>
                    <div class="tab-pane fade <?= ($active_tab == 'looking') ? 'show active' : '' ?>" id="pills-look" role="tabpanel">
                        <table class="table mb-0 font-size-12px border-radius-top-10px mt-4 tablehead">
                            <thead class="bg-color-F5F0F0">
                                <tr>
                                    <th class="width-30per">
                                        <div class="px-3">Name</div>
                                    </th>
                                    <th class="width-30per">                                            
                                        <div class="px-3">Major</div>
                                    </th>
                                    <th class="width-25per">
                                        <div class="px-3">Year</div>
                                    </th>
                                    <th class="width-15per">                                            
                                        <div class="px-3">Information</div>
                                    </th>
                                </tr>
                            </thead>
                        </table>
                        <div class="scroll-y-axis max-h-450px">
                            <table class="table tablebody font-size-13px col-5-color-876363">
                                <tbody>
                                <?php 
                                if ($resultNF && $resultNF->num_rows > 0):
                                while ($row = $resultNF->fetch_assoc()): 
                                    $fullName = $row['first_name'] . " " . $row['last_name'];
                                    $offId = "nfView" . $row['student_id'];
                                    $modalId = "sendOffer" . $row['student_id'];
                                ?>
                                    <tr>
                                        <td class="width-30per">
                                            <div class="px-3"><?= htmlspecialchars($fullName) ?></div>
                                        </td>

                                        <td class="width-30per">
                                            <div class="px-3"><?= htmlspecialchars($row['major_name']) ?></div>
                                        </td>

                                        <td class="width-25per">
                                            <div class="px-3"><?= htmlspecialchars($row['academic_year']) ?></div>
                                        </td>

                                        <td class="width-15per">
                                            <div class="px-3">
                                                <button type="button" class="btn btn-sm color-876363" 
                                                        data-bs-toggle="offcanvas" 
                                                        data-bs-target="#<?= $offId ?>">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                                <?php else: ?>
                                    <tr><td colspan="4" class="text-center py-4">No Student found matching your criteria.</td></tr>
                                <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php 
                        mysqli_data_seek($resultNF, 0);
                        while ($row = $resultNF->fetch_assoc()):
                            $fullName = $row['first_name'] . " " . $row['last_name'];
                            $offId = "nfView" . $row['student_id'];
                            $modalId = "sendOffer" . $row['student_id'];
                        ?>
                        <div class="offcanvas offcanvas-end width-700px" tabindex="-1" id="<?= $offId ?>">
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
                                                            <img src="../<?= htmlspecialchars($row['profile_image']) ?>" class="px-0 border-radius-50per width-130px height-130px img-cover margin-top--60px index-one border-color-fff shadow">                                                            <label for="" class="mt-2 font-weight-600 font-size-25px"><?= htmlspecialchars($fullName) ?></label>
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
                                                    <div class="row mx-2 px-3 pt-2 pb-4 mt-4 bg-color-f4f4f4 border-radius-10px">
                                                        <label for="" class="font-size-12px color-5f5f5f my-2">Send a Training Opportunity</label>
                                                        
                                                        <form action="send_offer.php" method="POST" class="d-flex flex-column gap-2">
                                                            <input type="hidden" name="student_id" value="<?= $row['student_id'] ?>">
                                                            
                                                            <div class="">
                                                                <select name="offer_id" class="form-select font-size-12px shadow-none" required>
                                                                    <option value="" disabled selected>Select an open Training Opportunity</option>
                                                                    <?php 
                                                                    $offers = $conn->query("SELECT offer_id, job_title FROM offers WHERE company_id = $company_id AND status = 'Open'AND offer_id NOT IN (SELECT offer_id FROM applications  WHERE student_id = {$row['student_id']} )");
                                                                    if ($offers && $offers->num_rows > 0) {
                                                                        while ($o = $offers->fetch_assoc()) {
                                                                            echo "<option value='{$o['offer_id']}'>".htmlspecialchars($o['job_title'])."</option>";
                                                                        }
                                                                    }
                                                                    ?>
                                                                </select>
                                                                
                                                                <button type="submit" class="btn btn-E51A1A font-size-12px mt-3">
                                                                    Send Training Opportunity
                                                                </button>
                                                            </div>
                                                            
                                                            <?php if ($offers->num_rows == 0): ?>
                                                                <small class="text-danger font-size-11px mt-1">No open Training Opportunities available. Please create one first.</small>
                                                            <?php endif; ?>
                                                        </form>
                                                    </div>
                                                </div>
                        </div>

                        <?php endwhile; ?>
                    </div>
                </div>
              </div>
            </div>
        </div>
    </div>
</body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
<script>
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
    
    document.querySelectorAll('.nav-pills a.nav-link[data-bs-toggle="pill"]').forEach(tab => {
        tab.addEventListener('click', function(e) {
            e.preventDefault();
            const targetUrl = this.getAttribute('href');
            if (targetUrl) {
                window.location.href = targetUrl;
            }
        });
    });
</script>
</html>
