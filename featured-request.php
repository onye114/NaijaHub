<?php

// Send featured-listing requests through the
// proper page, which uses the Paystack payment flow.

header("Location: featured.php", true, 302);
exit;