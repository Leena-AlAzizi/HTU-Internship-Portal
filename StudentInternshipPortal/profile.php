<?php
session_start();
require '../db_connection.php';

if (!isset($_SESSION['student_id']) || $_SESSION['user_type'] !== 'student') {
    header('Location: ../login.php');
    exit();
}

$student_id = $_SESSION['student_id'];

if (isset($_POST['update_profile'])) {
    $first_name    = $_POST['first_name'];
    $last_name     = $_POST['last_name'];
    $email         = $_POST['email'];
    $phone_number  = $_POST['phone_number'];
    $academic_year = $_POST['academic_year'];
    $department_id = $_POST['department'];
    $major_id      = $_POST['major'];
    $residence     = $_POST['place_of_residence'];

    $stmt_old = $conn->prepare("SELECT profile_image, cv_file FROM students WHERE student_id = ?");
    $stmt_old->bind_param("s", $student_id);
    $stmt_old->execute();
    $result_old = $stmt_old->get_result();
    $row_old = $result_old->fetch_assoc();
    $profile_image = $row_old['profile_image']; 
    $cv_file = $row_old['cv_file']; 
    $stmt_old->close();

    if (!empty($_FILES['profile_pic']['name'])) {
        $targetDir = "../uploads/";
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }
        $fileName = time() . "_" . basename($_FILES["profile_pic"]["name"]);
        $targetFilePath = $targetDir . $fileName;

        if (move_uploaded_file($_FILES["profile_pic"]["tmp_name"], $targetFilePath)) {
            $profile_image = "uploads/" . $fileName;
        }
    }

    if (isset($_FILES['cv_file']) && $_FILES['cv_file']['error'] == 0) {
        $targetDir = "../uploads/cv/";
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        $cvFileName = uniqid() . "_" . basename($_FILES["cv_file"]["name"]);
        $cvFilePath = $targetDir . $cvFileName;

        if (move_uploaded_file($_FILES["cv_file"]["tmp_name"], $cvFilePath)) {
            $cv_file = "uploads/cv/" . $cvFileName;
        }
    }

    $stmt = $conn->prepare("
        UPDATE students 
        SET first_name=?, last_name=?, email=?, phone_number=?, academic_year=?, 
            department_id=?, major_id=?, place_of_residence=?, profile_image=?, cv_file=?
        WHERE student_id=?
    ");

    $stmt->bind_param(
        "sssssiissss", 
        $first_name, 
        $last_name, 
        $email, 
        $phone_number, 
        $academic_year, 
        $department_id, 
        $major_id, 
        $residence, 
        $profile_image, 
        $cv_file, 
        $student_id
    );

    if ($stmt->execute()) {
        header("Location: profile.php");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
}

$stmt = $conn->prepare("
    SELECT 
        s.first_name, 
        s.last_name, 
        s.profile_image, 
        s.cv_file,
        s.email, 
        s.phone_number, 
        s.academic_year, 
        s.place_of_residence, 
        s.department_id, 
        d.department_name, 
        s.major_id, 
        m.major_name
    FROM students s
    LEFT JOIN departments d ON s.department_id = d.department_id
    LEFT JOIN majors m ON s.major_id = m.major_id
    WHERE s.student_id = ?
");
$stmt->bind_param("s", $student_id);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();
$stmt->close();

$img_path = "../img/";
$profile_img_src = (!empty($student['profile_image']) && file_exists("../" . $student['profile_image'])) 
                   ? "../" . $student['profile_image'] 
                   : $img_path . "default.png";
$cv_link = (!empty($student['cv_file']) && file_exists("../" . $student['cv_file'])) ? "../" . $student['cv_file'] : null;

?>
<!DOCTYPE html>
<html lang="en" class="h-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    <link rel="icon" type="image/x-icon" href="../img/htu_logo.png">    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
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
                                <a href="dashboard.php" class="btn w-100 text-start">
                                    <i class="bi bi-house-door"></i>
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
                                <a href="profile.php" class="btn w-100 text-start bg-color-F5F0F0">
                                    <i class="bi bi-person-fill"></i>
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
                <label for="" class="font-weight-600 font-size-22px">Profile</label>
                <label for="" class="font-weight-600 font-size-17px mt-4">Documents</label>
                <div class="d-flex align-items-center py-3 px-4 mt-3 border-radius-10px bg-color-F5F0F0">
                    <div class="row w-100">
                        <div class="col-md-12 pe-0">
                            <div class="d-flex align-items-center justify-content-between mt-3">
                                <div class="d-flex align-items-center">
                                <div class="margin-end-13px p-1 border-radius-7px bg-color-B80000 font-size-23px" >
                                    <i class="bi bi-file-pdf color-fff"></i>
                                </div>
                                <div>
                                    <div class="fw-bold font-size-13px">CV</div>
                                        <div id="cv-file-name" class="font-size-12px color-876363">
                                            <?php if ($cv_link): ?>
                                                <a href="<?= $cv_link ?>" target="_blank" style="text-decoration: none; color: #5f5f5f;"><?= basename($student['cv_file']) ?></a>
                                            <?php else: ?>
                                                No file selected
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <input type="file" id="cv-file" name="cv_file" accept="application/pdf" class="d-none">
                                    <button type="button" class="btn font-size-12px bg-color-E51A1A color-fff border-radius-10px px-3 py-1 font-weight-500" onclick="document.getElementById('cv-file').click();">Upload</button>                                </div>
                            </div>
                        </div>
                    </div>
                    
                </div>
                        <div class="mt-4">
                            <div class="d-flex justify-content-between my-2">
                                <label for="" class="font-weight-600 font-size-17px ">Personal Information</label>
                                <button type="button" class="btn font-size-12px btn-E51A1A me-4" data-bs-toggle="offcanvas" data-bs-target="#offcanvasRight" aria-controls="offcanvasRight"><i class="bi bi-pen me-2"></i>Edit</button>
                                <div class="offcanvas offcanvas-end width-600px" tabindex="-1" id="offcanvasRight" aria-labelledby="offcanvasRightLabel">
                                    <div class="offcanvas-header">
                                    <div class="d-flex flex-column px-4">
                                        <label for="" class="font-weight-600 font-size-22px">Update Profile Information</label>
                                    </div>
                                    </div>
                                    <div class="offcanvas-body">
                                        <form method="POST" enctype="multipart/form-data">
                                            <div class="px-4">
                                                
                                                <div class="row">
                                                    <div class="col-md-6 mb-3">
                                                        <label class="form-label font-size-13px color-876363 font-weight-600">First Name</label>
                                                        <input type="text" name="first_name" class="form-control font-size-13px shadow-none" value="<?= htmlspecialchars($student['first_name']) ?>" required>
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <label class="form-label font-size-13px color-876363 font-weight-600">Last Name</label>
                                                        <input type="text" name="last_name" class="form-control font-size-13px shadow-none" value="<?= htmlspecialchars($student['last_name']) ?>" required>
                                                    </div>
                                                </div>

                                                <div class="row">
                                                    <div class="col-md-12 mb-3">
                                                        <label class="form-label font-size-13px color-876363 font-weight-600">Email Address</label>
                                                        <input type="email" name="email" class="form-control font-size-13px shadow-none" value="<?= htmlspecialchars($student['email']) ?>" required>
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <label class="form-label font-size-13px color-876363 font-weight-600">Phone Number</label>
                                                        <input type="text" name="phone_number" class="form-control font-size-13px shadow-none" value="<?= htmlspecialchars($student['phone_number']) ?>" required>
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <label class="form-label font-size-13px color-876363 font-weight-600">Place of Residence</label>
                                                        <input type="text" name="place_of_residence" class="form-control font-size-13px shadow-none" value="<?= htmlspecialchars($student['place_of_residence']) ?>" required>
                                                    </div>
                                                </div>

                                                <div class="row">
                                                    <div class="col-md-12 mb-3">
                                                        <label class="form-label font-size-13px color-876363 font-weight-600">Academic Year</label>
                                                        <select name="academic_year" class="form-select font-size-13px shadow-none" required>
                                                            <?php
                                                            $enumValues = str_replace("'", "", substr($conn->query("SHOW COLUMNS FROM students LIKE 'academic_year'")->fetch_assoc()['Type'], 5, -1));
                                                            foreach(explode(",", $enumValues) as $year): ?>
                                                                <option value="<?= $year ?>" <?= $year == $student['academic_year'] ? 'selected' : '' ?>><?= $year ?></option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <label class="form-label font-size-13px color-876363 font-weight-600">Department</label>
                                                        <select name="department" id="department" class="form-select font-size-13px shadow-none" required>
                                                            <?php
                                                            $dept_result = $conn->query("SELECT department_id, department_name FROM departments");
                                                            while($dept = $dept_result->fetch_assoc()): ?>
                                                                <option value="<?= $dept['department_id'] ?>" <?= $dept['department_id'] == $student['department_id'] ? 'selected' : '' ?>>
                                                                    <?= htmlspecialchars($dept['department_name']) ?>
                                                                </option>
                                                            <?php endwhile; ?>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <label class="form-label font-size-13px color-876363 font-weight-600">Major</label>
                                                        <select name="major" id="major" class="form-select font-size-13px shadow-none" required>
                                                            <?php
                                                            $major_result = $conn->query("SELECT major_id, major_name FROM majors WHERE department_id = " . (int)$student['department_id']);
                                                            while($major = $major_result->fetch_assoc()): ?>
                                                                <option value="<?= $major['major_id'] ?>" <?= $major['major_id'] == $student['major_id'] ? 'selected' : '' ?>>
                                                                    <?= htmlspecialchars($major['major_name']) ?>
                                                                </option>
                                                            <?php endwhile; ?>
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="mb-4">
                                                    <label class="form-label font-size-13px color-876363 font-weight-600">Profile Picture</label>
                                                    <div id="drop-area" class="border-radius-10px py-4 text-center" style="border: 2px dashed #E5E8EB; cursor: pointer;" onclick="selectFile()">
                                                        <p class="font-size-12px color-5f5f5f mb-0" id="drop-text">Drag & Drop image or click to browse</p>
                                                        <img id="image-preview" src="<?= !empty($student['profile_image']) ? htmlspecialchars($student['profile_image']) : '#' ?>" 
                                                            style="<?= !empty($student['profile_image']) ? 'display:block;' : 'display:none;' ?> max-width: 100px; margin: 10px auto; border-radius: 50%;">
                                                        <input type="file" id="profile-pic" name="profile_pic" accept="image/*" class="d-none" onchange="handleFileInput(event)">
                                                    </div>
                                                </div>

                                                <div class="mt-4 mb-5 d-flex justify-content-end pb-5">
                                                    <button type="button" class="btn px-4 me-2 btn-E5E8EB font-size-12px" data-bs-dismiss="offcanvas">Cancel</button>
                                                    <button type="submit" name="update_profile" class="btn px-4 btn-E51A1A font-size-12px text-white shadow-sm">Save Changes</button>
                                                </div>
                                                
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>  
                        </div>
                <div class="py-3 px-4 mt-2 border-radius-10px bg-color-F5F0F0">
                    <div class="row">
                        <div class="col-md-4 d-flex flex-column mb-3">
                            <label class="font-size-12px color-876363">Full Name</label>
                            <label for="" class="font-size-13px mt-2 font-weight-500  ">
                                <?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) ?>
                            </label>
                        </div>
                        <div class="col-md-4 d-flex flex-column mb-3">
                            <label class="font-size-12px color-876363">Email Address</label>
                            <label for="" class="font-size-13px mt-2 font-weight-500  ">
                                <?= htmlspecialchars($student['email']) ?>
                            </label>
                        </div>
                        <div class="col-md-4 d-flex flex-column mb-3">
                            <label class="font-size-12px color-876363">Phone Number</label>
                            <label for="" class="font-size-13px mt-2 font-weight-500  ">
                                <?= htmlspecialchars($student['phone_number']) ?>
                            </label>
                        </div>

                        <div class="col-md-4 d-flex flex-column mb-3">
                            <label class="font-size-12px color-876363">Department</label>
                            <label for="" class="font-size-13px mt-2 font-weight-500  ">
                                <?= htmlspecialchars($student['department_name']) ?>
                            </label>
                        </div>
                        <div class="col-md-4 d-flex flex-column mb-3">
                            <label class="font-size-12px color-876363">Major</label>
                            <label for="" class="font-size-13px mt-2 font-weight-500  ">
                                <?= htmlspecialchars($student['major_name']) ?>
                            </label>
                        </div>
                        <div class="col-md-4 d-flex flex-column mb-3">
                            <label class="font-size-12px color-876363">Academic Year</label>
                            <label for="" class="font-size-13px mt-2 font-weight-500  ">
                                <?= htmlspecialchars($student['academic_year']) ?>
                            </label>
                        </div>

                        <div class="col-md-4 d-flex flex-column">
                            <label class="font-size-12px color-876363">Place of Residence</label>
                            <label for="" class="font-size-13px mt-2 font-weight-500  ">
                                <?= htmlspecialchars($student['place_of_residence']) ?>
                            </label>
                        </div>
                    </div>
                </div>
                
              </div>
            </div>
        </div>
    </div>
</body>
<script>
    // upload cv
    document.getElementById("cv-file").addEventListener("change", function(event) {
        const file = event.target.files[0];
        if (!file) return;

        if (file.type !== "application/pdf") {
            alert("Please upload a PDF file only.");
            return;
        }

        document.getElementById("cv-file-name").textContent = file.name;

        const formData = new FormData();
        formData.append("cv_file", file);

        fetch("upload_cv.php", {
            method: "POST",
            body: formData
        })
        .then(response => response.text())
        .then(data => {
            if (data.startsWith("success:")) {
                const filePath = data.split(":")[1].trim();
                document.getElementById("cv-file-name").innerHTML = `
                    <a href="${filePath}" target="_blank" style="text-decoration:none; color:#876363;">
                        ${file.name}
                    </a>`;
            } else {
                alert("Upload failed: " + data);
            }
        })
        .catch(error => {
            alert("Error uploading file.");
            console.error(error);
        });
    });

    // Uploading a profile picture
    function selectFile() {
        document.getElementById('profile-pic').click();
    }

    function handleFileInput(event) {
        const file = event.target.files[0];
        if (file) previewImage(file);
    }

    function handleFileDrop(event) {
        event.preventDefault();
        const file = event.dataTransfer.files[0];
        if (file) {
            document.getElementById('profile-pic').files = event.dataTransfer.files;
            previewImage(file);
        }
    }

    function previewImage(file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('image-preview');
            preview.src = e.target.result;
            preview.style.display = 'block';
        }
        reader.readAsDataURL(file);
    }
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
</html>