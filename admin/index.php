<?php

session_start();

require "../config.php";

error_reporting(E_ALL);
ini_set('display_errors', 1);


/*
|--------------------------------------------------------------------------
| ADMIN SECURITY
|--------------------------------------------------------------------------
*/

if (
    !isset($_SESSION["admin_id"]) ||
    $_SESSION["admin_role"] !== "admin"
) {
    header("Location: login.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| BUSINESS STATISTICS
|--------------------------------------------------------------------------
*/

$totalResult = $conn->query("
    SELECT COUNT(*) AS total
    FROM businesses
");

$totalBusinesses = (int)(
    $totalResult->fetch_assoc()["total"] ?? 0
);


$pendingResult = $conn->query("
    SELECT COUNT(*) AS total
    FROM businesses
    WHERE status = 'pending'
");

$pendingBusinesses = (int)(
    $pendingResult->fetch_assoc()["total"] ?? 0
);


$approvedResult = $conn->query("
    SELECT COUNT(*) AS total
    FROM businesses
    WHERE status = 'approved'
");

$approvedBusinesses = (int)(
    $approvedResult->fetch_assoc()["total"] ?? 0
);


$rejectedResult = $conn->query("
    SELECT COUNT(*) AS total
    FROM businesses
    WHERE status = 'rejected'
");

$rejectedBusinesses = (int)(
    $rejectedResult->fetch_assoc()["total"] ?? 0
);


/*
|--------------------------------------------------------------------------
| REVIEW STATISTICS
|--------------------------------------------------------------------------
*/

$totalReviewsResult = $conn->query("
    SELECT COUNT(*) AS total
    FROM reviews
");

$totalReviews = (int)(
    $totalReviewsResult->fetch_assoc()["total"] ?? 0
);


$pendingReviewsResult = $conn->query("
    SELECT COUNT(*) AS total
    FROM reviews
    WHERE status = 'pending'
");

$pendingReviews = (int)(
    $pendingReviewsResult->fetch_assoc()["total"] ?? 0
);


$approvedReviewsResult = $conn->query("
    SELECT COUNT(*) AS total
    FROM reviews
    WHERE status = 'approved'
");

$approvedReviews = (int)(
    $approvedReviewsResult->fetch_assoc()["total"] ?? 0
);


$rejectedReviewsResult = $conn->query("
    SELECT COUNT(*) AS total
    FROM reviews
    WHERE status = 'rejected'
");

$rejectedReviews = (int)(
    $rejectedReviewsResult->fetch_assoc()["total"] ?? 0
);


/*
|--------------------------------------------------------------------------
| ALL BUSINESSES
|--------------------------------------------------------------------------
*/

$businessResult = $conn->query("
    SELECT
        businesses.*,
        categories.name AS category
    FROM businesses
    LEFT JOIN categories
        ON businesses.category_id = categories.id
    ORDER BY businesses.created_at DESC
");


/*
|--------------------------------------------------------------------------
| ALL REVIEWS
|--------------------------------------------------------------------------
*/

$reviewResult = $conn->query("
    SELECT
        reviews.id,
        reviews.rating,
        reviews.comment,
        reviews.status,
        reviews.created_at,
        businesses.business_name
    FROM reviews
    INNER JOIN businesses
        ON reviews.business_id = businesses.id
    ORDER BY reviews.created_at DESC
");

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        NaijaHub Admin Dashboard
    </title>

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f7f5;
            color: #17211b;
        }

        a {
            text-decoration: none;
        }

        .topbar {
            background: #122019;
            color: #ffffff;
            padding: 16px 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
        }

        .logo {
            font-size: 22px;
            font-weight: 800;
        }

        .logo span {
            color: #44cf7f;
        }

        .admin-info {
            display: flex;
            align-items: center;
            gap: 15px;
            font-size: 13px;
        }

        .logout {
            background: #c0392b;
            color: white;
            padding: 9px 13px;
            border-radius: 8px;
        }

        .container {
            width: min(1200px, calc(100% - 30px));
            margin: 30px auto;
        }

        .welcome {
            margin-bottom: 25px;
        }

        .welcome h1 {
            margin: 0 0 6px;
            font-size: 30px;
        }

        .welcome p {
            margin: 0;
            color: #68756d;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            margin-bottom: 28px;
        }

        .stat {
            background: white;
            border: 1px solid #dce5df;
            border-radius: 16px;
            padding: 22px;
        }

        .stat-label {
            display: block;
            color: #68756d;
            font-size: 12px;
            margin-bottom: 8px;
        }

        .stat-number {
            font-size: 30px;
            font-weight: 800;
        }

        .stat.pending .stat-number {
            color: #c68912;
        }

        .stat.approved .stat-number {
            color: #12894a;
        }

        .stat.rejected .stat-number {
            color: #c0392b;
        }

        .panel {
            background: white;
            border: 1px solid #dce5df;
            border-radius: 18px;
            overflow: hidden;
            margin-bottom: 28px;
        }

        .panel-header {
            padding: 22px;
            border-bottom: 1px solid #e7ece9;
        }

        .panel-header h2 {
            margin: 0 0 5px;
        }

        .panel-header p {
            margin: 0;
            color: #68756d;
            font-size: 13px;
        }

        .table-wrap {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 1050px;
        }

        th,
        td {
            text-align: left;
            padding: 15px 17px;
            border-bottom: 1px solid #edf1ee;
            font-size: 13px;
            vertical-align: middle;
        }

        th {
            background: #f8faf9;
            color: #657169;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: .05em;
        }

        tr:last-child td {
            border-bottom: 0;
        }

        .business-name {
            font-weight: 700;
        }

        .category {
            color: #68756d;
        }

        .badge {
            display: inline-block;
            padding: 5px 9px;
            border-radius: 99px;
            font-size: 10px;
            font-weight: 800;
        }

        .badge-pending {
            background: #fff4d8;
            color: #9b6d08;
        }

        .badge-approved {
            background: #e7f7ec;
            color: #176c38;
        }

        .badge-rejected {
            background: #ffe7e7;
            color: #a62828;
        }

        .yes {
            color: #12894a;
            font-weight: 700;
        }

        .no {
            color: #8b968f;
        }

        .actions {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
        }

        .action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 7px 9px;
            border-radius: 7px;
            font-size: 11px;
            font-weight: 700;
            color: white;
        }

        .approve {
            background: #12894a;
        }

        .reject {
            background: #c0392b;
        }

        .feature {
            background: #b27b08;
        }

        .verify {
            background: #356fb4;
        }

        .delete {
            background: #6d3c3c;
        }

        .review-star {
            color: #c68912;
            font-weight: 700;
        }

        .review-text {
            max-width: 420px;
            line-height: 1.5;
            color: #536057;
        }

        .empty {
            text-align: center;
            padding: 45px 20px;
            color: #68756d;
        }

        .section-title {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;
        }

        .count-pill {
            background: #f0f4f1;
            color: #536057;
            padding: 7px 11px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 700;
        }

        @media (max-width: 900px) {

            .stats {
                grid-template-columns: repeat(2, 1fr);
            }

            .admin-info span {
                display: none;
            }

        }

        @media (max-width: 520px) {

            .stats {
                grid-template-columns: 1fr;
            }

            .topbar {
                padding: 14px;
            }

            .container {
                width: min(100% - 20px, 1200px);
            }

        }

    </style>

</head>

<body>


<header class="topbar">

    <div class="logo">
        Naija<span>Hub</span> Admin
    </div>


    <div class="admin-info">

        <span>
            <?php
            echo htmlspecialchars(
                $_SESSION["admin_name"]
            );
            ?>
        </span>


        <a
            href="logout.php"
            class="logout"
        >
            Logout
        </a>

    </div>

</header>


<main class="container">


    <section class="welcome">

        <h1>
            Admin Dashboard
        </h1>

        <p>
            Manage NaijaHub businesses, listings and customer reviews.
        </p>

    </section>


    <!-- BUSINESS STATS -->

    <section class="stats">


        <div class="stat">

            <span class="stat-label">
                Total Businesses
            </span>

            <span class="stat-number">
                <?php echo $totalBusinesses; ?>
            </span>

        </div>


        <div class="stat pending">

            <span class="stat-label">
                Pending Businesses
            </span>

            <span class="stat-number">
                <?php echo $pendingBusinesses; ?>
            </span>

        </div>


        <div class="stat approved">

            <span class="stat-label">
                Approved Businesses
            </span>

            <span class="stat-number">
                <?php echo $approvedBusinesses; ?>
            </span>

        </div>


        <div class="stat rejected">

            <span class="stat-label">
                Rejected Businesses
            </span>

            <span class="stat-number">
                <?php echo $rejectedBusinesses; ?>
            </span>

        </div>


    </section>


    <!-- BUSINESS MANAGEMENT -->

    <section class="panel">


        <div class="panel-header">

            <div class="section-title">

                <div>

                    <h2>
                        Business Management
                    </h2>

                    <p>
                        Approve, reject, verify, feature or delete listings.
                    </p>

                </div>

                <span class="count-pill">
                    <?php echo $totalBusinesses; ?> total
                </span>

            </div>

        </div>


        <?php if ($businessResult && $businessResult->num_rows > 0): ?>


            <div class="table-wrap">

                <table>

                    <thead>

                        <tr>

                            <th>
                                Business
                            </th>

                            <th>
                                Category
                            </th>

                            <th>
                                City
                            </th>

                            <th>
                                Rating
                            </th>

                            <th>
                                Status
                            </th>

                            <th>
                                Verified
                            </th>

                            <th>
                                Featured
                            </th>

                            <th>
                                Actions
                            </th>

                        </tr>

                    </thead>


                    <tbody>


                        <?php while ($business = $businessResult->fetch_assoc()): ?>


                            <tr>


                                <td>

                                    <div class="business-name">

                                        <?php
                                        echo htmlspecialchars(
                                            $business["business_name"]
                                        );
                                        ?>

                                    </div>

                                </td>


                                <td>

                                    <span class="category">

                                        <?php
                                        echo htmlspecialchars(
                                            $business["category"] ?? "Uncategorized"
                                        );
                                        ?>

                                    </span>

                                </td>


                                <td>

                                    <?php
                                    echo htmlspecialchars(
                                        $business["city"]
                                    );
                                    ?>

                                </td>


                                <td>

                                    <span class="review-star">
                                        ★
                                    </span>

                                    <?php
                                    echo number_format(
                                        (float)$business["rating"],
                                        1
                                    );
                                    ?>

                                </td>


                                <td>


                                    <?php if ($business["status"] === "pending"): ?>

                                        <span class="badge badge-pending">
                                            Pending
                                        </span>


                                    <?php elseif ($business["status"] === "approved"): ?>

                                        <span class="badge badge-approved">
                                            Approved
                                        </span>


                                    <?php else: ?>

                                        <span class="badge badge-rejected">
                                            Rejected
                                        </span>

                                    <?php endif; ?>


                                </td>


                                <td>


                                    <?php if ((int)$business["verified"] === 1): ?>

                                        <span class="yes">
                                            ✓ Yes
                                        </span>


                                    <?php else: ?>

                                        <span class="no">
                                            No
                                        </span>

                                    <?php endif; ?>


                                </td>


                                <td>


                                    <?php if ((int)$business["featured"] === 1): ?>

                                        <span class="yes">
                                            ★ Yes
                                        </span>


                                    <?php else: ?>

                                        <span class="no">
                                            No
                                        </span>

                                    <?php endif; ?>


                                </td>


                                <td>

                                    <div class="actions">


                                        <?php if ($business["status"] !== "approved"): ?>

                                            <a
                                                href="approve.php?id=<?php echo $business["id"]; ?>"
                                                class="action approve"
                                            >
                                                Approve
                                            </a>

                                        <?php endif; ?>


                                        <?php if ($business["status"] !== "rejected"): ?>

                                            <a
                                                href="reject.php?id=<?php echo $business["id"]; ?>"
                                                class="action reject"
                                            >
                                                Reject
                                            </a>

                                        <?php endif; ?>


                                        <a
                                            href="verify.php?id=<?php echo $business["id"]; ?>"
                                            class="action verify"
                                        >
                                            <?php
                                            echo (
                                                (int)$business["verified"] === 1
                                            )
                                                ? "Unverify"
                                                : "Verify";
                                            ?>
                                        </a>


                                        <a
                                            href="feature.php?id=<?php echo $business["id"]; ?>"
                                            class="action feature"
                                        >
                                            <?php
                                            echo (
                                                (int)$business["featured"] === 1
                                            )
                                                ? "Unfeature"
                                                : "Feature";
                                            ?>
                                        </a>


                                        <a
                                            href="delete.php?id=<?php echo $business["id"]; ?>"
                                            class="action delete"
                                            onclick="return confirm('Are you sure you want to delete this business?');"
                                        >
                                            Delete
                                        </a>


                                    </div>

                                </td>


                            </tr>


                        <?php endwhile; ?>


                    </tbody>

                </table>

            </div>


        <?php else: ?>


            <div class="empty">

                No businesses have been submitted yet.

            </div>


        <?php endif; ?>


    </section>


    <!-- REVIEW STATS -->

    <section class="stats">


        <div class="stat">

            <span class="stat-label">
                Total Reviews
            </span>

            <span class="stat-number">
                <?php echo $totalReviews; ?>
            </span>

        </div>


        <div class="stat pending">

            <span class="stat-label">
                Pending Reviews
            </span>

            <span class="stat-number">
                <?php echo $pendingReviews; ?>
            </span>

        </div>


        <div class="stat approved">

            <span class="stat-label">
                Approved Reviews
            </span>

            <span class="stat-number">
                <?php echo $approvedReviews; ?>
            </span>

        </div>


        <div class="stat rejected">

            <span class="stat-label">
                Rejected Reviews
            </span>

            <span class="stat-number">
                <?php echo $rejectedReviews; ?>
            </span>

        </div>


    </section>


    <!-- REVIEW MANAGEMENT -->

    <section class="panel">


        <div class="panel-header">

            <div class="section-title">

                <div>

                    <h2>
                        Review Management
                    </h2>

                    <p>
                        Approve or reject customer reviews before they become public.
                    </p>

                </div>

                <span class="count-pill">
                    <?php echo $totalReviews; ?> total
                </span>

            </div>

        </div>


        <?php if ($reviewResult && $reviewResult->num_rows > 0): ?>


            <div class="table-wrap">

                <table>

                    <thead>

                        <tr>

                            <th>
                                Business
                            </th>

                            <th>
                                Rating
                            </th>

                            <th>
                                Review
                            </th>

                            <th>
                                Status
                            </th>

                            <th>
                                Date
                            </th>

                            <th>
                                Actions
                            </th>

                        </tr>

                    </thead>


                    <tbody>


                        <?php while ($review = $reviewResult->fetch_assoc()): ?>


                            <tr>


                                <td>

                                    <div class="business-name">

                                        <?php
                                        echo htmlspecialchars(
                                            $review["business_name"]
                                        );
                                        ?>

                                    </div>

                                </td>


                                <td>

                                    <span class="review-star">

                                        <?php
                                        echo str_repeat(
                                            "★",
                                            (int)$review["rating"]
                                        );
                                        ?>

                                    </span>

                                    <br>

                                    <?php
                                    echo (int)$review["rating"];
                                    ?>/5

                                </td>


                                <td>

                                    <div class="review-text">

                                        <?php
                                        echo nl2br(
                                            htmlspecialchars(
                                                $review["comment"]
                                            )
                                        );
                                        ?>

                                    </div>

                                </td>


                                <td>


                                    <?php if ($review["status"] === "pending"): ?>

                                        <span class="badge badge-pending">
                                            Pending
                                        </span>


                                    <?php elseif ($review["status"] === "approved"): ?>

                                        <span class="badge badge-approved">
                                            Approved
                                        </span>


                                    <?php else: ?>

                                        <span class="badge badge-rejected">
                                            Rejected
                                        </span>

                                    <?php endif; ?>


                                </td>


                                <td>

                                    <?php
                                    echo htmlspecialchars(
                                        $review["created_at"]
                                    );
                                    ?>

                                </td>


                                <td>

                                    <div class="actions">


                                        <?php if ($review["status"] !== "approved"): ?>

                                            <a
                                                href="review-action.php?id=<?php echo $review["id"]; ?>&action=approve"
                                                class="action approve"
                                            >
                                                Approve
                                            </a>

                                        <?php endif; ?>


                                        <?php if ($review["status"] !== "rejected"): ?>

                                            <a
                                                href="review-action.php?id=<?php echo $review["id"]; ?>&action=reject"
                                                class="action reject"
                                            >
                                                Reject
                                            </a>

                                        <?php endif; ?>


                                    </div>

                                </td>


                            </tr>


                        <?php endwhile; ?>


                    </tbody>

                </table>

            </div>


        <?php else: ?>


            <div class="empty">

                No reviews have been submitted yet.

            </div>


        <?php endif; ?>


    </section>


</main>


</body>

</html>