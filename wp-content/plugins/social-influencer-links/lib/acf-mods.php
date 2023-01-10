<?php
if ( \defined( 'WPM_SIL_DEVELOPER' ) ) 
{
    \add_filter( 'acf/settings/save_json', function( $path ){
        $path = WPM_SIL_ACF_SAVE_PATH;
        return $path;
    });
}

\add_filter( 'acf/settings/load_json', function( $paths ){
    $paths[] = WPM_SIL_ACF_SAVE_PATH;
    return $paths;
});