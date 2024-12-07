<?php 
include '..\src\conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $supplier_id = $_POST['supplier_id'];
    $supplier_name = $_POST['supplier_name'];
    $contact_number = $_POST['contact_number'];
    $email = $_POST['email'];
    $address = $_POST['address'];
    $lead_time = $_POST['lead_time'];

    $sql = "UPDATE suppliers SET SupplierName = ?, ContactNumber = ?, Email = ?, Address = ?, LeadTime = ? WHERE SupplierID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssii", $supplier_name, $contact_number, $email, $address, $lead_time, $supplier_id);
    $stmt->execute();

    header("Location: ../src/supplier_list.php");
    exit();
}

?>  