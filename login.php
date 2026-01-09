<?php
session_start();
require 'db_connection.php';

$error = null; 
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $credential = trim($_POST['username']);
    $password = trim($_POST['password']);

    $query_staff = "SELECT staff_id, username, password FROM staff WHERE (email = ? OR username = ?) AND password = ?";
    $stmt = mysqli_prepare($conn, $query_staff);
    mysqli_stmt_bind_param($stmt, "sss", $credential, $credential, $password);
    mysqli_stmt_execute($stmt);
    $res_staff = mysqli_stmt_get_result($stmt);

    if ($res_staff && mysqli_num_rows($res_staff) > 0) {
        $user = mysqli_fetch_assoc($res_staff);
        $_SESSION['user_type'] = 'staff';
        $_SESSION['staff_id'] = $user['staff_id'];
        $_SESSION['username'] = $user['username']; 
        header("Location: StaffInternshipPortal/dashboard.php");
        exit();
    }

    $query_company = "SELECT * FROM companies WHERE (email = ? OR company_name = ?) AND password = ?";
    $stmt = mysqli_prepare($conn, $query_company);
    mysqli_stmt_bind_param($stmt, "sss", $credential, $credential, $password);
    mysqli_stmt_execute($stmt);
    $res_company = mysqli_stmt_get_result($stmt);

    if ($res_company && mysqli_num_rows($res_company) > 0) {
        $user = mysqli_fetch_assoc($res_company);
        
        if ($user['status'] === 'canceled') {
            $error = "Your account has been deactivated. Please contact support.";
        } else {
            $_SESSION['user_type'] = 'company';
            $_SESSION['company_id'] = $user['company_id'];
            $_SESSION['company_name'] = $user['company_name'];
            header("Location: CompanyInternshipPortal/dashboard.php");
            exit();
        }
    }

    if (!$error) {
        $query_student = "SELECT * FROM students WHERE (student_id = ? OR email = ?) AND password = ?";
        $stmt = mysqli_prepare($conn, $query_student);
        mysqli_stmt_bind_param($stmt, "sss", $credential, $credential, $password);
        mysqli_stmt_execute($stmt);
        $res_student = mysqli_stmt_get_result($stmt);

        if ($res_student && mysqli_num_rows($res_student) > 0) {
            $user = mysqli_fetch_assoc($res_student);
            $_SESSION['user_type'] = 'student';
            $_SESSION['student_id'] = $user['student_id'];
            $_SESSION['first_name'] = $user['first_name'];
            header("Location: StudentInternshipPortal/dashboard.php");
            exit();
        }
    }

    if (!$error) {
        $error = "Invalid Credentials. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en" class="h-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HTU Internship Portal | Login</title>
    <link rel="icon" type="image/x-icon" href="img/htu_logo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body class="h-100" style="overflow: hidden;"> 
    <div class="container-fluid h-100 px-0">
        <div class="row h-100 g-0">
            <div class="col-md-6 h-100">
                    <img src="img\grad.jpg" alt="" class="img-cover w-100 opacity-75" style="height: -webkit-fill-available; position: relative;">
                    <label for="" class="lable1">Welcome to Internship Portal</label>
            </div>
            
            <div class="col-md-6 d-flex justify-content-center align-items-center bg-white">
                <div class="px-5 w-100" style="max-width: 500px;">
                    <div class="text-center mb-4">
                        <img src="img/htu_logo.png" alt="HTU Logo" class="mb-3" style="width: 130px;">
                        <h2 class="font-weight-600 color-876363">Login</h2>
                    </div>

                    <form action="" method="POST" class="mt-4">
                        <div class="mb-3">
                            <label class="form-label color-5f5f5f font-size-12px">Email or Student ID</label>
                            <input type="text" name="username" class="form-control form-input shadow-none" placeholder="Enter your ID or Email" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label color-5f5f5f font-size-12px">Password</label>
                            <input type="password" name="password" class="form-control form-input shadow-none" placeholder="Enter your password" required>
                        </div>
                        
                        <div class="mb-3 text-end">
                            <a href="#" class="color-876363 font-size-12px text-decoration-none">Forgot Password?</a>
                        </div>

                        <button type="submit" class="btn w-100 bg-color-e8343f color-fff font-weight-600 border-radius-9px py-2">Login</button>
                    </form>
                    
                    <?php if($error): ?>
                        <div class="alert alert-danger font-size-12px mt-4 border-radius-9px text-center"><?= $error ?></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div> 
</body>
</html>