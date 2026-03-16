<?php
// register-process.php
// Handle user registration form submission

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
$username = trim($_POST['username'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

// Validation
$errors = [];

// Validate username
if (empty($username)) {
    $errors[] = 'Username is required';
} elseif (strlen($username) < 3) {
    $errors[] = 'Username must be at least 3 characters';
} elseif (strlen($username) > 255) {
    $errors[] = 'Username must not exceed 255 characters';
} elseif (!preg_match('/^[a-zA-Z0-9_-]+$/', $username)) {
    $errors[] = 'Username can only contain letters, numbers, hyphens, and underscores';
}

// Validate email
if (empty($email)) {
    $errors[] = 'Email is required';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Invalid email format';
}

// Validate password
if (empty($password)) {
    $errors[] = 'Password is required';
} elseif (strlen($password) < 8) {
    $errors[] = 'Password must be at least 8 characters';
}

// Check if passwords match
if ($password !== $confirm_password) {
    $errors[] = 'Passwords do not match';
}

// If there are validation errors, return them
if (!empty($errors)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'errors' => $errors]);
    exit();
}

// Check if username already exists
$stmt = $conn->prepare('SELECT id FROM users WHERE username = ?');
$stmt->bind_param('s', $username);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    http_response_code(409);
    echo json_encode(['success' => false, 'message' => 'Username already exists']);
    $stmt->close();
    $conn->close();
    exit();
}
$stmt->close();

// Check if email already exists
$stmt = $conn->prepare('SELECT id FROM users WHERE email = ?');
$stmt->bind_param('s', $email);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    http_response_code(409);
    echo json_encode(['success' => false, 'message' => 'Email already exists']);
    $stmt->close();
    $conn->close();
    exit();
}
$stmt->close();

// Generate random creator_id (8 alphanumeric characters)
$creator_id = bin2hex(random_bytes(4)); // Generates 8 character hex string

// Hash the password using bcrypt
$hashed_password = password_hash($password, PASSWORD_BCRYPT);

// Insert user into database
$stmt = $conn->prepare('INSERT INTO users (username, email, password, creator_id) VALUES (?, ?, ?, ?)');
$stmt->bind_param('ssss', $username, $email, $hashed_password, $creator_id);

if ($stmt->execute()) {
    // Registration successful
    $user_id = $stmt->insert_id;
    
    // Set session variables
    $_SESSION['user_id'] = $user_id;
    $_SESSION['username'] = $username;
    $_SESSION['creator_id'] = $creator_id;
    
    http_response_code(201);
    echo json_encode([
        'success' => true,
        'message' => 'Registration successful',
        'redirect' => 'login.html'
    ]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Registration failed: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>