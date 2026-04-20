<?php
$pageTitle = $pageTitle ?? 'PROJECT TRINITY';
$extraCSS = $extraCSS ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inria+Sans:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/styles.css">
    <?php if (!empty($extraCSS)): ?>
    <link rel="stylesheet" href="<?php echo htmlspecialchars($extraCSS); ?>">
    <?php endif; ?>
</head>
<body>
    <!-- Border Frame Container (Fixed) -->
    <div class="border-frame">
        <!-- Decorative corners -->
        <div class="corner corner-top-left"></div>
        <div class="corner corner-top-right"></div>
        <div class="corner corner-bottom-left"></div>
        <div class="corner corner-bottom-right"></div>
    </div>

    <div class="page-container">