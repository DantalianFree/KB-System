<?php 
include '..\src\conn.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $supplierName = $_POST['supplier_name'];
    $contactNumber = $_POST['contact_number'];
    $email = $_POST['email'];
    $address = $_POST['address'];
    $leadTime = $_POST['lead_time'];

    $stmt = $conn->prepare("INSERT INTO supplier (SupplierName, ContactNumber, Email, `Address`, LeadTime) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssi", $supplierName, $contactNumber, $email, $address, $leadTime);
    $stmt->execute();

    header("Location: ../src/supplier_list.php");
    exit();
}
?>
