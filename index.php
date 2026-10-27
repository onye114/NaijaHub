<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require "config.php";

/*
|--------------------------------------------------------------------------
| GET APPROVED BUSINESSES
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        businesses.id,
        businesses.business_name,
        businesses.slug,
        businesses.description,
        businesses.phone,
        businesses.whatsapp,
        businesses.email,
        businesses.city,
        businesses.rating,
        businesses.featured,
        businesses.verified,
        categories.name AS category
    FROM businesses
    INNER JOIN categories
        ON businesses.category_id = categories.id
    WHERE businesses.status = 'approved'
    ORDER BY businesses.featured DESC, businesses.rating DESC
";

$result = $conn->query($sql);

$businesses = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $businesses[] = $row;
    }
}

$businessCount = count($businesses);

?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <meta
        name="description"
        content="NaijaHub helps you discover trusted Nigerian businesses, services and deals."
    >

    <title>NaijaHub — Discover Trusted Local Services & Deals</title>

    <link
        rel="preconnect"
        href="https://fonts.googleapis.com"
    >

    <link
        rel="preconnect"
        href="https://fonts.gstatic.com"
        crossorigin
    >

    <link
        href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Space+Grotesk:wght@500;600;700&display=swap"
        rel="stylesheet"
    >

    <link
        rel="stylesheet"
        href="assets/style.css"
    >

</head>

<body>

<!-- TOP BAR -->

<div class="topbar">

    <div class="container topbar-inner">

        <span>
            🇳🇬 Built for local businesses in Nigeria
        </span>

        <span>
            Need help?

            <a href="mailto:onyekachristian4321@gmail.com">
                onyekachristian4321@gmail.com
            </a>
        </span>

    </div>

</div>


<!-- HEADER -->

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

            <a href="#discover">
                Discover
            </a>

            <a href="#categories">
                Categories
            </a>

            <a href="#deals">
                Deals
            </a>

            <a href="#how-it-works">
                How it works
            </a>

        </nav>


        <div class="nav-actions">

            <a
                href="#list-business"
                class="btn btn-primary"
            >
                List your business
            </a>

            <button
                class="menu-btn"
                id="menuBtn"
                type="button"
            >
                ☰
            </button>

        </div>

    </div>


    <div
        class="mobile-menu"
        id="mobileMenu"
    >

        <a href="#discover">
            Discover
        </a>

        <a href="#categories">
            Categories
        </a>

        <a href="#deals">
            Deals
        </a>

        <a href="featured.php">
    Featured
</a>

        <a href="#how-it-works">
            How it works
        </a>

        <a href="#list-business">
            List your business
        </a>

    </div>

</header>


<!-- HERO -->

<section class="hero">

    <div class="container hero-wrap">


        <div class="hero-copy">

            <div class="badge">

                <span></span>

                Discover what Nigeria has to offer

            </div>


            <h1>

                Find the right business.

                <br>

                <em>
                    Get the right deal.
                </em>

            </h1>


            <p class="hero-sub">

                Search trusted services, local businesses
                and special offers near you — all in one place.

            </p>


            <div class="search-panel">

                <div class="search-field">

                    <span class="search-icon">
                        ⌕
                    </span>

                    <input
                        id="searchInput"
                        type="search"
                        placeholder="What are you looking for?"
                    >

                </div>


                <div class="divider"></div>


                <div class="search-field location-field">

                    <span class="pin-icon">
                        ⌖
                    </span>

                    <select id="locationFilter">

                        <option value="">
                            Anywhere in Nigeria
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


                <button
                    class="search-btn"
                    id="searchBtn"
                    type="button"
                >
                    Search
                </button>

            </div>


            <div class="popular-searches">

                <span>
                    Popular:
                </span>

                <button
                    type="button"
                    data-search="technology"
                >
                    Web Design
                </button>

                <button
                    type="button"
                    data-search="food"
                >
                    Restaurants
                </button>

                <button
                    type="button"
                    data-search="events"
                >
                    Photography
                </button>

                <button
                    type="button"
                    data-search="home"
                >
                    Home Services
                </button>

            </div>


            <div class="hero-proof">

                <div class="avatars">

                    <span>AJ</span>
                    <span>MK</span>
                    <span>TO</span>
                    <span>+</span>

                </div>


                <div>

                    <strong>
                        <?php echo $businessCount; ?>
                        businesses
                    </strong>

                    <small>
                        currently listed on NaijaHub
                    </small>

                </div>

            </div>

        </div>


        <div class="hero-visual">

            <div class="visual-main">

                <div class="visual-overlay"></div>


                <div class="floating-card float-top">

                    <span class="mini-icon green">
                        ✓
                    </span>

                    <div>

                        <strong>
                            Verified businesses
                        </strong>

                        <small>
                            Local listings
                        </small>

                    </div>

                </div>


                <div class="floating-card float-bottom">

                    <span class="mini-icon gold">
                        ★
                    </span>

                    <div>

                        <strong>
                            Top-rated services
                        </strong>

                        <small>
                            Customer favourites
                        </small>

                    </div>

                </div>


                <div class="hero-location">

                    <span class="location-dot"></span>

                    <div>

                        <strong>
                            Lagos, Nigeria
                        </strong>

                        <small>
                            Discover something nearby
                        </small>

                    </div>

                </div>

            </div>

        </div>

    </div>

</section>


<!-- STATS -->

<section class="stats-strip">

    <div class="container stats">

        <div>

            <strong>
                <?php echo $businessCount; ?>
            </strong>

            <span>
                Listed Businesses
            </span>

        </div>


        <div>

            <strong>
                6
            </strong>

            <span>
                Main Categories
            </span>

        </div>


        <div>

            <strong>
                4
            </strong>

            <span>
                Sample Cities
            </span>

        </div>


        <div>

            <strong>
                4.8/5
            </strong>

            <span>
                Demo Average Rating
            </span>

        </div>

    </div>

</section>


<!-- CATEGORIES -->

<section
    class="section"
    id="categories"
>

    <div class="container">


        <div class="section-header">

            <div>

                <span class="section-kicker">
                    BROWSE BY CATEGORY
                </span>

                <h2>
                    What do you need today?
                </h2>

            </div>


            <a
                href="#discover"
                class="arrow-link"
            >
                Explore all →
            </a>

        </div>


        <div class="category-grid">


            <button
                class="category-tile"
                type="button"
                data-category="technology"
            >

                <span class="tile-icon purple">
                    ⌘
                </span>

                <strong>
                    Technology
                </strong>

                <small>
                    Web, software & repairs
                </small>

            </button>


            <button
                class="category-tile"
                type="button"
                data-category="food"
            >

                <span class="tile-icon orange">
                    ●
                </span>

                <strong>
                    Food & Restaurants
                </strong>

                <small>
                    Meals, catering & delivery
                </small>

            </button>


            <button
                class="category-tile"
                type="button"
                data-category="beauty"
            >

                <span class="tile-icon pink">
                    ✦
                </span>

                <strong>
                    Beauty & Wellness
                </strong>

                <small>
                    Salons, spa & makeup
                </small>

            </button>


            <button
                class="category-tile"
                type="button"
                data-category="home services"
            >

                <span class="tile-icon blue">
                    ⌂
                </span>

                <strong>
                    Home Services
                </strong>

                <small>
                    Cleaning, plumbing & repairs
                </small>

            </button>


            <button
                class="category-tile"
                type="button"
                data-category="events"
            >

                <span class="tile-icon yellow">
                    ◆
                </span>

                <strong>
                    Events & Media
                </strong>

                <small>
                    Photography & vendors
                </small>

            </button>


            <button
                class="category-tile"
                type="button"
                data-category="education"
            >

                <span class="tile-icon teal">
                    ▣
                </span>

                <strong>
                    Education
                </strong>

                <small>
                    Tutors & training
                </small>

            </button>


        </div>

    </div>

</section>


<!-- BUSINESSES -->

<section
    class="section section-light"
    id="discover"
>

    <div class="container">


        <div class="section-header">

            <div>

                <span class="section-kicker">
                    DISCOVER
                </span>

                <h2>
                    Popular businesses
                </h2>

                <p class="section-description">
                    Businesses currently loaded from your MySQL database.
                </p>

            </div>


            <div class="filter-actions">

                <button
                    class="filter-btn active"
                    id="featuredBtn"
                    type="button"
                >
                    Featured
                </button>

                <button
                    class="filter-btn"
                    id="ratingBtn"
                    type="button"
                >
                    Top rated
                </button>

            </div>

        </div>


        <div
            class="listings-grid"
            id="listingGrid"
        >

            <?php if ($businessCount > 0): ?>


                <?php foreach ($businesses as $business): ?>

                    <article
                        class="listing-card"
                        data-business="<?php echo htmlspecialchars(strtolower($business['business_name'])); ?>"
                        data-category="<?php echo htmlspecialchars(strtolower($business['category'])); ?>"
                        data-city="<?php echo htmlspecialchars(strtolower($business['city'])); ?>"
                        data-rating="<?php echo (float)$business['rating']; ?>"
                        data-featured="<?php echo (int)$business['featured']; ?>"
                    >


                        <div class="listing-visual">

                            <?php if ((int)$business['featured'] === 1): ?>

                                <span class="feature-tag">
                                    FEATURED
                                </span>

                            <?php endif; ?>


                            <span class="visual-letter">

                                <?php
                                echo strtoupper(
                                    substr(
                                        $business['business_name'],
                                        0,
                                        1
                                    )
                                );
                                ?>

                            </span>

                        </div>


                        <div class="listing-body">


                            <div class="listing-top">

                                <div>

                                    <h3>

                                        <?php
                                        echo htmlspecialchars(
                                            $business['business_name']
                                        );
                                        ?>

                                    </h3>


                                    <div class="listing-category">

                                        <?php
                                        echo htmlspecialchars(
                                            $business['category']
                                        );
                                        ?>

                                    </div>

                                </div>


                                <?php if ((int)$business['verified'] === 1): ?>

                                    <span class="verified">
                                        ✓ Verified
                                    </span>

                                <?php endif; ?>

                            </div>


                            <p class="muted">

                                <?php
                                echo htmlspecialchars(
                                    $business['description']
                                );
                                ?>

                            </p>


                            <div class="listing-row">

                                <span class="rating">

                                    ★

                                    <?php
                                    echo number_format(
                                        (float)$business['rating'],
                                        1
                                    );
                                    ?>

                                </span>


                                <span>

                                    <?php
                                    echo htmlspecialchars(
                                        $business['city']
                                    );
                                    ?>

                                </span>

                            </div>


                            <a
                                href="business.php?slug=<?php echo urlencode($business['slug']); ?>"
                                class="listing-btn"
                            >
                                View business
                            </a>


                        </div>

                    </article>

                <?php endforeach; ?>


            <?php else: ?>

                <div class="empty-state">

                    <div class="empty-icon">
                        ⌕
                    </div>

                    <h3>
                        No businesses yet
                    </h3>

                    <p>
                        Approved businesses will appear here.
                    </p>

                </div>

            <?php endif; ?>


        </div>

    </div>

</section>


<!-- DEALS -->

<section
    class="section deals-section"
    id="deals"
>

    <div class="container">


        <div class="deal-banner">

            <div>

                <span class="section-kicker white">
                    DEALS & SPECIAL OFFERS
                </span>

                <h2>
                    Save money on things
                    you already need.
                </h2>

                <p>
                    Businesses can promote limited-time offers
                    to people actively looking to buy.
                </p>

            </div>


            <div class="deal-chip">
                Coming soon
            </div>

        </div>


        <div class="deal-grid">


            <article class="deal-card">

                <div class="deal-image food-image">

                    <span class="deal-tag">
                        DEMO DEAL
                    </span>

                </div>

                <div class="deal-body">

                    <small>
                        Food
                    </small>

                    <h3>
                        Weekend family meal deal
                    </h3>

                    <p>
                        Deals system will be connected to MySQL next.
                    </p>

                </div>

            </article>


            <article class="deal-card">

                <div class="deal-image tech-image">

                    <span class="deal-tag">
                        DEMO DEAL
                    </span>

                </div>

                <div class="deal-body">

                    <small>
                        Technology
                    </small>

                    <h3>
                        Business website package
                    </h3>

                    <p>
                        Businesses will be able to create real promotions.
                    </p>

                </div>

            </article>


            <article class="deal-card">

                <div class="deal-image beauty-image">

                    <span class="deal-tag">
                        DEMO DEAL
                    </span>

                </div>

                <div class="deal-body">

                    <small>
                        Beauty
                    </small>

                    <h3>
                        Weekend beauty package
                    </h3>

                    <p>
                        Paid and free promotional deals will be supported.
                    </p>

                </div>

            </article>


        </div>

    </div>

</section>


<!-- HOW IT WORKS -->

<section
    class="section"
    id="how-it-works"
>

    <div class="container">


        <div class="center-heading">

            <span class="section-kicker">
                HOW IT WORKS
            </span>

            <h2>
                Simple for customers.
                Valuable for businesses.
            </h2>

            <p>
                NaijaHub is designed to make local discovery easier
                while helping businesses reach customers.
            </p>

        </div>


        <div class="steps-grid">


            <article class="step-card">

                <span class="step-number">
                    01
                </span>

                <h3>
                    Search
                </h3>

                <p>
                    Tell us what service you need
                    and where you need it.
                </p>

            </article>


            <article class="step-card">

                <span class="step-number">
                    02
                </span>

                <h3>
                    Compare
                </h3>

                <p>
                    Check categories, ratings,
                    locations and business details.
                </p>

            </article>


            <article class="step-card">

                <span class="step-number">
                    03
                </span>

                <h3>
                    Connect
                </h3>

                <p>
                    Contact the business and
                    request a quote or booking.
                </p>

            </article>


        </div>

    </div>

</section>


<!-- BUSINESS CTA -->

<section
    class="section business-cta"
    id="list-business"
>

    <div class="container business-cta-inner">


        <div>

            <span class="section-kicker">
                FOR BUSINESS OWNERS
            </span>

            <h2>
                Ready to put your business
                in front of more customers?
            </h2>

            <p>
                Create a listing and start building
                your presence on NaijaHub.
            </p>

        </div>


        <a
            href="submit-business.php"
            class="btn btn-light large"
        >
            List my business
        </a>


    </div>

</section>


<!-- FOOTER -->

<footer class="footer">

    <div class="container footer-top">


        <div class="footer-brand">

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


            <p>
                Discover trusted businesses,
                services and deals across Nigeria.
            </p>

        </div>


        <div class="footer-col">

            <strong>
                Explore
            </strong>

            <a href="#discover">
                Businesses
            </a>

            <a href="#categories">
                Categories
            </a>

            <a href="#deals">
                Deals
            </a>

        </div>


        <div class="footer-col">

            <strong>
                For businesses
            </strong>

            <a href="#list-business">
                List your business
            </a>

            <a href="#how-it-works">
                How it works
            </a>

        </div>


        <div class="footer-col">

            <strong>
                Contact
            </strong>

            <a href="mailto:onyekachristian4321@gmail.com">
                onyekachristian4321@gmail.com
            </a>

            <span>
                Lagos, Nigeria
            </span>

        </div>


    </div>


    <div class="container footer-bottom">

        <span>
            © 2026 NaijaHub. All rights reserved.
        </span>

        <span>
            Built for Nigeria 🇳🇬
        </span>

    </div>

</footer>


<script>

document.addEventListener("DOMContentLoaded", function () {

    const searchInput =
        document.getElementById("searchInput");

    const locationFilter =
        document.getElementById("locationFilter");

    const searchBtn =
        document.getElementById("searchBtn");

    const listingGrid =
        document.getElementById("listingGrid");

    let cards = Array.from(
        listingGrid.querySelectorAll(".listing-card")
    );


    function filterBusinesses(scrollToResults = false) {

        const search =
            searchInput.value
                .trim()
                .toLowerCase();

        const location =
            locationFilter.value
                .trim()
                .toLowerCase();


        cards.forEach(function (card) {

            const business =
                card.dataset.business || "";

            const category =
                card.dataset.category || "";

            const city =
                card.dataset.city || "";


            const matchesSearch =
                search === "" ||
                business.includes(search) ||
                category.includes(search);


            const matchesLocation =
                location === "" ||
                city === location;


            card.style.display =
                matchesSearch && matchesLocation
                    ? ""
                    : "none";

        });


        if (scrollToResults) {

            document
                .getElementById("discover")
                .scrollIntoView({
                    behavior: "smooth"
                });

        }

    }


    searchBtn.addEventListener(
        "click",
        function () {
            filterBusinesses(true);
        }
    );


    searchInput.addEventListener(
        "keydown",
        function (event) {

            if (event.key === "Enter") {
                filterBusinesses(true);
            }

        }
    );


    locationFilter.addEventListener(
        "change",
        function () {
            filterBusinesses(false);
        }
    );


    document
        .querySelectorAll("[data-search]")
        .forEach(function (button) {

            button.addEventListener(
                "click",
                function () {

                    searchInput.value =
                        button.dataset.search;

                    locationFilter.value = "";

                    filterBusinesses(true);

                }
            );

        });


    document
        .querySelectorAll("[data-category]")
        .forEach(function (button) {

            button.addEventListener(
                "click",
                function () {

                    searchInput.value =
                        button.dataset.category;

                    locationFilter.value = "";

                    filterBusinesses(true);

                }
            );

        });


    const featuredBtn =
        document.getElementById("featuredBtn");

    const ratingBtn =
        document.getElementById("ratingBtn");


    featuredBtn.addEventListener(
        "click",
        function () {

            featuredBtn.classList.add("active");
            ratingBtn.classList.remove("active");


            cards.sort(function (a, b) {

                return (
                    Number(b.dataset.featured) -
                    Number(a.dataset.featured)
                );

            });


            cards.forEach(function (card) {
                listingGrid.appendChild(card);
            });

        }
    );


    ratingBtn.addEventListener(
        "click",
        function () {

            ratingBtn.classList.add("active");
            featuredBtn.classList.remove("active");


            cards.sort(function (a, b) {

                return (
                    Number(b.dataset.rating) -
                    Number(a.dataset.rating)
                );

            });


            cards.forEach(function (card) {
                listingGrid.appendChild(card);
            });

        }
    );


    const menuBtn =
        document.getElementById("menuBtn");

    const mobileMenu =
        document.getElementById("mobileMenu");


    if (menuBtn) {

        menuBtn.addEventListener(
            "click",
            function () {

                mobileMenu.classList.toggle("open");

            }
        );

    }

});

</script>


</body>

</html>