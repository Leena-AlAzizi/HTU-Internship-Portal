<?php
session_start();
require '../db_connection.php';

if (!isset($_SESSION['company_id']) || $_SESSION['user_type'] !== 'company') {
    header("Location: ../login.php");
    exit();
}

if (isset($_POST['update_offer'])) {
    $company_id = $_SESSION['company_id'];
    $offer_id = (int)$_POST['offer_id'];
    
    $job_title = mysqli_real_escape_string($conn, $_POST['job_title']);
    $location = mysqli_real_escape_string($conn, $_POST['location']);
    $training_duration = mysqli_real_escape_string($conn, $_POST['training_duration']);
    $training_type = mysqli_real_escape_string($conn, $_POST['training_type']);
    $salary = (int)$_POST['salary']; 
    $max_applicants = (int)$_POST['max_applicants'];
    $application_deadline = $_POST['application_deadline'];
    $about_internship = mysqli_real_escape_string($conn, $_POST['about_internship']);
    $responsibilities = mysqli_real_escape_string($conn, $_POST['responsibilities']);
    $requirements = mysqli_real_escape_string($conn, $_POST['requirements']);

    $query = "UPDATE offers SET 
                job_title = '$job_title', 
                location = '$location', 
                training_duration = '$training_duration', 
                training_type = '$training_type', 
                salary = $salary, 
                max_applicants = $max_applicants, 
                application_deadline = '$application_deadline', 
                about_internship = '$about_internship', 
                responsibilities = '$responsibilities', 
                requirements = '$requirements' 
              WHERE offer_id = $offer_id AND company_id = $company_id";

    if (mysqli_query($conn, $query)) {
        header("Location: offers.php?updated=1");
        exit();
    } else {
        echo "Error updating record: " . mysqli_error($conn);
    }
}