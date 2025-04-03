<?php
include 'includes/db_connect.php';

if(isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // First, get the student's image
    $sql = "SELECT Hinh FROM SinhVien WHERE MaSV = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $student = $result->fetch_assoc();
    
    // Delete related records in ChiTietDangKy and DangKy
    $sql = "DELETE ctdk FROM ChiTietDangKy ctdk 
            INNER JOIN DangKy dk ON ctdk.MaDK = dk.MaDK 
            WHERE dk.MaSV = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $id);
    $stmt->execute();
    
    // Delete from DangKy
    $sql = "DELETE FROM DangKy WHERE MaSV = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $id);
    $stmt->execute();
    
    // Delete the student
    $sql = "DELETE FROM SinhVien WHERE MaSV = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $id);
    
    if($stmt->execute()) {
        // Delete the image file if it exists
        if($student['Hinh'] && file_exists('images/' . $student['Hinh'])) {
            unlink('images/' . $student['Hinh']);
        }
    }
}

header("Location: students.php");
exit();
?> 