<?php

require "config.php";

$businessId = (int)($_GET["business_id"] ?? $_POST["business_id"] ?? 0);

if ($businessId <= 0) {
    header("Location: index.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| GET BUSINESS
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT
        businesses.id,
        businesses.business_name,
        businesses.slug
    FROM businesses
    WHERE businesses.id = ?
      AND businesses.status = 'approved'
    LIMIT 1
");

$stmt->bind_param("i", $businessId);
$stmt->execute();

$result = $stmt->get_result();
$business = $result->fetch_assoc();

if (!$business) {
    die("Business not found.");
}


$message = "";
$messageType = "";

/*
|--------------------------------------------------------------------------
| SUBMIT REVIEW
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $rating = (int)($_POST["rating"] ?? 0);
    $comment = trim($_POST["comment"] ?? "");

    if ($rating < 1 || $rating > 5 || $comment === "") {

        $message = "Please choose a rating and write a comment.";
        $messageType = "error";

    } else {

        /*
        | For now, reviews are submitted without requiring
        | a customer account. We can add customer accounts next.
        */

        $stmt = $conn->prepare("
            INSERT INTO reviews
            (
                business_id,
                user_id,
                rating,
                comment,
                status
            )
            VALUES
            (
                ?,
                NULL,
                ?,
                ?,
                'pending'
            )
        ");

        $stmt->bind_param(
            "iis",
            $businessId,
            $rating,
            $comment
        );

        if ($stmt->execute()) {

            $message =
                "Thank you. Your review was submitted and is waiting for approval.";

            $messageType = "success";

        } else {

            $message =
                "We could not save your review.";

            $messageType = "error";

        }

    }

}

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
        Review <?php echo htmlspecialchars($business["business_name"]); ?>
        — NaijaHub
    </title>

    <link
        rel="stylesheet"
        href="assets/style.css"
    >

    <style>

        .review-page {
            padding: 70px 0;
            background: #f5f8f6;
            min-height: calc(100vh - 76px);
        }

        .review-card {
            width: min(650px, 100%);
            margin: 0 auto;
            background: #fff;
            border: 1px solid #dce5df;
            border-radius: 22px;
            padding: 32px;
            box-shadow: 0 20px 60px rgba(20,47,32,.08);
        }

        .review-card h1 {
            margin: 8px 0;
            font-size: 32px;
        }

        .review-card p {
            color: #68756d;
        }

        .review-form {
            display: grid;
            gap: 18px;
            margin-top: 25px;
        }

        .review-form label {
            display: block;
            font-size: 13px;
            font-weight: 700;
        }

        .review-form select,
        .review-form textarea {
            display: block;
            width: 100%;
            margin-top: 7px;
            border: 1px solid #dce5df;
            border-radius: 11px;
            padding: 12px;
            outline: none;
        }

        .review-form textarea {
            min-height: 140px;
            resize: vertical;
        }

        .review-form button {
            border: 0;
            border-radius: 11px;
            padding: 13px;
            background: #12894a;
            color: #fff;
            font-weight: 700;
            cursor: pointer;
        }

        .review-form button:hover {
            background: #0c6f3a;
        }

        .message {
            padding: 12px 14px;
            border-radius: 10px;
            font-size: 13px;
            margin-top: 18px;
        }

        .message.success {
            background: #e7f7ec;
            color: #176c38;
        }

        .message.error {
            background: #fff0f0;
            color: #a62828;
        }

    </style>

</head>

<body>

<header class="header">

    <div class="container nav">

        <a href="index.php" class="brand">

            <span class="brand-icon">
                N
            </span>

            <span>
                Naija<span>Hub</span>
            </span>

        </a>

    </div>

</header>


<main class="review-page">

    <div class="container">

        <div class="review-card">

            <span class="section-kicker">
                CUSTOMER REVIEW
            </span>

            <h1>
                Review
                <?php
                echo htmlspecialchars(
                    $business["business_name"]
                );
                ?>
            </h1>

            <p>
                Share your experience with this business.
                Your review will be checked by an admin before
                it appears publicly.
            </p>


            <?php if ($message !== ""): ?>

                <div class="message <?php echo $messageType; ?>">

                    <?php
                    echo htmlspecialchars($message);
                    ?>

                </div>

            <?php endif; ?>


            <form
                method="POST"
                class="review-form"
            >

                <input
                    type="hidden"
                    name="business_id"
                    value="<?php echo $business["id"]; ?>"
                >


                <label>

                    Rating

                    <select
                        name="rating"
                        required
                    >

                        <option value="">
                            Select rating
                        </option>

                        <option value="5">
                            ★★★★★ — Excellent
                        </option>

                        <option value="4">
                            ★★★★☆ — Very good
                        </option>

                        <option value="3">
                            ★★★☆☆ — Good
                        </option>

                        <option value="2">
                            ★★☆☆☆ — Poor
                        </option>

                        <option value="1">
                            ★☆☆☆☆ — Very poor
                        </option>

                    </select>

                </label>


                <label>

                    Your review

                    <textarea
                        name="comment"
                        placeholder="Tell other customers about your experience..."
                        required
                    ></textarea>

                </label>


                <button type="submit">
                    Submit Review
                </button>

            </form>

        </div>

    </div>

</main>

</body>

</html>