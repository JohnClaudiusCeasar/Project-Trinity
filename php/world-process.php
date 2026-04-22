<?php
// world-process.php
// Handle world creation form submission

session_start();

require_once 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

$name        = trim($_POST['worldName'] ?? '');
$type_id    = trim($_POST['worldType'] ?? '') ?: null;
$description = trim($_POST['worldDescription'] ?? '');
$location   = trim($_POST['worldLocation'] ?? '');
$era        = trim($_POST['worldEra'] ?? '');
$government = trim($_POST['worldGovernment'] ?? '');
$population = trim($_POST['worldPopulation'] ?? '');
$language   = trim($_POST['worldLanguage'] ?? '');
$religion   = trim($_POST['worldReligion'] ?? '');
$currency  = trim($_POST['worldCurrency'] ?? '');
$tags       = trim($_POST['worldTags'] ?? '');
$currentRulersJson = $_POST['worldCurrentRulers'] ?? '';
$previousRulersJson = $_POST['worldPreviousRulers'] ?? '';

$errors = [];

if (empty($name)) {
    $errors[] = 'Name is required';
}

if (!empty($errors)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'errors' => $errors]);
    exit();
}

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare('INSERT INTO worlds
        (name, type_id, description, created_by, location, era, government, population, language, religion, currency, tags)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
    $stmt->execute([
        $name,
        $type_id,
        $description ?: null,
        $_SESSION['user_id'],
        $location ?: null,
        $era ?: null,
        $government ?: null,
        $population ?: null,
        $language ?: null,
        $religion ?: null,
        $currency ?: null,
        $tags ?: null
    ]);

    $world_id = $pdo->lastInsertId();

    if (!empty($currentRulersJson)) {
        $rulers = json_decode($currentRulersJson, true);
        if (is_array($rulers)) {
            $charStmt = $pdo->prepare('INSERT INTO character_world
                (character_id, world_id, role) VALUES (?, ?, ?)');
            foreach ($rulers as $char) {
                $charId = $char['id'] ?? null;
                $role = $char['role'] ?? 'Current Ruler';
                if ($charId) {
                    $charStmt->execute([
                        $charId,
                        $world_id,
                        $role
                    ]);
                }
            }
        }
    }

    if (!empty($previousRulersJson)) {
        $previousRulers = json_decode($previousRulersJson, true);
        if (is_array($previousRulers)) {
            $prevStmt = $pdo->prepare('INSERT INTO character_world
                (character_id, world_id, role) VALUES (?, ?, ?)');
            foreach ($previousRulers as $char) {
                $charId = $char['id'] ?? null;
                $role = $char['role'] ?? 'Former Ruler';
                if ($charId) {
                    $prevStmt->execute([
                        $charId,
                        $world_id,
                        $role
                    ]);
                }
            }
        }
    }

    $pdo->commit();

    http_response_code(201);
    echo json_encode([
        'success' => true,
        'message' => 'World created successfully',
        'redirect' => 'view'
    ]);

} catch (PDOException $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to create world: ' . $e->getMessage()]);
}
?>