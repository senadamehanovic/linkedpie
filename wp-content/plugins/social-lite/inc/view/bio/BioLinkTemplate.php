<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <?php wp_head(); ?>
</head>

<body>
    <div id="<?php echo SOCIAL_LITE_SLUG . '-root'; ?>" data-bio-link-id="<?php echo get_the_ID(); ?>"></div>
    <?php wp_footer(); ?>
</body>

</html>