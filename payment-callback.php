<?php

require "config.php";
require "paystack-config.php";

error_reporting(E_ALL);
ini_set("display_errors", "0");

$success = false;
$message = "";
$businessName = "";

$reference = trim($_GET["reference"] ?? "");


/*
|--------------------------------------------------------------------------
| CHECK THE PAYMENT REFERENCE
|--------------------------------------------------------------------------
*/

if ($reference === "") {

    $message = "No payment reference was provided.";

} elseif (
    !preg_match(
        '/^NHP-([1-9][0-9]*)-([A-F0-9]{10})$/i',
        $reference,
        $matches
    )
) {

    $message = "The payment reference is invalid.";

} else {

    $businessIdFromReference = (int)$matches[1];


    /*
    |--------------------------------------------------------------------------
    | VERIFY TRANSACTION WITH PAYSTACK
    |--------------------------------------------------------------------------
    */

    $url =
        "https://api.paystack.co/transaction/verify/" .
        urlencode($reference);

    $ch = curl_init($url);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);

    curl_setopt(
        $ch,
        CURLOPT_HTTPHEADER,
        [
            "Authorization: Bearer " . PAYSTACK_SECRET_KEY,
            "Cache-Control: no-cache"
        ]
    );

    $response = curl_exec($ch);
    $curlError = curl_error($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    curl_close($ch);


    if ($response === false || $curlError !== "") {

        $message =
            "We could not verify the payment. Please try again.";

    } else {

        $data = json_decode($response, true);


        if (
            $httpCode < 200 ||
            $httpCode >= 300 ||
            !is_array($data) ||
            ($data["status"] ?? false) !== true ||
            !isset($data["data"])
        ) {

            $message = "Paystack could not verify this transaction.";

        } else {

            $transaction = $data["data"];

            $transactionReference =
                (string)($transaction["reference"] ?? "");

            $transactionStatus =
                (string)($transaction["status"] ?? "");

            $transactionAmount =
                (int)($transaction["amount"] ?? 0);

            $transactionCurrency =
                (string)($transaction["currency"] ?? "");

            $metadata = $transaction["metadata"] ?? [];

            if (!is_array($metadata)) {
                $metadata = [];
            }

            $businessIdFromMetadata =
                (int)($metadata["business_id"] ?? 0);

            $purpose =
                (string)($metadata["purpose"] ?? "");


            /*
            |--------------------------------------------------------------------------
            | CHECK ALL PAYMENT DETAILS
            |--------------------------------------------------------------------------
            */

            $validPayment =
                hash_equals($reference, $transactionReference) &&
                $transactionStatus === "success" &&
                $transactionAmount === (int)PAYSTACK_FEATURED_AMOUNT &&
                strtoupper($transactionCurrency) === "NGN" &&
                $purpose === "NaijaHub Featured Listing" &&
                $businessIdFromMetadata === $businessIdFromReference;


            if (!$validPayment) {

                $message =
                    "Payment was not confirmed for this Featured Listing. "
                    . "Check the transaction details or contact support.";

            } else {

                /*
                |--------------------------------------------------------------------------
                | CONFIRM THE BUSINESS IS APPROVED
                |--------------------------------------------------------------------------
                */

                $check = $conn->prepare("
                    SELECT business_name
                    FROM businesses
                    WHERE id = ?
                      AND status = 'approved'
                    LIMIT 1
                ");

                if (!$check) {

                    $message =
                        "We could not load the selected business.";

                } else {

                    $check->bind_param(
                        "i",
                        $businessIdFromMetadata
                    );

                    $check->execute();

                    $businessResult = $check->get_result();

                    $business = $businessResult->fetch_assoc();

                    $check->close();


                    if (!$business) {

                        $message =
                            "The selected business was not found "
                            . "or has not been approved.";

                    } else {

                        /*
                        |--------------------------------------------------------------------------
                        | ACTIVATE FEATURED STATUS
                        |--------------------------------------------------------------------------
                        */

                        $update = $conn->prepare("
                            UPDATE businesses
                            SET featured = 1
                            WHERE id = ?
                              AND status = 'approved'
                        ");

                        if (!$update) {

                            $message =
                                "Payment was verified, but the listing "
                                . "could not be updated. Contact support.";

                        } else {

                            $update->bind_param(
                                "i",
                                $businessIdFromMetadata
                            );

                            if ($update->execute()) {

                                $success = true;

                                $businessName =
                                    $business["business_name"];

                                $message =
                                    "Payment verified successfully. "
                                    . "Your business is now marked as Featured.";

                            } else {

                                $message =
                                    "Payment was verified, but the listing "
                                    . "could not be updated. Contact support.";

                            }

                            $update->close();

                        }

                    }

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

    <title>Payment Result — NaijaHub</title>

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 20px;
            background: #f4f7f5;
            font-family: Arial, sans-serif;
            color: #17211b;
        }

        .card {
            width: min(520px, 100%);
            background: white;
            padding: 35px;
            border-radius: 22px;
            text-align: center;
            box-shadow: 0 20px 60px rgba(0,0,0,.08);
        }

        .icon {
            font-size: 48px;
            margin-bottom: 15px;
        }

        h1 {
            margin: 0 0 12px;
        }

        p {
            color: #68756d;
            line-height: 1.6;
        }

        .button {
            display: inline-block;
            margin-top: 20px;
            padding: 12px 18px;
            background: #12894a;
            color: white;
            text-decoration: none;
            border-radius: 10px;
            font-weight: 700;
        }

    </style>

</head>

<body>

    <main class="card">

        <?php if ($success): ?>

            <div class="icon">✅</div>

            <h1>Payment Successful</h1>

            <p>
                <?php echo htmlspecialchars($message); ?>
            </p>

            <p>
                Business:
                <strong>
                    <?php echo htmlspecialchars($businessName); ?>
                </strong>
            </p>

        <?php else: ?>

            <div class="icon">ℹ️</div>

            <h1>Payment Result</h1>

            <p>
                <?php echo htmlspecialchars($message); ?>
            </p>

        <?php endif; ?>

        <a class="button" href="index.php">
            Return to NaijaHub
        </a>

    </main>

</body>

</html>