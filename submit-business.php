<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require "config.php";

$message = "";
$messageType = "";

/*
|--------------------------------------------------------------------------
| LOAD CATEGORIES
|--------------------------------------------------------------------------
*/

$categoryQuery = $conn->query("
    SELECT id, name
    FROM categories
    ORDER BY name ASC
");

$categories = [];

if ($categoryQuery) {

    while ($row = $categoryQuery->fetch_assoc()) {
        $categories[] = $row;
    }

}


/*
|--------------------------------------------------------------------------
| PROCESS FORM
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $businessName = trim($_POST["business_name"] ?? "");
    $categoryId   = (int)($_POST["category_id"] ?? 0);
    $description  = trim($_POST["description"] ?? "");
    $phone        = trim($_POST["phone"] ?? "");
    $whatsapp     = trim($_POST["whatsapp"] ?? "");
    $email        = trim($_POST["email"] ?? "");
    $address      = trim($_POST["address"] ?? "");
    $city         = trim($_POST["city"] ?? "");

    /*
    | Basic validation
    */

    if (
        $businessName === "" ||
        $categoryId <= 0 ||
        $description === "" ||
        $phone === "" ||
        $city === ""
    ) {

        $message = "Please complete all required fields.";
        $messageType = "error";

    } else {

        /*
        | Create URL-friendly slug
        */

        $slug = strtolower($businessName);

        $slug = preg_replace(
            '/[^a-z0-9]+/',
            '-',
            $slug
        );

        $slug = trim($slug, '-');


        /*
        | Make sure the slug is unique
        */

        $originalSlug = $slug;
        $counter = 1;

        while (true) {

            $check = $conn->prepare("
                SELECT id
                FROM businesses
                WHERE slug = ?
                LIMIT 1
            ");

            $check->bind_param(
                "s",
                $slug
            );

            $check->execute();

            $checkResult =
                $check->get_result();

            if ($checkResult->num_rows === 0) {
                break;
            }

            $slug =
                $originalSlug .
                "-" .
                $counter;

            $counter++;

        }


        /*
        | Insert business
        */

        $stmt = $conn->prepare("
            INSERT INTO businesses
            (
                owner_id,
                category_id,
                business_name,
                slug,
                description,
                phone,
                whatsapp,
                email,
                address,
                city,
                rating,
                featured,
                verified,
                status
            )
            VALUES
            (
                NULL,
                ?,
                ?,
                ?,
                ?,
                ?,
                ?,
                ?,
                ?,
                ?,
                0.0,
                0,
                0,
                'pending'
            )
        ");

        $stmt->bind_param(
            "issssssss",
            $categoryId,
            $businessName,
            $slug,
            $description,
            $phone,
            $whatsapp,
            $email,
            $address,
            $city
        );


        if ($stmt->execute()) {

            $message =
                "Your business has been submitted successfully. It is now waiting for admin approval.";

            $messageType = "success";

        } else {

            $message =
                "Something went wrong while saving your business.";

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
        List Your Business — NaijaHub
    </title>

    <link
        rel="stylesheet"
        href="assets/style.css"
    >

    <style>

        .form-page {
            padding: 70px 0;
            background: #f5f8f6;
            min-height: calc(100vh - 76px);
        }

        .form-wrapper {
            max-width: 760px;
            margin: 0 auto;
        }

        .form-card {
            background: #ffffff;
            border: 1px solid #dce5df;
            border-radius: 24px;
            padding: 36px;
            box-shadow: 0 20px 60px rgba(20, 47, 32, 0.08);
        }

        .form-card h1 {
            margin: 10px 0;
            font-size: 38px;
            line-height: 1.1;
        }

        .form-intro {
            color: #68756d;
            margin-bottom: 30px;
        }

        .business-form {
            display: grid;
            gap: 18px;
        }

        .form-group {
            display: grid;
            gap: 7px;
        }

        .form-group label {
            font-size: 13px;
            font-weight: 700;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            border: 1px solid #dce5df;
            border-radius: 12px;
            padding: 13px 14px;
            outline: none;
            background: #fff;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            border-color: #12894a;
            box-shadow: 0 0 0 3px rgba(18, 137, 74, 0.08);
        }

        .form-group textarea {
            min-height: 130px;
            resize: vertical;
        }

        .two-column {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 18px;
        }

        .required {
            color: #c33;
        }

        .message {
            padding: 13px 15px;
            border-radius: 12px;
            font-size: 13px;
            margin-bottom: 20px;
        }

        .message.success {
            background: #e7f7ec;
            color: #176c38;
            border: 1px solid #bfe4ca;
        }

        .message.error {
            background: #fff0f0;
            color: #a62828;
            border: 1px solid #efc4c4;
        }

        .form-note {
            margin-top: 5px;
            color: #77837c;
            font-size: 12px;
        }

        @media (max-width: 700px) {

            .form-card {
                padding: 24px 18px;
            }

            .form-card h1 {
                font-size: 30px;
            }

            .two-column {
                grid-template-columns: 1fr;
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


<main class="form-page">

    <div class="container form-wrapper">


        <div class="form-card">


            <span class="section-kicker">
                FOR BUSINESS OWNERS
            </span>


            <h1>
                List your business on NaijaHub
            </h1>


            <p class="form-intro">
                Submit your business details and our admin
                will review the listing before it becomes public.
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
                class="business-form"
            >


                <div class="form-group">

                    <label>
                        Business Name
                        <span class="required">*</span>
                    </label>

                    <input
                        type="text"
                        name="business_name"
                        placeholder="e.g. BrightPixel Studio"
                        required
                    >

                </div>


                <div class="two-column">


                    <div class="form-group">

                        <label>
                            Category
                            <span class="required">*</span>
                        </label>

                        <select
                            name="category_id"
                            required
                        >

                            <option value="">
                                Select category
                            </option>


                            <?php foreach ($categories as $category): ?>

                                <option
                                    value="<?php echo $category["id"]; ?>"
                                >

                                    <?php
                                    echo htmlspecialchars(
                                        $category["name"]
                                    );
                                    ?>

                                </option>

                            <?php endforeach; ?>


                        </select>

                    </div>


                    <div class="form-group">

                        <label>
                            City
                            <span class="required">*</span>
                        </label>

                        <select
                            name="city"
                            required
                        >

                            <option value="">
                                Select city
                            </option>

                            <option value="Lagos">
                                Lagos
                            </option>

                            <option value="Abuja">
                                Abuja
                            </option>

                            <option value="Port Harcourt">
                                Port Harcourt
                            </option>

                            <option value="Ibadan">
                                Ibadan
                            </option>

                        </select>

                    </div>


                </div>


                <div class="form-group">

                    <label>
                        Business Description
                        <span class="required">*</span>
                    </label>

                    <textarea
                        name="description"
                        placeholder="Tell customers what your business does..."
                        required
                    ></textarea>

                </div>


                <div class="two-column">


                    <div class="form-group">

                        <label>
                            Phone
                            <span class="required">*</span>
                        </label>

                        <input
                            type="text"
                            name="phone"
                            placeholder="+234..."
                            required
                        >

                    </div>


                    <div class="form-group">

                        <label>
                            WhatsApp
                        </label>

                        <input
                            type="text"
                            name="whatsapp"
                            placeholder="+234..."
                        >

                    </div>


                </div>


                <div class="two-column">


                    <div class="form-group">

                        <label>
                            Email
                        </label>

                        <input
                            type="email"
                            name="email"
                            placeholder="business@example.com"
                        >

                    </div>


                    <div class="form-group">

                        <label>
                            Address
                        </label>

                        <input
                            type="text"
                            name="address"
                            placeholder="Business address"
                        >

                    </div>


                </div>


                <div class="form-group">

                    <button
                        type="submit"
                        class="btn btn-primary"
                    >
                        Submit Business
                    </button>

                    <p class="form-note">
                        Your listing will remain pending until an admin approves it.
                    </p>

                </div>


            </form>


        </div>


    </div>

</main>


</body>

</html>