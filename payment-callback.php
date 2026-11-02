
<?php

require_once "config.php";
require_once "paystack-config.php";

error_reporting(E_ALL);
ini_set("display_errors", "0");
ini_set("log_errors", "1");

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

    $businessIdFromReference = (int) $matches[1];

    /*
    |--------------------------------------------------------------------------
    | VERIFY TRANSACTION WITH PAYSTACK
    |--------------------------------------------------------------------------
    */

    $url =
        "https://api.paystack.co/transaction/verify/" .
        urlencode($reference);

    $ch = curl_init($url);

    if ($ch === false) {

        $message = "Payment verification could not be started.";

    } else {

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
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        if ($response === false || $curlError !== "") {

            error_log("NaijaHub Paystack verification error: " . $curlError);

            $message =
                "We could not verify the payment. Please try again.";

        } else {

            $data = json_decode($response, true);

            if (
                $httpCode < 200 ||
                $httpCode >= 300 ||
                !is_array($data) ||
                ($data["status"] ?? false) !== true ||
                !isset($data["data"]) ||
                !is_array($data["data"])
            ) {

                $message =
                    "Paystack could not verify this transaction.";

            } else {

                $transaction = $data["data"];

                $transactionReference =
                    (string) ($transaction["reference"] ?? "");

                $transactionStatus =
                    (string) ($transaction["status"] ?? "");

                $transactionAmount =
                    (int) ($transaction["amount"] ?? 0);

                $transactionCurrency =
                    (string) ($transaction["currency"] ?? "");

                $metadata = $transaction["metadata"] ?? [];

                if (!is_array($metadata)) {
                    $metadata = [];
                }

                $businessIdFromMetadata =
                    (int) ($metadata["business_id"] ?? 0);

                $purpose =
                    (string) ($metadata["purpose"] ?? "");

                /*
                |--------------------------------------------------------------------------
                | CHECK ALL PAYMENT DETAILS
                |--------------------------------------------------------------------------
                */

                $validPayment =
                    hash_equals($reference, $transactionReference) &&
                    $transactionStatus === "success" &&
                    $transactionAmount === (int) PAYSTACK_FEATURED_AMOUNT &&
                    strtoupper($transactionCurrency) === "NGN" &&
                    $purpose === "NaijaHub Featured Listing" &&
                    $businessIdFromMetadata === $businessIdFromReference;

                if (!$validPayment) {

                    $message =
                        "Payment was not confirmed for this Featured Listing. " .
                        "Check the transaction details or contact support.";

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

                        error_log("NaijaHub business check preparation failed.");

                        $message =
                            "We could not load the selected business.";

                    } else {

                        $check->bind_param(
                            "i",
                            $businessIdFromMetadata
                        );

                        if (!$check->execute()) {

                            error_log("NaijaHub business check execution failed.");

                            $message =
                                "We could not verify the selected business.";

                        } else {

                            $businessResult = $check->get_result();

                            if (!$businessResult) {

                                $message =
                                    "We could not retrieve the business details.";

                            } else {

                                $business = $businessResult->fetch_assoc();

                                if (!$business) {

                                    $message =
                                        "The selected business was not found " .
                                        "or has not been approved.";

                                } else {

                                    /*
                                    |--------------------------------------------------------------------------
                                    | RECORD PAYMENT AND ACTIVATE FEATURED STATUS
                                    |--------------------------------------------------------------------------
                                    */

                                    $record = null;
                                    $update = null;
                                    $transactionStarted = false;

                                    try {

                                        /*
                                        | Start an InnoDB transaction so the payment
                                        | record and business update succeed together.
                                        */

                                        if (!$conn->begin_transaction()) {
                                            throw new RuntimeException(
                                                "Could not start database transaction."
                                            );
                                        }

                                        $transactionStarted = true;

                                        $currencyToStore =
                                            strtoupper($transactionCurrency);

                                        /*
                                        | A UNIQUE index on paystack_reference
                                        | prevents the same transaction being reused.
                                        */

                                        $record = $conn->prepare("
                                            INSERT INTO featured_payment_records
                                            (
                                                paystack_reference,
                                                business_id,
                                                amount_kobo,
                                                currency,
                                                purpose
                                            )
                                            VALUES (?, ?, ?, ?, ?)
                                        ");

                                        if (!$record) {
                                            throw new RuntimeException(
                                                "Could not prepare payment record."
                                            );
                                        }

                                        $record->bind_param(
                                            "siiss",
                                            $transactionReference,
                                            $businessIdFromMetadata,
                                            $transactionAmount,
                                            $currencyToStore,
                                            $purpose
                                        );

                                        if (!$record->execute()) {

                                            if ((int) $record->errno === 1062) {
                                                throw new RuntimeException(
                                                    "This payment has already been processed.",
                                                    1062
                                                );
                                            }

                                            throw new RuntimeException(
                                                "Could not save payment record."
                                            );
                                        }

                                        /*
                                        |--------------------------------------------------------------------------
                                        | ACTIVATE OR EXTEND FEATURED LISTING
                                        |--------------------------------------------------------------------------
                                        |
                                        | If the business has an active expiry date,
                                        | add 30 days to that date.
                                        |
                                        | If the listing has expired or has no expiry,
                                        | start a new 30-day period from now.
                                        */

                                        $update = $conn->prepare("
                                            UPDATE businesses
                                            SET featured = 1,
                                                featured_until = DATE_ADD(
                                                    GREATEST(
                                                        COALESCE(featured_until, NOW()),
                                                        NOW()
                                                    ),
                                                    INTERVAL 30 DAY
                                                )
                                            WHERE id = ?
                                              AND status = 'approved'
                                        ");

                                        if (!$update) {
                                            throw new RuntimeException(
                                                "Could not prepare business update."
                                            );
                                        }

                                        $update->bind_param(
                                            "i",
                                            $businessIdFromMetadata
                                        );

                                        if (!$update->execute()) {
                                            throw new RuntimeException(
                                                "Business update failed."
                                            );
                                        }

                                        if ($update->affected_rows !== 1) {
                                            throw new RuntimeException(
                                                "No approved business was updated."
                                            );
                                        }

                                        /*
                                        |--------------------------------------------------------------------------
                                        | COMMIT BOTH DATABASE CHANGES
                                        |--------------------------------------------------------------------------
                                        */

                                        if (!$conn->commit()) {
                                            throw new RuntimeException(
                                                "Database transaction could not be committed."
                                            );
                                        }

                                        $transactionStarted = false;

                                        $success = true;

                                        $businessName =
                                            $business["business_name"];

                                        $message =
                                            "Payment verified successfully. " .
                                            "Your business is now marked as Featured.";

                                    } catch (Throwable $e) {

                                        if ($transactionStarted) {

                                            try {
                                                $conn->rollback();
                                            } catch (Throwable $rollbackError) {
                                                error_log(
                                                    "NaijaHub rollback error: " .
                                                    $rollbackError->getMessage()
                                                );
                                            }
                                        }

                                        if ((int) $e->getCode() === 1062) {

                                            $message =
                                                "This payment has already been processed. " .
                                                "Please do not pay again using the same reference. " .
                                                "Contact support if your listing is not Featured.";

                                        } else {

                                            error_log(
                                                "NaijaHub featured payment error: " .
                                                $e->getMessage()
                                            );

                                            $message =
                                                "Payment was verified, but the listing " .
                                                "could not be updated. Contact support.";
                                        }

                                    } finally {

                                        if ($update instanceof mysqli_stmt) {
                                            $update->close();
                                        }

                                        if ($record instanceof mysqli_stmt) {
                                            $record->close();
                                        }
                                    }
                                }
                            }
                        }

                        $check->close();
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

    <meta name="robots" content="noindex, nofollow">

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

        .button:hover {
            background: #0e713c;
        }
    </style>

</head>

<body>

    <main class="card">

        <?php if ($success): ?>

            <div class="icon">✅</div>

            <h1>Payment Successful</h1>

            <p>
                <?php
                echo htmlspecialchars(
                    $message,
                    ENT_QUOTES | ENT_SUBSTITUTE,
                    "UTF-8"
                );
                ?>
            </p>

            <p>
                Business:
                <strong>
                    <?php
                    echo htmlspecialchars(
                        $businessName,
                        ENT_QUOTES | ENT_SUBSTITUTE,
                        "UTF-8"
                    );
                    ?>
                </strong>
            </p>

        <?php else: ?>

            <div class="icon">ℹ️</div>

            <h1>Payment Result</h1>

            <p>
                <?php
                echo htmlspecialchars(
                    $message,
                    ENT_QUOTES | ENT_SUBSTITUTE,
                    "UTF-8"
                );
                ?>
            </p>

        <?php endif; ?>

        <a class="button" href="index.php">
            Return to NaijaHub
        </a>

    </main>

</body>

</html>
