<?php
session_start();
require '../db_connection.php';

if (!isset($_SESSION['company_id']) || $_SESSION['user_type'] !== 'company') {
    header("Location: ../login.php");
    exit();
}

$company_id = $_SESSION['company_id'];
$stmt = $conn->prepare("SELECT company_name, email, phone_number, location, company_description, image FROM companies WHERE company_id = ?");
$stmt->bind_param("i", $company_id);
$stmt->execute();
$result = $stmt->get_result();
$company = $result->fetch_assoc();
$stmt->close();

$company_name = htmlspecialchars($company['company_name']);
$company_email = htmlspecialchars($company['email']);
$company_phone = htmlspecialchars($company['phone_number'] ?? 'N/A'); 
$company_location = htmlspecialchars($company['location'] ?? 'N/A');
$company_description = htmlspecialchars($company['company_description'] ?? 'No description provided.');

$company_image = (!empty($company['image']) && file_exists("../" . $company['image'])) 
                 ? "../" . $company['image'] 
                 : '../img/default_company.png';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
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
                                <a href="profile.php" class="btn w-100 text-start bg-color-F5F0F0">
                                    <i class="bi bi-person-fill"></i>
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
                    <label for="" class="font-weight-600 font-size-22px">Profile</label>
                  </div>
                        <div class="mt-4">
                            <div class="d-flex justify-content-between my-2">
                                <label for="" class="font-weight-600 font-size-17px ">Company Information</label>
                                <button type="button" class="btn font-size-12px btn-E51A1A h-100" data-bs-toggle="offcanvas" data-bs-target="#offcanvasRight" aria-controls="offcanvasRight"><i class="bi bi-pen me-2"></i>Edit</button>
                    
                                <div class="offcanvas offcanvas-end width-600px" tabindex="-1" id="offcanvasRight" aria-labelledby="offcanvasRightLabel">
                                <div class="offcanvas-header">
                                    <div class="d-flex flex-column px-4">
                                    <label for="" class="font-weight-600 font-size-22px">Update Profile Information</label>
                                    </div>
                                </div>
                                <div class="offcanvas-body">
                                    <form action="update_profile.php" method="POST" enctype="multipart/form-data">
                                        <input type="hidden" name="company_id" value="<?= $company_id ?>">
                                        <div class="px-4">
                                            
                                            <div class="row">
                                                <div class="col-md-12 mb-3">
                                                    <label for="company_name" class="form-label font-size-13px color-876363 font-weight-600">Company Name</label>
                                                    <input type="text" id="company_name" name="company_name" class="form-control font-size-13px" value="<?= $company_name ?>" required>
                                                </div>
                                                <div class="col-md-12 mb-3">
                                                    <label for="email" class="form-label font-size-13px color-876363 font-weight-600">Email Address</label>
                                                    <input type="email" id="email" name="email" class="form-control font-size-13px" value="<?= $company_email ?>" required>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label for="phone_number" class="form-label font-size-13px color-876363 font-weight-600">Phone Number</label>
                                                    <input type="text" id="phone_number" name="phone_number" class="form-control font-size-13px" value="<?= $company_phone ?>">
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="location" class="form-label font-size-13px color-876363 font-weight-600">Location</label>
                                                    <input type="text" id="location" name="location" class="form-control font-size-13px" value="<?= $company_location ?>">
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <label for="company_description" class="form-label font-size-13px color-876363 font-weight-600">About Company</label>
                                                <textarea id="company_description" name="company_description" class="form-control font-size-13px" rows="4"><?= $company_description ?></textarea>
                                            </div>
                                            <div class="mb-4">
                                                <label class="form-label font-size-13px color-876363 font-weight-600">Profile Picture / Logo</label>
                                                <div class="drop-area-custom py-4" id="drop-area" onclick="selectFile()" 
                                                    ondrop="handleFileDrop(event)" ondragover="event.preventDefault()"
                                                    style="border: 2px dashed #E5E8EB; border-radius: 10px; cursor: pointer; text-align: center;">
                                                    
                                                    <?php if (!empty($company['image']) && $company['image'] != 'img/default_company.png'): ?>
                                                        <p style="display: none;" id="drop-text" class="font-size-12px color-5f5f5f">Drag & Drop or Click to change</p>
                                                        <img id="image-preview" src="<?= $company_image ?>" alt="Preview" style="max-width: 100%; max-height: 150px; border-radius: 8px;">
                                                    <?php else: ?>
                                                        <p id="drop-text" class="font-size-12px color-5f5f5f">Drag & Drop an image here, or click to select one</p>
                                                        <img id="image-preview" src="#" alt="Preview" style="display: none; max-width: 100%; max-height: 150px; border-radius: 8px;">
                                                    <?php endif; ?>

                                                    <input type="file" id="profile-pic" name="profile_pic" accept="image/*" class="d-none" onchange="handleFileInput(event)">
                                                </div>
                                            </div>

                                            <div class="mt-4 mb-5 d-flex justify-content-end pb-4">
                                                <button type="button" class="btn px-4 me-2 btn-E5E8EB font-size-12px" data-bs-dismiss="offcanvas">Cancel</button>
                                                <button type="submit" class="btn px-4 btn-E51A1A font-size-12px text-white shadow-sm">Save Changes</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                                </div>
                            </div>
                        </div>
                  <div class="bg-color-f4f4f4 py-3 px-5 mt-3 border-radius-10px">
                    <div class="row">
                        <div class="col-md-6 d-flex flex-column">
                            <label for="" class="color-5f5f5f  font-size-12px mt-3 ">Company Name</label>
                            <label for="" class="font-size-13px mt-2 font-weight-500"><?= $company_name ?></label>
                        </div>
                        <div class="col-md-6 d-flex flex-column">
                            <label for="" class="color-5f5f5f  font-size-12px mt-3 ">Email</label>
                            <label for="" class="font-size-13px mt-2 font-weight-500"><?= $company_email ?></label>
                        </div>
                        <div class="col-md-6 d-flex flex-column">
                            <label for="" class="color-5f5f5f  font-size-12px mt-3 ">Phone Number</label>
                            <label for="" class="font-size-13px mt-2  width-70per font-weight-500"><?= $company_phone ?></label>
                        </div>
                        <div class="col-md-6 d-flex flex-column">
                            <label for="" class="color-5f5f5f  font-size-12px mt-3 ">Location</label>
                            <label for="" class="font-size-13px mt-2  width-70per font-weight-500"><?= $company_location ?></label>
                        </div>
                        <div class="col-md-12 d-flex flex-column">
                            <label for="" class="color-5f5f5f  font-size-12px mt-3 ">About Company</label>
                            <label for="" class="font-size-13px mt-2  width-70per font-weight-500"><?= $company_description ?></label>
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
    const dropArea = document.getElementById('drop-area');
    const fileInput = document.getElementById('profile-pic');
    const imagePreview = document.getElementById('image-preview');
    const dropText = document.getElementById('drop-text');

    function selectFile() {
        fileInput.click();
    }

    function handleFileInput(event) {
        const file = event.target.files[0];
        previewFile(file);
    }

    function handleFileDrop(event) {
        event.preventDefault();
        const file = event.dataTransfer.files[0];
        fileInput.files = event.dataTransfer.files; 
        previewFile(file);
    }

    function previewFile(file) {
        if (file && file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function(e) {
                imagePreview.src = e.target.result;
                imagePreview.style.display = 'block';
                if (dropText) {
                    dropText.style.display = 'none';
                }
            };
            reader.readAsDataURL(file);
        } else {
            imagePreview.style.display = 'none';
            imagePreview.src = '#';
            if (dropText) {
                dropText.style.display = 'block';
            }
        }
    }
    
    window.onload = function() {
        if (imagePreview.src && imagePreview.src !== window.location.href + '#') {
            if (dropText) {
                 dropText.style.display = 'none';
            }
            imagePreview.style.display = 'block';
        }
    }
</script>
</html>