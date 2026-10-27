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

$reviewId = (int)($_GET["id"] ?? 0);
$action = $_GET["action"] ?? "";

if ($reviewId <= 0) {
    header("Location: index.php");
    exit;
}

if ($action === "approve") {

    $stmt = $conn->prepare("
        UPDATE reviews
        SET status = 'approved'
        WHERE id = ?
    ");

    $stmt->bind_param("i", $reviewId);
    $stmt->execute();

} elseif ($action === "reject") {

    $stmt = $conn->prepare("
        UPDATE reviews
        SET status = 'rejected'
        WHERE id = ?
    ");

    $stmt->bind_param("i", $reviewId);
    $stmt->execute();

}

header("Location: index.php");
exit;

?>