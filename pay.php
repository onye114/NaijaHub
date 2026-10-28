<?php

require "config.php";
require "paystack-config.php";

error_reporting(E_ALL);
ini_set("display_errors", 0);

$message = "";


/*
|--------------------------------------------------------------------------
| GET APPROVED BUSINESSES
|--------------------------------------------------------------------------
*/

$result = $conn->query("
    SELECT id, business_name, email, city
    FROM businesses
    WHERE status = 'approved'
    ORDER BY business_name ASC
");

$businesses = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $businesses[] = $row;
    }
}


/*
|--------------------------------------------------------------------------
| START PAYMENT
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $businessId = (int)($_POST["business_id"] ?? 0);
    $email = trim($_POST["email"] ?? "");

    if ($businessId <= 0 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $message = "Please select a business and enter a valid email address.";

    } else {

        /*
        | Confirm that the business exists and is approved.
        */

        $stmt = $conn->prepare("
            SELECT id, business_name
            FROM businesses
            WHERE id = ?
              AND status = 'approved'
            LIMIT 1
        ");

        $stmt->bind_param("i", $businessId);
        $stmt->execute();

        $businessResult = $stmt->get_result();
        $business = $businessResult->fetch_assoc();

        if (!$business) {

            $message = "The selected business could not be found.";

        } else {

            /*
            | ₦10,000 = 1,000,000 kobo.
            */

            $amount = 1000000;

            /*
            | Generate a unique Paystack reference.
            */

            $reference =
                 "NHP-" .
                   $businessId .
                       "-" .
                     strtoupper(bin2hex(random_bytes(5)));     
                


            /*
            | Callback URL.
            |
            | On localhost this is only suitable for returning
            | the browser after checkout. Paystack webhooks
            | cannot reach localhost directly.
            */
             
            $callbackUrl = rtrim(APP_BASE_URL, '/') . '/payment-callback.php';


            $fields = [

                "email" => $email,

                "amount" => (string)$amount,

                "currency" => "NGN",

                "reference" => $reference,

                "callback_url" => $callbackUrl,

                "metadata" => [

                    "business_id" => $businessId,

                    "business_name" =>
                        $business["business_name"],

                    "purpose" =>
                        "NaijaHub Featured Listing"

                ]

            ];


            /*
            | Send request to Paystack.
            */

            $ch = curl_init(
                "https://api.paystack.co/transaction/initialize"
            );


            curl_setopt(
                $ch,
                CURLOPT_POST,
                true
            );


            curl_setopt(
                $ch,
                CURLOPT_POSTFIELDS,
                json_encode($fields)
            );


            curl_setopt(
                $ch,
                CURLOPT_HTTPHEADER,
                [

                    "Authorization: Bearer " .
                    PAYSTACK_SECRET_KEY,

                    "Content-Type: application/json",

                    "Cache-Control: no-cache"

                ]
            );


            curl_setopt(
                $ch,
                CURLOPT_RETURNTRANSFER,
                true
            );


            $response = curl_exec($ch);

            $curlError = curl_error($ch);

            curl_close($ch);


            if ($response === false || $curlError !== "") {

                $message =
                    "Could not connect to Paystack: " .
                    $curlError;

            } else {

                $data =
                    json_decode(
                        $response,
                        true
                    );


                if (
                    isset($data["status"]) &&
                    $data["status"] === true &&
                    isset(
                        $data["data"]["authorization_url"]
                    )
                ) {

                    /*
                    | Redirect customer to Paystack checkout.
                    */

                    header(
                        "Location: " .
                        $data["data"]["authorization_url"]
                    );

                    exit;

                } else {

                    $message =
                        $data["message"] ??
                        "Paystack could not initialize the payment.";

                }

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
        Pay for Featured Listing — NaijaHub
    </title>

    <link
        rel="stylesheet"
        href="assets/style.css"
    >

    <style>

        .payment-page {
            min-height: 100vh;
            background: #f5f8f6;
            padding: 70px 0;
        }

        .payment-card {
            width: min(620px, calc(100% - 30px));
            margin: 0 auto;
            background: white;
            border: 1px solid #dce5df;
            border-radius: 22px;
            padding: 34px;
            box-shadow: 0 20px 60px rgba(20,47,32,.08);
        }

        .payment-card h1 {
            margin: 8px 0;
            font-size: 34px;
        }

        .intro {
            color: #68756d;
            line-height: 1.6;
        }

        .price-box {
            margin: 25px 0;
            padding: 20px;
            border-radius: 15px;
            background: #f1f7f3;
        }

        .price-box span {
            display: block;
            color: #68756d;
            font-size: 13px;
        }

        .price-box strong {
            display: block;
            margin-top: 5px;
            font-size: 32px;
        }

        .form-group {
            margin-bottom: 18px;
        }

        label {
            display: block;
            margin-bottom: 7px;
            font-size: 13px;
            font-weight: 700;
        }

        select,
        input {
            width: 100%;
            padding: 13px;
            border: 1px solid #dce5df;
            border-radius: 10px;
            outline: none;
            background: white;
        }

        select:focus,
        input:focus {
            border-color: #12894a;
        }

        .pay-button {
            width: 100%;
            border: 0;
            border-radius: 11px;
            padding: 14px;
            background: #12894a;
            color: white;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
        }

        .pay-button:hover {
            background: #0c6f3a;
        }

        .message {
            background: #fff0f0;
            border: 1px solid #efc4c4;
            color: #a62828;
            border-radius: 10px;
            padding: 12px;
            font-size: 13px;
            margin: 18px 0;
        }

        .notice {
            margin-top: 16px;
            color: #68756d;
            font-size: 12px;
            line-height: 1.5;
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


<main class="payment-page">

    <div class="payment-card">

        <span class="section-kicker">
            FEATURED LISTING
        </span>

        <h1>
            Promote your business
        </h1>

        <p class="intro">
            Choose your approved business and continue to the
            Paystack test checkout.
        </p>


        <div class="price-box">

            <span>
                Featured Listing
            </span>

            <strong>
                ₦10,000
            </strong>

            <span>
                Per month — test mode
            </span>

        </div>


        <?php if ($message !== ""): ?>

            <div class="message">

                <?php
                echo htmlspecialchars($message);
                ?>

            </div>

        <?php endif; ?>


        <form method="POST">


            <div class="form-group">

                <label for="business_id">
                    Business
                </label>

                <select
                    id="business_id"
                    name="business_id"
                    required
                >

                    <option value="">
                        Select approved business
                    </option>


                    <?php foreach ($businesses as $business): ?>

                        <option
                            value="<?php echo (int)$business["id"]; ?>"
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

            </div>


            <div class="form-group">

                <label for="email">
                    Email for payment
                </label>

                <input
                    id="email"
                    type="email"
                    name="email"
                    placeholder="you@example.com"
                    required
                >

            </div>


            <button
                type="submit"
                class="pay-button"
            >
                Continue to Paystack
            </button>


        </form>


        <p class="notice">

            This is currently connected to Paystack Test Mode.
            No real payment should be used for this development test.

        </p>

    </div>

</main>

</body>

</html>