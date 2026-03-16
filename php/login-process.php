<?php
// login-process.php
// Handle user login form submission
 
// Start session
session_start();
 
// Include database connection
require_once 'db_connect.php';
 
// Check if form was submitted via POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}
 
// Get and sanitize form inputs
$login = trim($_POST['login'] ?? ''); // Can be username or email
$password = $_POST['password'] ?? '';
 
// Validation
$errors = [];
 
if (empty($login)) {
    $errors[] = 'Username or email is required';
}
 
if (empty($password)) {
    $errors[] = 'Password is required';
}
 
// If there are validation errors, return them
if (!empty($errors)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'errors' => $errors]);
    exit();
}
 
// Query to find user by username or email
$stmt = $conn->prepare('SELECT id, username, email, password, creator_id FROM users WHERE username = ? OR email = ?');
$stmt->bind_param('ss', $login, $login);
$stmt->execute();
$result = $stmt->get_result();
 
if ($result->num_rows === 0) {
    // User not found
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Invalid username/email or password']);
    $stmt->close();
    $conn->close();
    exit();
}
 
// Fetch user data
$user = $result->fetch_assoc();
$stmt->close();
 
// Verify password using bcrypt
if (!password_verify($password, $user['password'])) {
    // Password incorrect
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Invalid username/email or password']);
    $conn->close();
    exit();
}
 
// Password is correct - set session variables
$_SESSION['user_id'] = $user['id'];
$_SESSION['username'] = $user['username'];
$_SESSION['email'] = $user['email'];
$_SESSION['creator_id'] = $user['creator_id'];
 
http_response_code(200);
echo json_encode([
    'success' => true,
    'message' => 'Login successful',
    'redirect' => 'dashboard.html'
]);
 
$conn->close();
?>