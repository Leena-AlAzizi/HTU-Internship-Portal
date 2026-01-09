<?php
session_start();
require '../db_connection.php';

if (!isset($_SESSION['staff_id']) || $_SESSION['user_type'] !== 'staff') {
    header("Location: ../login.php");
    exit();
}

$active_tab = $_GET['tab'] ?? 'pills-pending';
$search_term = $_GET['search'] ?? '';
$department_filter = $_GET['department'] ?? '';
$major_filter = $_GET['major'] ?? '';
$year_filter = $_GET['year'] ?? '';

$search_condition = "";
if (!empty($search_term)) {
    $search = mysqli_real_escape_string($conn, $search_term); 
    $search_condition .= " AND (o.job_title LIKE '%$search%' OR c.company_name LIKE '%$search%')";
}

$filter_condition = ""; 

$base_query = "
    SELECT 
        o.*, 
        c.company_name, 
        c.image
    FROM offers o
    INNER JOIN companies c ON o.company_id = c.company_id
";

$pending_query = $base_query . " WHERE o.status = 'Pending' " . $search_condition . $filter_condition . " ORDER BY o.created_at DESC";
$pending_result = mysqli_query($conn, $pending_query);

$accepted_query = $base_query . " WHERE o.status = 'Open' " . $search_condition . $filter_condition . " ORDER BY o.created_at DESC";
$accepted_result = mysqli_query($conn, $accepted_query); 

$rejected_query = $base_query . " WHERE o.status = 'Expired' " . $search_condition . $filter_condition . " ORDER BY o.created_at DESC";
$rejected_result = mysqli_query($conn, $rejected_query); 

$current_search = htmlspecialchars($_GET['search'] ?? '');
?>
<!DOCTYPE html>
<html lang="en" class="h-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Offers</title>
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
                          <a href="students.php" class="btn w-100 text-start">
                            <i class="bi bi-people"></i>
                            <label class="ms-2">Students</label>
                          </a>
                        </li>
                        <li class="nav-item">
                          <a href="offers.php" class="btn w-100 text-start bg-color-F5F0F0">
                            <i class="bi bi-briefcase-fill"></i>
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
                    <label class="font-weight-600 font-size-22px">Offers</label>
                    <div class="d-flex mt-3">
                        <form method="GET" class="search d-flex bg-color-F5F0F0 border-radius-9px w-100">
                            <button class="btn pe-0" type="submit"><i class="bi bi-search color-876363 font-size-13px"></i></button>
                            <input class="form-control me-2 bg-color-F5F0F0 font-size-13px border-none" type="text" name="search" placeholder="Search Offer Title or Company" value="<?= $current_search ?>" />
                            <input type="hidden" name="tab" value="<?= $active_tab ?>">
                        </form>
                    </div>

                    <ul class="nav nav-pills mb-3 mt-4 border-bottom-color-876363" id="pills-tab" role="tablist">
                        <li class="nav-item"><button class="nav-link <?= ($active_tab == 'pills-pending') ? 'active' : '' ?>" data-bs-toggle="pill" data-bs-target="#pills-pending" type="button">Pending</button></li>
                        <li class="nav-item"><button class="nav-link <?= ($active_tab == 'pills-home') ? 'active' : '' ?>" data-bs-toggle="pill" data-bs-target="#pills-home" type="button">Open</button></li>
                        <li class="nav-item"><button class="nav-link <?= ($active_tab == 'pills-profile') ? 'active' : '' ?>" data-bs-toggle="pill" data-bs-target="#pills-profile" type="button">Expired</button></li>
                    </ul>

                    <div class="tab-content">
                        <?php 
                        $tabs = [
                            'pills-pending' => $pending_result,
                            'pills-home' => $accepted_result,
                            'pills-profile' => $rejected_result
                        ];

                        foreach ($tabs as $id => $result): ?>
                        <div class="tab-pane fade <?= ($active_tab == $id) ? 'show active' : '' ?>" id="<?= $id ?>" role="tabpanel">
                            <table class="table mb-0 font-size-12px border-radius-top-10px mt-4 tablehead">
                                <thead class="bg-color-F5F0F0">
                                    <tr>
                                        <th class="width-30per"><div class="px-3">Title</div></th>
                                        <th class="width-30per"><div class="px-3">Company</div></th>
                                        <th class="width-20per"><div class="px-3">Training Type</div></th>
                                        <th class="width-20per"><div class="px-3">Actions</div></th>
                                    </tr>
                                </thead>
                            </table>
                            <div class="scroll-y-axis max-h-450px">
                                <table class="table tablebody font-size-13px col-5-color-876363">
                                    <tbody>
                                        <?php if (mysqli_num_rows($result) > 0): 
                                            while ($row = mysqli_fetch_assoc($result)): ?>
                                            <tr>
                                                <td class="width-30per"><div class="px-3"><?= htmlspecialchars($row['job_title']) ?></div></td>
                                                <td class="width-30per"><div class="px-3"><?= htmlspecialchars($row['company_name']) ?></div></td>
                                                <td class="width-20per"><div class="px-3"><?= htmlspecialchars($row['training_type']) ?></div></td>
                                                <td class="width-20per">
                                                    <div class="px-3">
                                                        <button type="button" class="btn btn-sm color-876363" data-bs-toggle="offcanvas" data-bs-target="#offcanvasOffer<?= $row['offer_id'] ?>">
                                                            <i class="bi bi-eye"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>

                                            <div class="offcanvas offcanvas-end width-600px" tabindex="-1" id="offcanvasOffer<?= $row['offer_id'] ?>" aria-labelledby="offcanvasOfferLabel<?= $row['offer_id'] ?>">
                                                <div class="offcanvas-header">
                                                    <div class="d-flex px-4 align-items-center">
                                                        <label class="font-weight-600 font-size-22px">Offer Details</label>
                                                        <span class="font-size-11px bg-color-F5F0F0 border-radius-15px px-3 py-1 ms-3">
                                                            <?= htmlspecialchars($row['status']) ?>
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="offcanvas-body">
                                                    <div class="d-flex flex-column px-4">
                                                        <div class="d-flex align-items-center mb-4">
                                                            <img src="<?= htmlspecialchars($row['image'] ?? 'img/default_company.png') ?>" alt="" class="width-60px height-60px border-radius-50per img-cover">
                                                            <div class="d-flex flex-column ms-3">
                                                                <label class="font-size-18px font-weight-600 color-876363"><?= htmlspecialchars($row['company_name']) ?></label>
                                                                <label class="font-size-14px font-weight-500"><?= htmlspecialchars($row['job_title']) ?></label>
                                                            </div>
                                                        </div>

                                                        <div class="d-flex flex-column">
                                                            <label class="font-size-13px color-876363">Location</label>
                                                            <label class="font-size-13px mt-1 mb-3 font-weight-600"><?= htmlspecialchars($row['location']) ?></label>

                                                            <label class="font-size-13px color-876363">Training Duration</label>
                                                            <label class="font-size-13px mt-1 mb-3 font-weight-600"><?= htmlspecialchars($row['training_duration']) ?></label>

                                                            <label class="font-size-13px color-876363">Type of training</label>
                                                            <label class="font-size-13px mt-1 mb-3 font-weight-600"><?= htmlspecialchars($row['training_type']) ?></label>

                                                            <label class="font-size-13px color-876363">About the internship</label>
                                                            <label class="font-size-13px mt-1 mb-3 font-weight-600 text-justify"><?= nl2br(htmlspecialchars($row['about_internship'])) ?></label>

                                                            <label class="font-size-13px color-876363">Responsibilities</label>
                                                            <label class="font-size-13px mt-1 mb-3 font-weight-600"><?= nl2br(htmlspecialchars($row['responsibilities'])) ?></label>

                                                            <label class="font-size-13px color-876363">Requirements</label>
                                                            <label class="font-size-13px mt-1 mb-3 font-weight-600"><?= nl2br(htmlspecialchars($row['requirements'])) ?></label>

                                                            <label class="font-size-13px color-876363">Is there a salary?</label>
                                                            <label class="font-size-13px mt-1 mb-3 font-weight-600"><?= $row['salary'] ? 'Yes' : 'No' ?></label>

                                                            <label class="font-size-13px color-876363">Application deadline</label>
                                                            <label class="font-size-13px mt-1 mb-3 font-weight-600"><?= date('d/m/Y', strtotime($row['application_deadline'])) ?></label>
                                                        </div>
                                                        <div class="my-3 d-flex justify-content-end px-4">
                                                            <?php if ($row['status'] == 'Pending'): ?>
                                                                <a href="process_offer.php?id=<?= $row['offer_id'] ?>&action=reject" 
                                                                class="btn px-3 me-3 btn-E5E8EB font-size-12px text-dark" 
                                                                onclick="return confirm('Are you sure you want to reject this offer?')">
                                                                Reject
                                                                </a>
                                                                
                                                                <a href="process_offer.php?id=<?= $row['offer_id'] ?>&action=approve" 
                                                                class="btn px-3 btn-E51A1A font-size-12px text-white">
                                                                Approve
                                                                </a>
                                                            <?php elseif ($row['status'] == 'Open'): ?>
                                                                <a href="process_offer.php?id=<?= $row['offer_id'] ?>&action=expire" 
                                                                class="btn px-3 btn-E5E8EB font-size-12px text-dark"
                                                                onclick="return confirm('Are you sure you want to mark this offer as Expired?')">
                                                                <i class="bi bi-clock-history me-1"></i> Set as Expired
                                                                </a>
                                                            <?php else: ?>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php endwhile; 
                                        else: ?>
                                            <tr><td colspan="4" class="text-center py-4">No offers found.</td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>