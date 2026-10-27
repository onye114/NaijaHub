<?php

require "config.php";

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
        Featured Listings — NaijaHub
    </title>

    <link
        rel="stylesheet"
        href="assets/style.css"
    >

    <style>

        .pricing-page {
            padding: 70px 0;
            background: #f5f8f6;
            min-height: 100vh;
        }

        .pricing-heading {
            max-width: 700px;
            margin: 0 auto 40px;
            text-align: center;
        }

        .pricing-heading h1 {
            font-size: 42px;
            margin: 10px 0;
        }

        .pricing-heading p {
            color: #68756d;
            line-height: 1.7;
        }

        .pricing-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 22px;
            max-width: 850px;
            margin: 0 auto;
        }

        .price-card {
            background: #fff;
            border: 1px solid #dce5df;
            border-radius: 22px;
            padding: 32px;
            box-shadow: 0 18px 50px rgba(20,47,32,.07);
        }

        .price-card.featured {
            border: 2px solid #12894a;
            position: relative;
        }

        .recommended {
            position: absolute;
            top: 16px;
            right: 16px;
            background: #12894a;
            color: #fff;
            padding: 6px 10px;
            border-radius: 999px;
            font-size: 10px;
            font-weight: 800;
        }

        .price-card h2 {
            margin: 0 0 8px;
        }

        .price {
            font-size: 42px;
            font-weight: 800;
            margin: 15px 0;
        }

        .price small {
            font-size: 14px;
            color: #68756d;
            font-weight: 500;
        }

        .features {
            margin: 20px 0 25px;
            padding: 0;
            list-style: none;
        }

        .features li {
            padding: 9px 0;
            border-bottom: 1px solid #edf1ee;
            font-size: 14px;
        }

        .features li:last-child {
            border-bottom: 0;
        }

        .choose-btn {
            display: block;
            text-align: center;
            text-decoration: none;
            background: #12894a;
            color: white;
            padding: 13px;
            border-radius: 11px;
            font-weight: 700;
        }

        .choose-btn.secondary {
            background: #17211b;
        }

        @media (max-width: 750px) {

            .pricing-grid {
                grid-template-columns: 1fr;
            }

            .pricing-heading h1 {
                font-size: 32px;
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

    </div>

</header>


<main class="pricing-page">

    <div class="container">


        <div class="pricing-heading">

            <span class="section-kicker">
                GROW YOUR BUSINESS
            </span>

            <h1>
                Get discovered by more customers
            </h1>

            <p>
                Featured listings are placed more prominently
                on NaijaHub and can help businesses stand out.
            </p>

        </div>


        <div class="pricing-grid">


            <article class="price-card">

                <h2>
                    Standard
                </h2>

                <p>
                    For businesses that want a basic listing.
                </p>

                <div class="price">
                    Free
                </div>

                <ul class="features">

                    <li>
                        ✓ Business profile
                    </li>

                    <li>
                        ✓ Phone and WhatsApp contact
                    </li>

                    <li>
                        ✓ Customer reviews
                    </li>

                    <li>
                        ✓ Standard search placement
                    </li>

                </ul>

                <a
                    href="submit-business.php"
                    class="choose-btn secondary"
                >
                    List for Free
                </a>

            </article>


            <article class="price-card featured">

                <span class="recommended">
                    RECOMMENDED
                </span>

                <h2>
                    Featured
                </h2>

                <p>
                    For businesses that want more visibility.
                </p>

                <div class="price">
                    ₦10,000
                    <small>/ month</small>
                </div>

                <ul class="features">

                    <li>
                        ✓ Everything in Standard
                    </li>

                    <li>
                        ✓ Featured badge
                    </li>

                    <li>
                        ✓ Priority placement
                    </li>

                    <li>
                        ✓ Greater visibility on the homepage
                    </li>

                    <li>
                        ✓ Featured on the admin-managed listings
                    </li>

                </ul>

               <a
                      href="pay.php"
                      class="choose-btn"

                      >
                      Pay ₦10,000 & Get Featured
            </a>

            </article>


        </div>

    </div>

</main>

</body>

</html>