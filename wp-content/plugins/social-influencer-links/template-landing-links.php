<?php
/*
Template: WPM Social Influencer Links Template
*/
\show_admin_bar( false );

\the_post();

$links = new \WP_Query([
    'post_type' => 'wpm-landing-link',
    'posts_per_page' => 999,
    'orderby' => [
        'menu_order' => 'asc',
        'date' => 'desc',
        'title' => 'asc',
    ]
]);

$theme = \get_field( 'wpmll_opt_theme' ) == 'Dark' ? 'dark' : 'light';

?>
<!doctype html>
<html>
<head>
    <title><?php \wp_title( '' ); ?></title>
    
    <meta name="viewport" content="width=device-width"/>
    
    <link rel="preconnect" href="https://fonts.gstatic.com">

    <?php
        \wp_head(); 
        echo \get_field( 'wpmll_opt_embed-head' ); 
    ?>

    <style type="text/css">
    body.wpm-ll-body {
        <?php if ( $value = \get_field( 'wpmll_opt_bg_color' ) ) : ?>
        background: <?php echo \esc_html( $value ); ?> !important;
        <?php endif; ?>
        <?php if ( $value = \get_field( 'wpmll_opt_text_color' ) ) : ?>
        color: <?php echo \esc_html( $value ); ?> !important;
        <?php endif; ?>
    }
    <?php if ( $value = \get_field( 'wpmll_opt_link_color' ) ) : ?>
    body.wpm-ll-body a:not(.wpm-ll-button) { color: <?php echo \esc_html( $value ); ?> !important; }
    <?php endif; ?>
    </style>
</head>
<body class="wpm-ll-body wpm-ll-body-<?php echo \esc_attr( $theme ); ?>">

    <?php
        \wp_body_open(); 
        echo \get_field( 'wpmll_opt_embed-body' );
    ?>

    <section id="wpm-ll-main">
        <header>
            <?php if ( \has_post_thumbnail() ) : ?>
            <div class="wpm-ll-thumb">
                <?php \the_post_thumbnail( 'thumbnail' ); ?>
            </div>
            <?php endif; ?>
            <div class="wpm-ll-title">
                <h1><?php \the_title(); ?></h1>
            </div>
            <div class="wpm-ll-intro">
                <?php \the_content(); ?>
            </div>
        </header>
        <article>
            
            <?php if ( $links->have_posts() ) : ?>

            <ol class="wpm-ll-links">

                <?php while ( $links->have_posts() ) : $links->the_post(); ?>

                <li id="wpm-ll-link-<?php echo (int) \get_the_ID(); ?>">

                    <a href="<?php echo \esc_url( \get_field( 'wpmll_url' ) ); ?>" target="_blank" style="<?php
                        if ( $value = \get_field( 'wpmll_color_bg' ) )
                        {
                            echo 'background-color:' . \esc_attr( $value ) . ';';
                        }
                        if ( $value = \get_field( 'wpmll_color_text' ) )
                        {
                            echo 'color:' . \esc_attr( $value ) . ';';
                        }
                    ?>" class="wpm-ll-button">

                        <?php \the_title(); ?>
                        
                        <?php if ( $byline = \get_field( 'wpmll_byline' ) ) : ?>
                        
                        <span class="wpm-ll-link-desc">
                            <?php echo \esc_html( $byline ); ?>
                        </span>

                        <?php endif; ?>
                    </a>

                </li>

                <?php endwhile; \wp_reset_query(); ?>

            </ol>

            <?php endif; ?>

        </article>
        <footer id="wpm-ll-footer">
            <div class="wpm-ll-footer-content">
                
                <?php $social = \WPM\Influencer_Links::get_social_links( \get_the_ID() ); if ( \count( $social ) > 0 ) : ?>
                <div class="wpm-ll-social">
                    <?php 
                        $out = '';
                        foreach ( $social as $network => $url ) 
                        {
                            $label = \esc_html( \WPM\Influencer_Links::get_social_label( $network ) );
                            $out .= '<a target="_blank" href="' . \esc_url( $url ) .'" title="' . $label . '"><span>' 
                                 . $label . '</span></a> | ';
                        }
                        echo \rtrim( $out, ' | ' );
                    ?>
                </div>
                <?php endif; ?>

                <?php if ( $outro = \get_field( 'wpmll_opt_outro' ) ) : ?>

                <div class="wpm-ll-footer-outro"><?php echo $outro; ?></div>

                <?php endif; ?>

            </div>
        </footer>
        <?php if ( \get_field( 'wpmll_opt_builtwith' ) ) : ?>
        <div class="wpm-ll-built">
            <p>
                Generated with <a href="https://wordpress.org/plugins/social-influencer-links/">Social Influencer Links</a> 
                plugin from <a href="https://wpmaintainer.com" target="_blank">WP Maintainer</a>.
            </p>
        </div>
        <?php endif; if ( \current_user_can( 'edit_page' ) ) : ?>
        <div class="wpm-ll-edit"><p><?php \edit_post_link( 'Edit This Page' ); ?></p></div>
        <?php endif; ?>
    </section>

    <?php
        \wp_footer(); 
        echo \get_field( 'wpmll_opt_embed-footer' ); 
    ?> 

</body>
</html>
