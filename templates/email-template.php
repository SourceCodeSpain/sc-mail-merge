<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
?>
<!doctype html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo esc_html($subject); ?></title>
    </head>
    <body>
        <div class="container">
            <p><?php echo wp_kses_post($message); ?></p>
        </div>
    </body>
</html>