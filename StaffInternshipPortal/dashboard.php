<?php
session_start();
require '../db_connection.php';

if (!isset($_SESSION['staff_id']) || $_SESSION['user_type'] !== 'staff') { 
    header("Location: ../login.php"); 
    exit();
}

$deadline_key = 'INTERNSHIP_DEADLINE';
$deadline_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['set_deadline'])) {
    $new_deadline = $_POST['deadline_date'];
    
    $stmt = $conn->prepare("
        INSERT INTO semester_settings (setting_key, setting_value) 
        VALUES (?, ?)
        ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)
    ");
    $stmt->bind_param("ss", $deadline_key, $new_deadline);
    
    if ($stmt->execute()) {
        $deadline_message = "Deadline saved successfully!";
    } else {
        $deadline_message = "Error saving deadline: " . $conn->error;
    }
    $stmt->close();
}

$current_deadline = null;
$stmt = $conn->prepare("SELECT setting_value FROM semester_settings WHERE setting_key = ?");
$stmt->bind_param("s", $deadline_key);
$stmt->execute();
$result_deadline = $stmt->get_result();
if ($row = $result_deadline->fetch_assoc()) {
    $current_deadline = $row['setting_value'];
}
$stmt->close();

$query_no_offer = "
    SELECT COUNT(*) AS total
    FROM students 
    WHERE student_id NOT IN (
        SELECT DISTINCT student_id FROM applications
    )
";
$result1 = $conn->query($query_no_offer);
$students_without_offers = $result1->fetch_assoc()['total'];

$query_with_offer = "
    SELECT COUNT(DISTINCT student_id) AS total 
    FROM applications
";
$result2 = $conn->query($query_with_offer);
$students_with_offers = $result2->fetch_assoc()['total'];

$query_recent_offers = "
    SELECT offers.job_title, offers.location, offers.status,
           companies.company_name
    FROM offers
    JOIN companies ON offers.company_id = companies.company_id
    ORDER BY offers.offer_id DESC
    LIMIT 10
";
$recent_offers = $conn->query($query_recent_offers);

// Top Companies by Offer Count 
$query_top_companies = "
    SELECT 
        c.company_name, 
        COUNT(o.offer_id) AS offer_count
    FROM companies c
    JOIN offers o ON c.company_id = o.company_id
    GROUP BY c.company_name
    ORDER BY offer_count DESC
    LIMIT 5
";
$top_companies_result = $conn->query($query_top_companies);

//JS
$chart_labels = [];
$chart_data = [];

if ($top_companies_result) {
    while ($row = $top_companies_result->fetch_assoc()) {
        $chart_labels[] = htmlspecialchars($row['company_name']);
        $chart_data[] = $row['offer_count'];
    }
}

$query_total_students = "SELECT COUNT(*) AS total FROM students";
$total_students = $conn->query($query_total_students)->fetch_assoc()['total'];

// 2. Students who Secured an Internship (Placed/Accepted)
$query_placed = "SELECT COUNT(DISTINCT student_id) AS placed_count FROM applications WHERE status = 'Accepted'";
$placed_count = $conn->query($query_placed)->fetch_assoc()['placed_count'];

// 3. Students who Applied (Total students with applications)
$query_applied = "SELECT COUNT(DISTINCT student_id) AS applied_count FROM applications";
$applied_count = $conn->query($query_applied)->fetch_assoc()['applied_count'];

// 4. Calculate Derived Categories
$looking_count = $total_students - $applied_count; // Students with NO applications
$in_process_count = $applied_count - $placed_count; // Students who applied but are NOT accepted (In Process/Rejected)

// Prepare Data for Chart.js
$status_labels = ['Placed (Accepted)', 'In Process/Other Applied', 'Looking (No Application)'];
$status_data = [$placed_count, $in_process_count, $looking_count];
$status_colors = ['#042b67', '#2255a4', '#b7d4ff'];
?>
<!DOCTYPE html>
<html lang="en" class="h-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
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
                          <a href="dashboard.php" class="btn bg-color-F5F0F0 w-100 text-start">
                            <i class="bi bi-house-door-fill"></i>
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
                <label class="font-weight-600 font-size-22px">Dashboard</label>
                <label class="font-size-12px mt-3 color-5f5f5f">
                  Overview of all internships, applications, and student placements.
                </label>

                    <?php if ($deadline_message): ?>
                        <div class="alert alert-success mt-3 font-size-12px" role="alert">
                            <?= $deadline_message ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <div class="card p-4 border-radius-10px border-color-876363">
                                <h5 class="font-size-16px font-weight-600 color-876363 mb-3"><i class="bi bi-calendar3 me-1"></i> Set Internship Deadline</h5>
                                <form method="POST">
                                    <div class="row align-items-end">
                                        <div class="col-sm-6 mb-3 mb-sm-0">
                                            <label for="deadline_date" class="form-label font-size-13px">End Date for Finding Opportunity</label>
                                            <input type="date" name="deadline_date" id="deadline_date" class="form-control form-control-sm font-size-13px" 
                                                   value="<?= htmlspecialchars($current_deadline ?? '') ?>" required>
                                            <?php if ($current_deadline): ?>
                                                <small class="text-muted font-size-11px mt-1 d-block">Current Deadline: <?= date('F jS, Y', strtotime($current_deadline)) ?></small>
                                            <?php endif; ?>
                                        </div>
                                        <div class="col-sm-12 mt-2">
                                            <button type="submit" name="set_deadline" class="btn btn-876363 width-fit font-size-12px">
                                                Save/Update Deadline
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                <!-- STATISTICS 
                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="bg-color-F5F0F0 height-120px border-radius-10px padding-x-32px py-2 d-flex flex-column">
                            <label class="font-size-15px mt-3 font-weight-600">Students without Offers</label>
                            <label class="font-weight-700 font-size-24px mt-1"><?= $students_without_offers ?></label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="bg-color-F5F0F0 height-120px border-radius-10px padding-x-32px py-2 d-flex flex-column">
                            <label class="font-size-15px mt-3 font-weight-600">Students with Offers</label>
                            <label class="font-weight-700 font-size-24px mt-1"><?= $students_with_offers ?></label>
                        </div>
                    </div>
                </div>-->

                <!-- RECENT OFFERS TABLE -->
                <label class="font-weight-600 font-size-16px mt-3">Recent Offers</label>
                <table class="table mb-0 font-size-12px border-radius-top-10px mt-3">
                    <thead class="bg-color-F5F0F0">
                        <tr>
                            <th class="px-3 width-30per">Title</th>
                            <th class="px-3 width-25per">Company</th>
                            <th class="px-3 width-25per">Location</th>
                            <th class="px-4 width-20per "> Status</th>
                        </tr>
                    </thead>
                </table>

                <div class="scroll-y-axis max-h-250px">
                    <table class="table font-size-13px">
                        <tbody>
                            <?php while($row = $recent_offers->fetch_assoc()): ?>
                              <?php
                                $status = $row['status'];
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
                              <td class="px-3 width-30per"><?= $row['job_title'] ?></td>
                              <td class="px-3 width-25per"><?= $row['company_name'] ?></td>
                              <td class="px-3 width-25per"><?= $row['location'] ?></td>
                              <td class="px-3 width-20per">
                                <div class="px-3 py-1 d-flex justify-content-center width-fit border-radius-12px font-weight-500 " style="background-color: <?= $bgColor ?>; color: <?= $color ?>;">
                                    <?= htmlspecialchars($row['status']); ?>
                                </div>
                              </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>

                <label class="font-weight-600 font-size-16px mt-4">General statistics</label>

                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="card p-4 border-radius-10px border-color-876363 ">
                            <canvas id="topCompaniesChart"></canvas>
                        </div>
                    </div>
                    <div class="col-md-6">
                      <div class="card p-5 height-320px border-radius-10px border-color-876363 ">
                            <label class="font-size-12px font-weight-500 color-5f5f5f mb-2">Overall Student Status (Total: <?= $total_students ?>)</label>
                            <canvas id="statusDistributionChart"></canvas>
                    </div>
                </div>

              </div>
            </div>

        </div>
    </div>
</body>
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js"></script>
<script>
    const ctx = document.getElementById('topCompaniesChart');

    const labels = <?= json_encode($chart_labels) ?>;
    const data = <?= json_encode($chart_data) ?>;

    if (ctx && data.length > 0) {
        new Chart(ctx, {
            type: 'bar', //'bar', 'pie', 'doughnut'
            data: {
                labels: labels,
                datasets: [{
                    label: 'Number of Offers',
                    data: data,
                    backgroundColor: [
                        'rgba(4, 43, 103, 0.9)', 
                        'rgba(34, 85, 164, 0.8)',
                        'rgb(183, 212, 255, 0.9)',
                        'rgba(200, 200, 200, 0.7)',
                        'rgba(220, 220, 220, 0.6)'
                    ],
                    borderColor: 'rgba(183, 212, 255,.1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Offers Count'
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    title: {
                        display: true,
                        text: 'Top 5 Most Active Companies'
                    }
                }
            }
        });
    }
</script>
<script>

    // --- Student Status Distribution Chart ---
    const statusCtx = document.getElementById('statusDistributionChart');

    const statusLabels = <?= json_encode($status_labels) ?>;
    const statusData = <?= json_encode($status_data) ?>;
    const statusColors = <?= json_encode($status_colors) ?>;

    if (statusCtx && statusData.reduce((a, b) => a + b, 0) > 0) { 
        new Chart(statusCtx, {
            type: 'pie',
            data: {
                labels: statusLabels,
                datasets: [{
                    label: 'Number of Students',
                    data: statusData,
                    backgroundColor: statusColors,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            usePointStyle: true, 
                            pointStyle: 'circle',
                            font: {
                                size: 11
                            }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const value = context.parsed;
                                const percentage = ((value / total) * 100).toFixed(1) + '%';
                                return context.label + ': ' + value + ' (' + percentage + ')';
                            }
                        }
                    }
                }
            }
        });
    }
</script>
</html>
