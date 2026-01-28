<?php
session_start();
require '../db_connection.php';

if (!isset($_SESSION['staff_id']) || $_SESSION['user_type'] !== 'staff') {
    header("Location: ../login.php");
    exit();
}

$company_status_filter = $_GET['status'] ?? 'active';
$search_term = $_GET['search'] ?? '';

$query = "
    SELECT 
        c.company_id,
        c.company_name,
        c.email,
        c.phone_number,
        c.location,
        c.image,
        c.company_description
    FROM companies c
    WHERE c.status = '" . mysqli_real_escape_string($conn, $company_status_filter) . "'
";

if (!empty($search_term)) {
    $search = mysqli_real_escape_string($conn, $search_term);
    $query .= " AND c.company_name LIKE '%$search%'";
}

$query .= " ORDER BY c.company_name ASC";

$companies_result = mysqli_query($conn, $query);

$current_search_param = !empty($search_term) ? '&search=' . urlencode($search_term) : '';

$current_filters = http_build_query(array_filter($_GET, fn($key) => $key !== 'status', ARRAY_FILTER_USE_KEY));
$filter_separator = !empty($current_filters) ? '&' : '';
?>
<!DOCTYPE html>
<html lang="en" class="h-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Companies</title>
    <link rel="icon" type="image/x-icon" href="img\htu_logo.png">
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
                          <a href="companies.php" class="btn w-100 text-start bg-color-F5F0F0">
                            <i class="bi bi-buildings-fill"></i>
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
                    <div class="d-flex justify-content-between">
                        <label for="" class="font-weight-600 font-size-22px">Companies</label>
                        <button type="button" class="btn font-size-12px btn-E51A1A" data-bs-toggle="offcanvas" data-bs-target="#offcanvasRight" aria-controls="offcanvasRight"><i class="bi bi-plus me-2"></i>Add New Company</button>
                        <div class="offcanvas offcanvas-end width-600px" tabindex="-1" id="offcanvasRight" aria-labelledby="offcanvasRightLabel">
                            <div class="offcanvas-header">
                                <div class="d-flex flex-column px-4">
                                    <label class="font-weight-600 font-size-22px">Register New Company</label>
                                    <label class="font-size-12px mt-1 color-876363">Create a new partner account to post training opportunities.</label>
                                </div>
                            </div>

                            <div class="offcanvas-body pt-0">
                                <form method="POST" action="add_company_process.php" enctype="multipart/form-data">
                                    <div class="px-4">
                                        
                                        <div class="row mt-3">
                                            <div class="col-md-12 mb-3">
                                                <label class="form-label font-size-13px color-876363 font-weight-600">Company Name</label>
                                                <input type="text" name="company_name" class="form-control font-size-13px" placeholder="e.g. Microsoft Jordan" required>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label font-size-13px color-876363 font-weight-600">Email Address</label>
                                                <input type="email" name="email" class="form-control font-size-13px" placeholder="company@example.com" required>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label font-size-13px color-876363 font-weight-600">Phone Number</label>
                                                <input type="text" name="phone_number" class="form-control font-size-13px" placeholder="+962..." required>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label font-size-13px color-876363 font-weight-600">Location</label>
                                                <input type="text" name="location" class="form-control font-size-13px" placeholder="Amman, Jordan" required>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label font-size-13px color-876363 font-weight-600">Initial Password</label>
                                                <input type="password" name="password" class="form-control font-size-13px" placeholder="••••••••" required>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label font-size-13px color-876363 font-weight-600">Company Description</label>
                                            <textarea name="company_description" class="form-control font-size-13px" rows="4" placeholder="Briefly describe the company's industry and focus..." required></textarea>
                                        </div>

                                        <div class="mb-4">
                                            <label class="form-label font-size-13px color-876363 font-weight-600">Company Logo (Image)</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-white"><i class="bi bi-image"></i></span>
                                                <input type="file" name="company_image" accept="image/*" class="form-control font-size-13px">
                                            </div>
                                        </div>

                                        <div class="mt-4 mb-5 d-flex justify-content-end pb-4">
                                            <button type="button" class="btn px-4 me-2 btn-E5E8EB font-size-12px" data-bs-dismiss="offcanvas">Cancel</button>
                                            <button type="submit" name="add_company" class="btn px-4 btn-E51A1A font-size-12px text-white shadow-sm">Save Company</button>
                                        </div>

                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-flex mt-3">
                        <form method="GET" class="search d-flex bg-color-F5F0F0 border-radius-9px w-100">
                            <button class="btn pe-0" type="submit">
                                <i class="bi bi-search color-876363 font-size-13px"></i>
                            </button>
                            <input 
                                class="form-control me-2 bg-color-F5F0F0 font-size-13px border-none" 
                                type="text" 
                                name="search"
                                placeholder="Search Company Name"
                                value="<?= htmlspecialchars($_GET['search'] ?? '') ?>"
                            />
                            
                            <input type="hidden" name="status" value="<?= $company_status_filter ?>">
                        </form>
                        
                        <div class="position-relative">
                            <button class="btn mx-2" type="button" id="filterBtn">
                                <i class="bi bi-funnel-fill color-876363 font-size-19px"></i>
                            </button>

                            <div id="filterBox" 
                                class="p-3 shadow border bg-white position-absolute end-0 mt-2 border-radius-10px"
                                style="width: 260px; display: none; z-index: 1000;">

                                <form method="GET">
                                    <input type="hidden" name="search" value="<?= htmlspecialchars($search_term) ?>">
                                    <input type="hidden" name="status" value="<?= $company_status_filter ?>">
                                    
                                    <label class="font-size-13px mb-1">Status (for filter consistency)</label>
                                    <select name="status" class="form-select font-size-13px mb-3">
                                        <option value="active" <?= ($company_status_filter == 'active') ? 'selected' : '' ?>>Active</option>
                                        <option value="canceled" <?= ($company_status_filter == 'canceled') ? 'selected' : '' ?>>Canceled</option>
                                    </select>
                                    
                                    <button class="btn btn-sm bg-color-F5F0F0 w-100 font-size-13px">Apply Filters</button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <ul class="nav nav-pills mb-3 mt-4 border-bottom-color-876363" id="pills-tab" role="tablist">
                        <?php 
                        $current_search_param = !empty($search_term) ? '&search=' . urlencode($search_term) : '';
                        ?>

                        <li class="nav-item" role="presentation">
                            <a href="?status=active<?= $current_search_param ?>" 
                              class="nav-link <?= ($company_status_filter == 'active') ? 'active' : '' ?>">
                                Active Companies
                            </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a href="?status=canceled<?= $current_search_param ?>" 
                              class="nav-link <?= ($company_status_filter == 'canceled') ? 'active' : '' ?>">
                                Canceled Companies
                            </a>
                        </li>
                    </ul>

                    <div class="tab-content" id="pills-tabContent">
                        <div class="tab-pane fade show active" id="companies-pane" role="tabpanel">
                            <table class="table mb-0 font-size-12px border-radius-top-10px mt-2 tablehead">
                                <thead class="bg-color-F5F0F0">
                                    <tr>
                                        <th class="width-30per"><div class="px-3">Company Name</div></th>
                                        <th class="width-25per"><div class="px-3">Email</div></th>
                                        <th class="width-25per"><div class="px-3">Location</div></th>
                                        <th class="width-20per"><div class="px-3">Actions</div></th>
                                    </tr>
                                </thead>
                            </table>
                            
                            <div class="scroll-y-axis max-h-450px">
                                <table class="table tablebody font-size-13px col-5-color-876363">
                                    <tbody>
                                        <?php 
                                        if ($companies_result && mysqli_num_rows($companies_result) > 0) {
                                            while ($row = mysqli_fetch_assoc($companies_result)) {
                                        ?>
                                        <tr>
                                            <td class="width-30per"><div class="px-3"><?= htmlspecialchars($row['company_name']) ?></div></td>
                                            <td class="width-25per"><div class="px-3"><?= htmlspecialchars($row['email']) ?></div></td>
                                            <td class="width-25per"><div class="px-3"><?= htmlspecialchars($row['location'] ?? 'N/A') ?></div></td>
                                            <td class="width-20per">
                                                <div class="px-3">
                                                    <button type="button" class="btn btn-sm color-876363" title="View" data-bs-toggle="offcanvas" data-bs-target="#viewCompany<?= $row['company_id'] ?>">
                                                        <i class="bi bi-eye"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        
                                        <div class="offcanvas offcanvas-end width-650px" tabindex="-1" id="viewCompany<?= $row['company_id'] ?>">
                                            <div class="offcanvas-body pt-0">
                                                <div class="row">
                                                    <img src="<?= htmlspecialchars($row['image'] ?? 'img/default_company.jpg') ?>" alt="Company Image" class="w-100 height-175px px-0 img-cover opacity-half">
                                                    <div class="d-flex justify-content-center flex-column align-items-center text-center">
                                                        <img src="<?= htmlspecialchars($row['image'] ?? 'img/default_company.jpg') ?>" class="px-0 border-radius-50per width-130px height-130px img-cover margin-top--60px index-one border-color-fff shadow-sm">
                                                        <label class="mt-2 font-weight-600 font-size-25px"><?= htmlspecialchars($row['company_name']) ?></label>
                                                        <label class="mt-1 font-size-12px color-5f5f5f"><?= htmlspecialchars($row['location'] ?? 'Location N/A') ?></label>
                                                    </div>
                                                </div>
                                                <div class="row mx-2 px-3 pt-2 pb-4 mt-4 bg-color-f4f4f4 border-radius-10px">
                                                    <div class="col-md-6 d-flex flex-column mt-4 text-start">
                                                        <label class="font-size-12px color-5f5f5f">Email Address</label>
                                                        <label class="mt-1 font-size-14px font-weight-500"><?= htmlspecialchars($row['email']) ?></label>
                                                    </div>
                                                    <div class="col-md-6 d-flex flex-column mt-4 text-start">
                                                        <label class="font-size-12px color-5f5f5f">Phone Number</label>
                                                        <label class="mt-1 font-size-14px font-weight-500"><?= htmlspecialchars($row['phone_number'] ?? 'N/A') ?></label>
                                                    </div>
                                                    <div class="col-md-12 d-flex flex-column mt-4 text-start">
                                                        <label class="font-size-12px color-5f5f5f">About Company</label>
                                                        <label class="mt-1 font-size-14px text-justify"><?= nl2br(htmlspecialchars($row['company_description'] ?? 'No description provided.')) ?></label>
                                                    </div>
                                                </div>

                                                <div class="mt-4 ps-4 d-flex justify-content-end">
                                                    <?php if ($company_status_filter == 'active'): ?>
                                                        <a href="update_company_status.php?id=<?= $row['company_id'] ?>&status=canceled" 
                                                        class="btn btn-E51A1A text-white font-size-12px" 
                                                        onclick="return confirm('Are you sure you want to cancel this company?')">Cancel Company</a>
                                                    <?php else: ?>
                                                        <a href="update_company_status.php?id=<?= $row['company_id'] ?>&status=active" 
                                                        class="btn btn-E51A1A font-size-12px" 
                                                        onclick="return confirm('Re-activate this company?')">Re-activate Company</a>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                        <?php
                                                }
                                            } else {
                                                echo '<tr><td colspan="4" class="text-center py-4">No companies found.</td></tr>';
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
    const filterBtn = document.getElementById('filterBtn');
    const filterBox = document.getElementById('filterBox');

    filterBtn.addEventListener('click', () => {
        filterBox.style.display = filterBox.style.display === 'none' ? 'block' : 'none';
    });

    document.addEventListener('click', function(e) {
        if (!filterBtn.contains(e.target) && !filterBox.contains(e.target)) {
            filterBox.style.display = 'none';
        }
    });
</script>
</html>