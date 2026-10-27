<?php

require "config.php";

error_reporting(E_ALL);
ini_set('display_errors', 1);


/*
|--------------------------------------------------------------------------
| GET BUSINESS
|--------------------------------------------------------------------------
*/

$slug = trim($_GET["slug"] ?? "");

if ($slug === "") {
    header("Location: index.php");
    exit;
}


$stmt = $conn->prepare("
    SELECT
        businesses.*,
        categories.name AS category
    FROM businesses
    INNER JOIN categories
        ON businesses.category_id = categories.id
    WHERE businesses.slug = ?
      AND businesses.status = 'approved'
    LIMIT 1
");

$stmt->bind_param("s", $slug);
$stmt->execute();

$result = $stmt->get_result();

$business = $result->fetch_assoc();


if (!$business) {

    http_response_code(404);

    die("Business not found.");

}


/*
|--------------------------------------------------------------------------
| GET APPROVED REVIEWS
|--------------------------------------------------------------------------
*/

$reviewStmt = $conn->prepare("
    SELECT
        id,
        rating,
        comment,
        created_at
    FROM reviews
    WHERE business_id = ?
      AND status = 'approved'
    ORDER BY created_at DESC
");

$reviewStmt->bind_param(
    "i",
    $business["id"]
);

$reviewStmt->execute();

$reviewResult =
    $reviewStmt->get_result();


$reviews = [];

$ratingTotal = 0;

$reviewCount = 0;


while ($review = $reviewResult->fetch_assoc()) {

    $reviews[] = $review;

    $ratingTotal += (int)$review["rating"];

    $reviewCount++;

}


/*
|--------------------------------------------------------------------------
| CALCULATE RATING
|--------------------------------------------------------------------------
*/

if ($reviewCount > 0) {

    $averageRating =
        round(
            $ratingTotal / $reviewCount,
            1
        );

} else {

    $averageRating =
        (float)$business["rating"];

}


/*
|--------------------------------------------------------------------------
| CONTACT DATA
|--------------------------------------------------------------------------
*/

$whatsapp = preg_replace(
    "/[^0-9]/",
    "",
    $business["whatsapp"] ?: $business["phone"]
);

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
        <?php
        echo htmlspecialchars(
            $business["business_name"]
        );
        ?>
        — NaijaHub
    </title>

    <link
        rel="stylesheet"
        href="assets/style.css"
    >


    <style>

        .profile-page {
            padding: 60px 0 80px;
            background: #f5f8f6;
            min-height: calc(100vh - 76px);
        }

        .profile {
            background: #ffffff;
            border: 1px solid #dce5df;
            border-radius: 24px;
            overflow: hidden;
            box-shadow:
                0 20px 60px rgba(20,47,32,.08);
        }

        .profile-cover {
            height: 240px;
            background:
                linear-gradient(
                    135deg,
                    #d8efe0,
                    #8bbf9d
                );
            display: grid;
            place-items: center;
        }

        .profile-letter {
            font-size: 100px;
            font-weight: 800;
            color: rgba(255,255,255,.7);
        }

        .profile-content {
            padding: 35px;
        }

        .profile-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 20px;
        }

        .profile-category {
            color: #12894a;
            font-weight: 700;
            font-size: 14px;
        }

        .profile-title {
            margin: 7px 0;
            font-size: 40px;
            line-height: 1.1;
        }

        .verified {
            color: #12894a;
            font-size: 13px;
            font-weight: 700;
        }

        .profile-description {
            max-width: 800px;
            color: #657169;
            line-height: 1.7;
            margin: 20px 0;
        }

        .rating-summary {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
            margin: 20px 0;
        }

        .rating-number {
            font-size: 26px;
            font-weight: 800;
        }

        .stars {
            color: #d29815;
            letter-spacing: 2px;
        }

        .review-count {
            color: #68756d;
            font-size: 13px;
        }

        .profile-meta {
            display: grid;
            grid-template-columns:
                repeat(3, 1fr);
            gap: 14px;
            margin: 25px 0;
        }

        .meta-box {
            border: 1px solid #dce5df;
            border-radius: 14px;
            padding: 16px;
        }

        .meta-box small {
            display: block;
            color: #68756d;
            margin-bottom: 6px;
        }

        .meta-box strong {
            display: block;
            line-height: 1.4;
        }

        .profile-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .profile-btn {
            display: inline-flex;
            justify-content: center;
            align-items: center;
            padding: 12px 17px;
            border-radius: 11px;
            text-decoration: none;
            font-weight: 700;
            background: #12894a;
            color: #ffffff;
        }

        .profile-btn.secondary {
            background: #17211b;
        }

        .reviews-section {
            margin-top: 30px;
        }

        .reviews-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            margin-bottom: 15px;
        }

        .reviews-header h2 {
            margin: 0;
        }

        .review-card {
            background: #ffffff;
            border: 1px solid #dce5df;
            border-radius: 16px;
            padding: 18px;
            margin-bottom: 12px;
        }

        .review-top {
            display: flex;
            justify-content: space-between;
            gap: 15px;
            margin-bottom: 8px;
        }

        .review-stars {
            color: #d29815;
            letter-spacing: 1px;
        }

        .review-date {
            color: #7a857e;
            font-size: 12px;
        }

        .review-comment {
            margin: 0;
            color: #536057;
            line-height: 1.6;
        }

        .empty-reviews {
            background: #ffffff;
            border: 1px solid #dce5df;
            border-radius: 16px;
            padding: 25px;
            color: #68756d;
        }

        .back-link {
            display: inline-block;
            margin-bottom: 18px;
            color: #12894a;
            font-size: 13px;
            font-weight: 700;
            text-decoration: none;
        }

        @media (max-width: 750px) {

            .profile-content {
                padding: 23px;
            }

            .profile-top {
                flex-direction: column;
            }

            .profile-title {
                font-size: 30px;
            }

            .profile-meta {
                grid-template-columns: 1fr;
            }

            .reviews-header {
                flex-direction: column;
                align-items: flex-start;
            }

        }

    </style>

</head>


<body>


<header class="header">

    <div class="container nav">

        <a
            href="index.php"
            class="brand"
        >

            <span class="brand-icon">
                N
            </span>

            <span>
                Naija<span>Hub</span>
            </span>

        </a>


        <nav class="desktop-nav">

            <a href="index.php">
                Home
            </a>

            <a href="index.php#discover">
                Discover
            </a>

            <a href="index.php#categories">
                Categories
            </a>

        </nav>

    </div>

</header>


<main class="profile-page">

    <div class="container">


        <a
            href="index.php#discover"
            class="back-link"
        >
            ← Back to businesses
        </a>


        <article class="profile">


            <div class="profile-cover">

                <div class="profile-letter">

                    <?php
                    echo strtoupper(
                        substr(
                            $business["business_name"],
                            0,
                            1
                        )
                    );
                    ?>

                </div>

            </div>


            <div class="profile-content">


                <div class="profile-top">

                    <div>

                        <div class="profile-category">

                            <?php
                            echo htmlspecialchars(
                                $business["category"]
                            );
                            ?>

                        </div>


                        <h1 class="profile-title">

                            <?php
                            echo htmlspecialchars(
                                $business["business_name"]
                            );
                            ?>

                        </h1>

                    </div>


                    <?php if ((int)$business["verified"] === 1): ?>

                        <span class="verified">
                            ✓ Verified Business
                        </span>

                    <?php endif; ?>


                </div>


                <p class="profile-description">

                    <?php
                    echo nl2br(
                        htmlspecialchars(
                            $business["description"]
                        )
                    );
                    ?>

                </p>


                <!-- RATING -->

                <div class="rating-summary">

                    <span class="rating-number">
                        <?php
                        echo number_format(
                            $averageRating,
                            1
                        );
                        ?>
                    </span>


                    <span class="stars">

                        <?php

                        $roundedRating =
                            (int)round($averageRating);

                        echo str_repeat(
                            "★",
                            $roundedRating
                        );

                        echo str_repeat(
                            "☆",
                            5 - $roundedRating
                        );

                        ?>

                    </span>


                    <span class="review-count">

                        <?php
                        echo $reviewCount;
                        ?>

                        approved review<?php
                        echo $reviewCount === 1
                            ? ""
                            : "s";
                        ?>

                    </span>

                </div>


                <!-- BUSINESS DETAILS -->

                <div class="profile-meta">


                    <div class="meta-box">

                        <small>
                            Location
                        </small>

                        <strong>

                            <?php
                            echo htmlspecialchars(
                                $business["city"]
                            );
                            ?>

                        </strong>

                    </div>


                    <div class="meta-box">

                        <small>
                            Address
                        </small>

                        <strong>

                            <?php
                            echo htmlspecialchars(
                                $business["address"]
                                ?: "Not provided"
                            );
                            ?>

                        </strong>

                    </div>


                    <div class="meta-box">

                        <small>
                            Category
                        </small>

                        <strong>

                            <?php
                            echo htmlspecialchars(
                                $business["category"]
                            );
                            ?>

                        </strong>

                    </div>


                </div>


                <!-- CONTACT BUTTONS -->

                <div class="profile-actions">


                    <?php if (!empty($business["phone"])): ?>

                        <a
                            class="profile-btn"
                            href="tel:<?php echo htmlspecialchars($business["phone"]); ?>"
                        >
                            📞 Call Business
                        </a>

                    <?php endif; ?>


                    <?php if (!empty($whatsapp)): ?>

                        <a
                            class="profile-btn"
                            href="https://wa.me/<?php echo $whatsapp; ?>"
                            target="_blank"
                            rel="noopener"
                        >
                            💬 WhatsApp
                        </a>

                    <?php endif; ?>


                    <?php if (!empty($business["email"])): ?>

                        <a
                            class="profile-btn secondary"
                            href="mailto:<?php echo htmlspecialchars($business["email"]); ?>"
                        >
                            ✉ Email
                        </a>

                    <?php endif; ?>


                    <a
                        class="profile-btn secondary"
                        href="review.php?business_id=<?php echo $business["id"]; ?>"
                    >
                        ⭐ Write a Review
                    </a>


                </div>


            </div>

        </article>


        <!-- REVIEWS -->

        <section class="reviews-section">


            <div class="reviews-header">

                <div>

                    <span class="section-kicker">
                        CUSTOMER REVIEWS
                    </span>

                    <h2>
                        What customers are saying
                    </h2>

                </div>


                <a
                    class="profile-btn"
                    href="review.php?business_id=<?php echo $business["id"]; ?>"
                >
                    ⭐ Write a Review
                </a>

            </div>


            <?php if ($reviewCount > 0): ?>


                <?php foreach ($reviews as $review): ?>


                    <article class="review-card">


                        <div class="review-top">


                            <span class="review-stars">

                                <?php

                                echo str_repeat(
                                    "★",
                                    (int)$review["rating"]
                                );

                                echo str_repeat(
                                    "☆",
                                    5 - (int)$review["rating"]
                                );

                                ?>

                            </span>


                            <span class="review-date">

                                <?php
                                echo htmlspecialchars(
                                    date(
                                        "M j, Y",
                                        strtotime(
                                            $review["created_at"]
                                        )
                                    )
                                );
                                ?>

                            </span>


                        </div>


                        <p class="review-comment">

                            <?php
                            echo nl2br(
                                htmlspecialchars(
                                    $review["comment"]
                                )
                            );
                            ?>

                        </p>


                    </article>


                <?php endforeach; ?>


            <?php else: ?>


                <div class="empty-reviews">

                    No approved reviews yet.
                    Be the first to share your experience.

                </div>


            <?php endif; ?>


        </section>


    </div>

</main>


</body>

</html>