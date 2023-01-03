<!DOCTYPE html>
<html>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<title><?php wp_title( '|', true, 'right' ); ?></title>
<link rel="stylesheet" href="<?php echo esc_url( get_stylesheet_uri() ); ?>" type="text/css" />
<?php wp_head(); ?>
</head>
<body>
<?php 
    wp_nav_menu(['menu' => 'primary', 'container' => '',
    'theme_location' => 'primary', 'items_wrap' => '<ul id="" class="navbar-nav me-auto mb-2 mb-lg-0">%3$s</ul>',]);

    var_dump(get_header());
?>

<h1 class="hej"> hejdå</h1>
