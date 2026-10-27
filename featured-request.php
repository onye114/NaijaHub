<?php

require "config.php";

error_reporting(E_ALL);
ini_set("display_errors", 1);

$message = "";
$messageType = "";

$businessesResult = $conn->query("
    SELECT id, business_name, city
    FROM businesses
    WHERE status = 'approved'
    ORDER BY business_name ASC
");

$businesses = [];

if ($businessesResult) {
    while ($row = $businessesResult->fetch_assoc()) {
        $businesses[] = $row;
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $businessId = (int)($_POST["business_id"] ?? 0);

    if ($businessId <= 0) {

        $message = "Please select a business.";
        $messageType = "error";

    } else {

        $check = $conn->prepare("
            SELECT id
            FROM businesses
            WHERE id = ?
              AND status = 'approved'
            LIMIT 1
        ");

        $check->bind_param("i", $businessId);
        $check->execute();

        $result = $check->get_result();

        if ($result->num_rows === 0) {

            $message = "That business could not be found.";
            $messageType = "error";

        } else {

            /*
            | For now this simply marks the business as featured.
            | We will add real payment verification afterward.
            */

            $stmt = $conn->prepare("
                UPDATE businesses
                SET featured = 1
                WHERE id = ?
            ");

            $stmt->bind_param("i", $businessId);

            if ($stmt->execute()) {

                $message =
                    "Featured listing request recorded successfully. The business is now marked as featured.";

                $messageType = "success";

            } else {

                $message =
                    "The featured request could not be saved.";

                $messageType = "error";
            }
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
        Apply for Featured Listing — NaijaHub
    </title>

    <link
        rel="stylesheet"
        href="assets/style.css"
    >

    <style>

        .page {
            min-height: 100vh;
            background: #f5f8f6;
            padding: 70px 0;
        }

        .card {
            width: min(650px, 100%);
            margin: 0 auto;
            background: #fff;
            border: 1px solid #dce5df;
            border-radius: 22px;
            padding: 34px;
            box-shadow: 0 20px 60px rgba(20,47,32,.08);
        }

        .card h1 {
            margin: 8px 0;
            font-size: 34px;
        }

        .intro {
            color: #68756d;
            line-height: 1.6;
        }

        .form {
            margin-top: 25px;
        }

        label {
            display: block;
            font-size: 13px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        select {
            width: 100%;
            padding: 13px;
            border: 1px solid #dce5df;
            border-radius: 10px;
            background: #fff;
            outline: none;
        }

        select:focus {
            border-color: #12894a;
        }

        button {
            margin-top: 16px;
            width: 100%;
            border: 0;
            border-radius: 10px;
            padding: 13px;
            background: #12894a;
            color: white;
            font-weight: 700;
            cursor: pointer;
        }

        .message {
            margin-top: 18px;
            padding: 12px 14px;
            border-radius: 10px;
            font-size: 13px;
        }

        .success {
            background: #e7f7ec;
            color: #176c38;
        }

        .error {
            background: #fff0f0;
            color: #a62828;
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

    </div>

</header>


<main class="page">

    <div class="container">

        <div class="card">

            <span class="section-kicker">
                FEATURED LISTING
            </span>

            <h1>
                Feature your business
            </h1>

            <p class="intro">
                Select an approved business below to mark it as a
                Featured Listing. This is a development-stage setup;
                payment verification will be connected next.
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
                class="form"
            >

                <label for="business_id">
                    Select business
                </label>

                <select
                    id="business_id"
                    name="business_id"
                    required
                >

                    <option value="">
                        Choose an approved business
                    </option>

                    <?php foreach ($businesses as $business): ?>

                        <option
                            value="<?php echo $business["id"]; ?>"
                        >

                            <?php
                            echo htmlspecialchars(
                                $business["business_name"]
                            );
                            ?>

                            —
                            <?php
                            echo htmlspecialchars(
                                $business["city"]
                            );
                            ?>

                        </option>

                    <?php endforeach; ?>

                </select>


                <button type="submit">
                    Apply for Featured Listing
                </button>

            </form>

        </div>

    </div>

</main>

</body>

</html>