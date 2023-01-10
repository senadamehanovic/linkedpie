<?php

use linkinbio\Controllers\LinkInBioSettings;
use linkinbio\Controllers\LinkInBioCPT;
$links = new WP_Query(['post_type' => LinkInBioCPT::POST_TYPE_SLUG, 'order' => 'ASC', 'orderby' => 'menu_order', 'posts_per_page' => -1]);
$settings = LinkInBioSettings::instance();
$thumb = $settings->get_setting('thumb');
$name = $settings->get_setting('name');
$name_url = $settings->get_setting('name_url');
?>
<!DOCTYPE html>
<html <?php 
language_attributes();
?>>
<head>

    <meta charset="<?php 
bloginfo('charset');
?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    <title><?php 
echo esc_html($post->post_title);
?></title>

    <?php 
wp_head();
?>
	<style>
	   .linkinbio-container ul li a,
	   .linkinbio-container ul li a:visited {
	       background-color: <?php 
echo $settings->get_setting('btn_color');
?>;
	       color: <?php 
echo $settings->get_setting('link_color');
?>;
	   }
	   .linkinbio-container ul li a:hover {
	       background-color: <?php 
echo $settings->get_setting('btn_hover_color');
?>;
	       color: <?php 
echo $settings->get_setting('link_hover_color');
?>;
	   }
	   .linkinbio-container img {
	       width: <?php 
echo $settings->get_setting('thumb_size');
?>px;
	   }
	   body.linkinbio {
	       background-color: <?php 
echo $settings->get_setting('bg_color');
?>;
	   }
	</style>
</head>
<body class="linkinbio">
    <div class="linkinbio-container">
    	<?php 
if (!empty($thumb)) {
    ?>
    		<img src="<?php 
    echo $thumb;
    ?>" alt="<?php 
    echo $name;
    ?>">
    	<?php 
}
?>
    	<?php 
if (!empty($name)) {
    ?>
    		<h1>
    			<?php 
    echo !empty($name_url) ? "<a href='{$name_url}'>{$name}</a>" : $name;
    ?>
    		</h1>
    	<?php 
}
?>
    	<ul>
		<?php 
while ($links->have_posts()) {
    $links->the_post();
    $link_url = get_post_meta(get_the_ID(), LinkInBioCPT::META_URL, true);
    the_title("<li><a href='" . esc_url($link_url) . "'>", "</a></li>");
}
?>
		</ul>
		<a class="linkinbio-credits" href="https://contentandcreations.nl/link-in-bio/?utm_source=<?php 
echo urlencode(home_url() . $_SERVER['REQUEST_URI']);
?>&utm_medium=footer_credits&utm_campaign=linkinbio">Link In Bio By Content & Creations</a>
    </div>
<?php 
wp_footer();
?>
</body>
</html>