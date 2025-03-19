<?php
session_start();
include 'config.php';
header('Content-Type: application/json');

error_reporting(E_ALL);
ini_set('display_errors', 1);

// ✅ Check database connection
if (!$conn) {
    echo json_encode(["status" => 500, "message" => "Database connection error."]);
    exit();
}

// ✅ Ensure request is POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["status" => 405, "message" => "Invalid request method."]);
    exit();
}

// ✅ Capture and sanitize input
$email = trim($_POST['email'] ?? '');
$password = trim($_POST['password'] ?? '');

if (empty($email) || empty($password)) {
    echo json_encode(["status" => 400, "message" => "Email and password are required."]);
    exit();
}

// ✅ SQL Query to get user details, including farmer and position details
$sql = "
    SELECT 
        u.id, u.name, u.email, u.password, u.profile_id AS role, 
        IF(u.profile_id = 1, f.farmer_type_id, NULL) AS farmer_type_id,
        IF(u.profile_id = 1, ft.type_name, NULL) AS farmer_type_name,
        IF(u.profile_id = 2, bm.position_id, NULL) AS position_id, 
        IF(u.profile_id = 2, p.position_name, NULL) AS position_name
    FROM users u
    LEFT JOIN farmers f ON u.id = f.user_id AND u.profile_id = 1
    LEFT JOIN farmer_types ft ON f.farmer_type_id = ft.id
    LEFT JOIN board_members bm ON u.id = bm.user_id AND u.profile_id = 2
    LEFT JOIN positions p ON bm.position_id = p.id
    WHERE u.email = ?
";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(["status" => 500, "message" => "SQL error: " . $conn->error]);
    exit();
}

// ✅ Bind and execute query
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

// ✅ Verify user exists
if ($stmt->num_rows > 0) {
    $stmt->bind_result($id, $name, $dbemail, $hashedPassword, $role, $farmerTypeId, $farmerTypeName, $positionId, $positionName);
    $stmt->fetch();

    // ✅ Verify password
    if (password_verify($password, $hashedPassword)) {
        $_SESSION['id'] = $id;
        $_SESSION['name'] = $name;
        $_SESSION['email'] = $dbemail;
        $_SESSION['role'] = $role;

        // Add extra session data based on profile type
        if ($role == 1) { // Farmer
            $_SESSION['farmer_type_id'] = $farmerTypeId;
            $_SESSION['type_name'] = $farmerTypeName;
        } elseif ($role == 2) { // Other Role with Position
            $_SESSION['position_id'] = $positionId;
            $_SESSION['position_name'] = $positionName;
        }

        // ✅ Success Response
        $response = [
            "status" => 200,
            "message" => "Login successful!",
            "user" => [
                "id" => $id,
                "name" => $name,
                "email" => $dbemail,
                "role" => $role
            ]
        ];

        // Append additional data based on role
        if ($role == 1) {
            $response['user']['farmer_type_id'] = $farmerTypeId;
            $response['user']['type_name'] = $farmerTypeName;
        } elseif ($role == 2) {
            $response['user']['position_id'] = $positionId;
            $response['user']['position_name'] = $positionName;
        }

        echo json_encode($response);
        exit();
    } else {
        echo json_encode(["status" => 401, "message" => "Incorrect password."]);
        exit();
    }
} else {
    echo json_encode(["status" => 404, "message" => "User not found."]);
    exit();
}
?>