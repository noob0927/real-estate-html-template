<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "realestate";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'];
    $price = $_POST['price'];
    $type = $_POST['type'];
    $size = $_POST['size'];
    $location = $_POST['location'];
    $features = $_POST['features'];
    $images = [];
    $uploadErrors = [];

    // Handle file uploads
    if (!empty($_FILES['images']['name'][0])) {
        $uploadDir = 'uploads/';
        foreach ($_FILES['images']['name'] as $key => $name) {
            $tmpName = $_FILES['images']['tmp_name'][$key];
            $filePath = $uploadDir . basename($name);
            if (move_uploaded_file($tmpName, $filePath)) {
                $images[] = $filePath;
            } else {
                $uploadErrors[] = "Failed to upload file: $name";
            }
        }
    }

    // Check for upload errors
    if (!empty($uploadErrors)) {
        echo json_encode(["status" => "error", "message" => implode(", ", $uploadErrors)]);
        exit;
    }

    // Convert images array to JSON for storage
    $imagesJson = json_encode($images);

    // Insert data into the database
    $stmt = $conn->prepare("INSERT INTO properties (title, price, type, size, location, features, images) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssss", $title, $price, $type, $size, $location, $features, $imagesJson);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Property added successfully!"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Error: " . $stmt->error]);
    }

    $stmt->close();
}

$conn->close();
?>
