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

$stmt = $conn->prepare("SELECT COUNT(*) AS active_count FROM offers WHERE company_id = ? AND status = 'Open'");
$stmt->bind_param("i", $company_id);
$stmt->execute();
$result = $stmt->get_result();
$active_row = $result->fetch_assoc();
$active_postings = $active_row['active_count'];
$stmt->close();

$stmt = $conn->prepare("
    SELECT COUNT(*) AS accepted_count
    FROM applications a
    JOIN offers o ON a.offer_id = o.offer_id
    WHERE o.company_id = ?
    AND a.status IN ('Interview', 'Accepted')
");
$stmt->bind_param("i", $company_id);
$stmt->execute();
$result = $stmt->get_result();
$accepted_row = $result->fetch_assoc();
$accepted_offers = $accepted_row['accepted_count'];
$stmt->close();

$stmt = $conn->prepare("
    SELECT o.offer_id, o.job_title, o.training_type, o.status,
           (SELECT COUNT(*) FROM applications a WHERE a.offer_id = o.offer_id) AS student_count
    FROM offers o
    WHERE o.company_id = ?
    ORDER BY o.created_at DESC
");
$stmt->bind_param("i", $company_id);
$stmt->execute();
$result = $stmt->get_result();
$offers = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

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
                          class="width-50px height-50px border-radius-50per img-cover" 
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
                                <a href="dashboard.php" class="btn bg-color-F5F0F0 w-100 text-start">
                                    <i class="bi bi-house-door-fill"></i>
                                    <label for="" class="ms-2">Dashboard</label>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="offers.php" class="btn w-100 text-start">
                                    <i class="bi  bi-briefcase"></i>
                                    <label for="" class="ms-2">Offers</label>
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
                <label for="" class="font-weight-600 font-size-22px">Dashboard</label>
                <label for="" class="font-weight-600 font-size-14px mt-3">Overview</label>
                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="bg-color-f4f4f4 height-120px border-radius-10px padding-x-35px py-2 d-flex flex-column">
                            <label for="" class="font-size-16px mt-3">Active postings</label>
                            <label for="" class="font-weight-700 font-size-24px mt-1 color-876363">
                                <?= htmlspecialchars($active_postings) ?>
                            </label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="bg-color-f4f4f4 height-120px border-radius-10px padding-x-35px py-2 d-flex flex-column">
                            <label for="" class="font-size-16px mt-3">Accepted Offers</label>
                            <label for="" class="font-weight-700 font-size-24px mt-1 color-876363">
                                <?= htmlspecialchars($accepted_offers) ?>
                            </label>
                        </div>
                    </div>
                </div>
                <label for="" class="font-weight-600 font-size-14px mt-3">Potential Offers Status</label>
                <table class="table mb-0 font-size-12px border-radius-top-10px mt-3 ">
                    <thead class="bg-color-F5F0F0">
                        <tr>
                            <th class="width-25per">
                                <div class="px-3">Title</div>
                            </th>
                            <th class="width-25per">
                                <div class="px-3">Training Type</div>
                            </th>
                            <th class="width-25per">
                                <div class="px-3">Students in Process</div>
                            </th>
                            <th class="width-25per">                                            
                                <div class="px-3">Status</div>
                            </th>
                        </tr>
                    </thead>
                </table>
                <div class="scroll-y-axis max-h-300px">
                    <table class="table tablebody font-size-13px">
                        <tbody>
                            <?php if (!empty($offers)): ?>
                                <?php foreach ($offers as $offer): ?>
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
                                            <div class="px-3"><?= htmlspecialchars($offer['job_title']) ?></div>
                                        </td>
                                        <td class="width-25per">
                                            <div class="px-3"><?= htmlspecialchars($offer['training_type']) ?></div>
                                        </td>
                                        <td class="width-25per">
                                            <div class="px-3"><?= htmlspecialchars($offer['student_count']) ?></div>
                                        </td>
                                        <td class="width-25per">
                                            <div class="px-3 py-1 d-flex justify-content-center width-fit border-radius-12px font-weight-500"
                                                style="background-color: <?= $bgColor ?>; color: <?= $color ?>;">
                                                <?= htmlspecialchars($status) ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center py-3 text-muted">No offers found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>

                    </table>
                </div>
              </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>