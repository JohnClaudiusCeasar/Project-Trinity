<?php
$extraJS = $extraJS ?? '';
?>
    </div>

    <script src="js/script.js"></script>
    <?php if (!empty($extraJS)): ?>
    <script src="<?php echo htmlspecialchars($extraJS); ?>"></script>
    <?php endif; ?>
</body>
</html>