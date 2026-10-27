<?php

session_start();

require "../config.php";

if (
    !isset($_SESSION["admin_id"]) ||
    $_SESSION["admin_role"] !== "admin"
) {
    header("Location: login.php");
    exit;
}

$id = (int)($_GET["id"] ?? 0);

if ($id > 0) {

    $stmt = $conn->prepare("
        UPDATE businesses
        SET featured = CASE
            WHEN featured = 1 THEN 0
            ELSE 1
        END
        WHERE id = ?
    ");

    $stmt->bind_param("i", $id);
    $stmt->execute();
}

header("Location: index.php");
exit;
?>