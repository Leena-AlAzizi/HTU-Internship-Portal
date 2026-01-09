<?php
session_start();
require '../db_connection.php';

if (!isset($_SESSION['staff_id']) || $_SESSION['user_type'] !== 'staff') {
    header("Location: ../login.php");
    exit();
}

$active_tab = $_GET['tab'] ?? 'pills-pending';
$search_term = $_GET['search'] ?? '';

$search_condition = "";
if (!empty($search_term)) {
    // Escaping the string prevents SQL injection and ensures LIKE works correctly
    $search_condition = " AND CONCAT(s.first_name, ' ', s.last_name) LIKE '%" . mysqli_real_escape_string($conn, $search_term) . "%'";
}

$base_query = "SELECT eo.*, CONCAT(s.first_name, ' ', s.last_name) AS student_name, s.profile_image 
               FROM external_offers eo
               LEFT JOIN students s ON eo.student_id = s.student_id";

$pending_query = $base_query . " WHERE eo.status = 'Pending' " . $search_condition . " ORDER BY eo.created_at DESC";
$pending_result = mysqli_query($conn, $pending_query);

$accepted_query = $base_query . " WHERE eo.status = 'Accepted' " . $search_condition . " ORDER BY eo.created_at DESC";
$accepted_result = mysqli_query($conn, $accepted_query);

$rejected_query = $base_query . " WHERE eo.status = 'Rejected' " . $search_condition . " ORDER BY eo.created_at DESC";
$rejected_result = mysqli_query($conn, $rejected_query);

$current_filters_array = array_filter($_GET, fn($key) => $key !== 'tab' && $key !== 'search', ARRAY_FILTER_USE_KEY);
if (!empty($search_term)) {
    $current_filters_array['search'] = $search_term;
}
$current_filters = http_build_query($current_filters_array);
$filter_separator = !empty($current_filters) ? '&' : '';
?>
<!DOCTYPE html>
<html lang="en" class="h-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>External Offers</title>
    <link rel="icon" type="image/x-icon" href="../img/htu_logo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.0/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body class="h-100">
    <div class="container-fluid h-100">
        <div class="row h-100">
            <!-- LEFT MENU -->
            <div class="left-side col-md-2 ps-1">
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
                          <a href="offers.php" class="btn w-100 text-start">
                            <i class="bi bi-briefcase"></i>
                            <label class="ms-2">Offers</label>
                          </a>
                        </li>
                        <li class="nav-item">
                          <a href="external_offer.php" class="btn w-100 text-start bg-color-F5F0F0">
                            <i class="bi bi-person-plus-fill"></i>
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

            <!-- RIGHT SIDE -->
          <div class="right-side col-md-10 p-4">
              <div class="d-flex flex-column">
                  <label class="font-weight-600 font-size-22px">External Offers</label>
                  <label class="font-size-12px mt-3 color-5f5f5f">
                      Overview of External internships.
                  </label>
                  <div class="d-flex mt-3">
                      <form method="GET" class="search d-flex bg-color-F5F0F0 border-radius-9px w-100">
                          <button class="btn pe-0" type="submit">
                              <i class="bi bi-search color-876363 font-size-13px"></i>
                          </button>
                          <input 
                              class="form-control me-2 bg-color-F5F0F0 font-size-13px border-none" 
                              type="text" 
                              name="search"
                              placeholder="Search Student Name"
                              value="<?= htmlspecialchars($_GET['search'] ?? '') ?>"
                          />
                          
                          <input type="hidden" name="tab" value="<?= $active_tab ?>">
                          <?php 

                          $search_param_exists = false;
                          foreach ($_GET as $key => $value) {
                              if ($key == 'search') {
                                  $search_param_exists = true;
                                  continue;
                              }
                              if ($key == 'tab') continue;
                              echo '<input type="hidden" name="' . htmlspecialchars($key) . '" value="' . htmlspecialchars($value) . '">';
                          }
                          ?>
                      </form>
                      <div class="position-relative">

                          <button class="btn mx-2" type="button" id="filterBtn">
                              <i class="bi bi-funnel-fill color-876363 font-size-19px"></i>
                          </button>

                          <div id="filterBox" 
                              class="p-3 shadow border bg-white position-absolute end-0 mt-2 border-radius-10px"
                              style="width: 260px; display: none; z-index: 1000;">

                              <form method="GET">
                                  <label class="font-size-13px mb-1">Department</label>
                                  <select name="department" class="form-select font-size-13px mb-3">
                                      <option value="">All</option>
                                      <?php 
                                      $departments_query = "SELECT * FROM departments";
                                      $departments_result_filter = mysqli_query($conn, $departments_query);
                                      if ($departments_result_filter) {
                                          while($dep = mysqli_fetch_assoc($departments_result_filter)) { ?>
                                              <option class="" value="<?= $dep['department_id'] ?>"
                                                  <?= (isset($_GET['department']) && $_GET['department'] == $dep['department_id']) ? 'selected' : '' ?>>
                                                  <?= $dep['department_name'] ?>
                                              </option>
                                          <?php } 
                                      }?>
                                  </select>
                                  <label class="font-size-13px mb-1">Major</label>
                                  <select name="major" class="form-select font-size-13px mb-3">
                                      <option value="">All</option>
                                      <?php 
                                      $majors_query = "SELECT * FROM majors";
                                      $majors_result_filter = mysqli_query($conn, $majors_query);
                                      if ($majors_result_filter) {
                                          while($maj = mysqli_fetch_assoc($majors_result_filter)) { ?>
                                              <option value="<?= $maj['major_id'] ?>"
                                                  <?= (isset($_GET['major']) && $_GET['major'] == $maj['major_id']) ? 'selected' : '' ?>>
                                                  <?= $maj['major_name'] ?>
                                              </option>
                                          <?php } 
                                      }?>
                                  </select>
                                  <label class="font-size-13px mb-1">Year</label>
                                  <select name="year" class="form-select font-size-13px mb-3">
                                      <option value="">All</option>
                                      <?php 
                                      $years_q_filter = mysqli_query($conn, "SELECT DISTINCT academic_year FROM students");
                                      if ($years_q_filter) {
                                          while($yr = mysqli_fetch_assoc($years_q_filter)) { ?>
                                              <option value="<?= $yr['academic_year'] ?>"
                                                  <?= (isset($_GET['year']) && $_GET['year'] == $yr['academic_year']) ? 'selected' : '' ?>>
                                                  <?= $yr['academic_year'] ?>
                                              </option>
                                          <?php } 
                                      }?>
                                  </select>

                                  <input type="hidden" name="search" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                                  <input type="hidden" name="tab" value="<?= $active_tab ?>">
                                  <button class="btn btn-sm bg-color-F5F0F0 w-100 font-size-13px">Apply Filters</button>
                              </form>

                          </div>
                      </div>

                  </div>
                  
                  <ul class="nav nav-pills mb-3 mt-4 border-bottom-color-876363" id="pills-tab" role="tablist">
                      <?php 
                      $current_filters_for_links = http_build_query(array_filter($_GET, fn($key) => $key !== 'tab', ARRAY_FILTER_USE_KEY));
                      $link_separator = !empty($current_filters_for_links) ? '&' : '';
                      ?>

                      <li class="nav-item" role="presentation">
                          <a href="?tab=pills-pending<?= $link_separator . $current_filters_for_links ?>" 
                            class="nav-link <?= ($active_tab == 'pills-pending') ? 'active' : '' ?>">
                              Pending Offers
                          </a>
                      </li>
                      <li class="nav-item" role="presentation">
                          <a href="?tab=pills-home<?= $link_separator . $current_filters_for_links ?>" 
                            class="nav-link <?= ($active_tab == 'pills-home') ? 'active' : '' ?>">
                              Accepted Offers
                          </a>
                      </li>
                      <li class="nav-item" role="presentation">
                          <a href="?tab=pills-profile<?= $link_separator . $current_filters_for_links ?>" 
                            class="nav-link <?= ($active_tab == 'pills-profile') ? 'active' : '' ?>">
                              Rejected Offers
                          </a>
                      </li>
                  </ul>

<div class="tab-content" id="pills-tabContent">
    <?php 
    $tabs = [
        'pills-pending' => $pending_result,
        'pills-home'    => $accepted_result,
        'pills-profile' => $rejected_result
    ];

    foreach ($tabs as $id => $result): ?>
    <div class="tab-pane fade <?= ($active_tab == $id) ? 'show active' : '' ?>" id="<?= $id ?>" role="tabpanel">
        <table class="table mb-0 font-size-12px border-radius-top-10px mt-4 tablehead">
            <thead class="bg-color-F5F0F0">
                <tr>
                    <th class="width-30per"><div class="px-3">Student Name</div></th>
                    <th class="width-25per"><div class="px-3">Company</div></th>
                    <th class="width-25per"><div class="px-3">Position</div></th>
                    <th class="width-20per"><div class="px-3">Actions</div></th>
                </tr>
            </thead>
        </table>
        <div class="scroll-y-axis max-h-450px">
            <table class="table tablebody font-size-13px col-5-color-876363">
                <tbody>
                    <?php if ($result && mysqli_num_rows($result) > 0): 
                        while ($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td class="width-30per"><div class="px-3"><?= htmlspecialchars($row['student_name']) ?></div></td>
                            <td class="width-25per"><div class="px-3"><?= htmlspecialchars($row['company_name']) ?></div></td>
                            <td class="width-25per"><div class="px-3"><?= htmlspecialchars($row['position_title']) ?></div></td>
                            <td class="width-20per">
                                <div class="px-3">
                                    <button type="button" class="btn btn-sm color-876363" data-bs-toggle="offcanvas" data-bs-target="#viewExternal<?= $row['external_offer_id'] ?>">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <div class="offcanvas offcanvas-end width-600px" tabindex="-1" id="viewExternal<?= $row['external_offer_id'] ?>" aria-labelledby="viewExternalLabel<?= $row['external_offer_id'] ?>">
                            <div class="offcanvas-header">
                                <div class="d-flex px-4 align-items-center">
                                    <label class="font-weight-600 font-size-22px">External Offer Details</label>
                                </div>
                            </div>

                            <div class="offcanvas-body">
                                <div class="d-flex flex-column px-4">
                                    <div class="d-flex align-items-center mb-4">
                                        <img src="<?= !empty($row['profile_image']) ? htmlspecialchars($row['profile_image']) : 'img/default.png' ?>" 
                                            alt="Student" class="width-60px height-60px border-radius-50per img-cover shadow-sm">
                                        
                                        <div class="d-flex flex-column ms-3">
                                            <label class="font-size-18px font-weight-600 color-876363">
                                                <?= htmlspecialchars($row['student_name']) ?>
                                            </label>
                                            <label class="font-size-14px">
                                                Student Applicant
                                            </label>
                                        </div>
                                    </div>

                                    <div class="d-flex flex-column">
                                        <label class="font-size-13px color-876363">Company Name</label>
                                        <label class="font-size-13px mt-1 mb-3 font-weight-600"><?= htmlspecialchars($row['company_name']) ?></label>

                                        <label class="font-size-13px color-876363">Position Title</label>
                                        <label class="font-size-13px mt-1 mb-3 font-weight-600"><?= htmlspecialchars($row['position_title']) ?></label>

                                        <label class="font-size-13px color-876363">Offer Description</label>
                                        <label class="font-size-13px mt-1 mb-3 font-weight-500 text-justify"><?= nl2br(htmlspecialchars($row['offer_description'])) ?></label>

                                        <label class="font-size-13px color-876363">Submission Date</label>
                                        <label class="font-size-13px mt-1 mb-3 font-weight-600">
                                            <?= date('d/m/Y', strtotime($row['created_at'])) ?>
                                        </label>
                                    </div>

                                    <div class="my-3 d-flex justify-content-end px-0">
                                        <?php if ($row['status'] == 'Pending'): ?>
                                            <a href="process_external_offer.php?id=<?= $row['external_offer_id'] ?>&action=reject" 
                                            class="btn px-3 me-3 btn-E5E8EB font-size-12px text-dark" 
                                            onclick="return confirm('Are you sure you want to reject this external offer?')">
                                            Reject
                                            </a>
                                            
                                            <a href="process_external_offer.php?id=<?= $row['external_offer_id'] ?>&action=approve" 
                                            class="btn px-3 btn-E51A1A font-size-12px text-white">
                                            Approve Offer
                                            </a>
                                        <?php else: ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endwhile; 
                    else: ?>
                        <tr><td colspan="4" class="text-center py-4">No external offers found in this category.</td></tr>
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
<script>
        const filterBtn = document.getElementById('filterBtn');
        const filterBox = document.getElementById('filterBox');

        if (filterBtn && filterBox) {
            filterBtn.addEventListener('click', (event) => {
                event.stopPropagation();
                filterBox.style.display = filterBox.style.display === 'none' ? 'block' : 'none';
            });
            
            document.addEventListener('click', function(e) {
                if (filterBox.style.display === 'block' && 
                    !filterBtn.contains(e.target) && 
                    !filterBox.contains(e.target)) {
                    filterBox.style.display = 'none';
                }
            });
        }
    </script>
</html>
