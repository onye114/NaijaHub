<?php

session_start();

require "../config.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    if ($email === "" || $password === "") {

        $error = "Please enter your email and password.";

    } else {

        $stmt = $conn->prepare("
            SELECT id, name, email, password, role
            FROM users
            WHERE email = ?
            LIMIT 1
        ");

        if (!$stmt) {

            $error = "Database query could not be prepared.";

        } else {

            $stmt->bind_param("s", $email);
            $stmt->execute();

            $result = $stmt->get_result();
            $user = $result->fetch_assoc();

            if (
                $user &&
                $user["role"] === "admin" &&
                password_verify($password, $user["password"])
            ) {

                $_SESSION["admin_id"] = $user["id"];
                $_SESSION["admin_name"] = $user["name"];
                $_SESSION["admin_role"] = $user["role"];

                header("Location: index.php");
                exit;

            } else {

                $error = "Invalid admin email or password.";

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

    <title>NaijaHub Admin Login</title>

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            background: #f3f7f4;
            font-family: Arial, sans-serif;
            color: #17211b;
        }

        .login-card {
            width: min(420px, calc(100% - 30px));
            background: #ffffff;
            padding: 34px;
            border-radius: 22px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.10);
        }

        .logo {
            width: 44px;
            height: 44px;
            display: grid;
            place-items: center;
            border-radius: 12px;
            background: #12894a;
            color: #ffffff;
            font-weight: 800;
            font-size: 20px;
            margin-bottom: 18px;
        }

        h1 {
            margin: 0 0 8px;
            font-size: 28px;
        }

        .subtitle {
            color: #68756d;
            margin: 0 0 25px;
            font-size: 14px;
        }

        .error {
            background: #fff0f0;
            color: #a62828;
            border: 1px solid #efc4c4;
            padding: 11px 12px;
            border-radius: 9px;
            margin-bottom: 16px;
            font-size: 13px;
        }

        label {
            display: block;
            margin-bottom: 15px;
            font-size: 13px;
            font-weight: 700;
        }

        input {
            width: 100%;
            margin-top: 7px;
            padding: 12px;
            border: 1px solid #dce5df;
            border-radius: 10px;
            outline: none;
        }

        input:focus {
            border-color: #12894a;
        }

        button {
            width: 100%;
            border: 0;
            padding: 13px;
            border-radius: 10px;
            background: #12894a;
            color: #ffffff;
            font-weight: 700;
            cursor: pointer;
        }

        button:hover {
            background: #0c6f3a;
        }

        .back {
            display: block;
            text-align: center;
            margin-top: 18px;
            color: #12894a;
            text-decoration: none;
            font-size: 13px;
        }

    </style>

</head>

<body>

<div class="login-card">

    <div class="logo">N</div>

    <h1>NaijaHub Admin</h1>

    <p class="subtitle">
        Sign in to manage businesses and listings.
    </p>

    <?php if ($error !== ""): ?>

        <div class="error">
            <?php echo htmlspecialchars($error); ?>
        </div>

    <?php endif; ?>

    <form method="POST">

        <label>
            Email

            <input
                type="email"
                name="email"
                placeholder="Admin email"
                required
            >
        </label>

        <label>
            Password

            <input
                type="password"
                name="password"
                placeholder="Password"
                required
            >
        </label>

        <button type="submit">
            Sign in
        </button>

    </form>

    <a href="../index.php" class="back">
        ← Back to NaijaHub
    </a>

</div>

</body>
</html>