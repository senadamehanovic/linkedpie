<?php
namespace ChadwickMarketing\SocialLite\view\menu;
use ChadwickMarketing\SocialLite\base\UtilsProvider;

// @codeCoverageIgnoreStart
defined('ABSPATH') or die('No script kiddies please!'); // Avoid direct file request
// @codeCoverageIgnoreEnd

/**
 * Creates a WordPress backend menu page and demontrates a React component (public/ts/admin.tsx).
 *
 */
class Page {
    use UtilsProvider;

    const ROOT_ID = SOCIAL_LITE_SLUG . '-root';


    /**
     * Add new menu page.
     */
    public function admin_menu() {

        add_menu_page('Social', 'Social', 'manage_options', self::ROOT_ID, [
            $this,
            'render_component_library'
        ], 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIiB3aWR0aD0iMTgiIGhlaWdodD0iMTgiIHZpZXdCb3g9IjAgMCAxOCAxOCI+CiAgPGRlZnM+CiAgICA8Y2xpcFBhdGggaWQ9ImNsaXAtWmVpY2hlbmZsw6RjaGVfMSI+CiAgICAgIDxyZWN0IHdpZHRoPSIxOCIgaGVpZ2h0PSIxOCIvPgogICAgPC9jbGlwUGF0aD4KICA8L2RlZnM+CiAgPGcgaWQ9IlplaWNoZW5mbMOkY2hlXzEiIGRhdGEtbmFtZT0iWmVpY2hlbmZsw6RjaGUg4oCTIDEiIGNsaXAtcGF0aD0idXJsKCNjbGlwLVplaWNoZW5mbMOkY2hlXzEpIj4KICAgIDxyZWN0IGlkPSJSZWNodGVja18zIiBkYXRhLW5hbWU9IlJlY2h0ZWNrIDMiIHdpZHRoPSIxOCIgaGVpZ2h0PSIxOCIgcng9IjQiIGZpbGw9IiNmZmYiLz4KICAgIDxwYXRoIGlkPSJQZmFkXzEiIGRhdGEtbmFtZT0iUGZhZCAxIiBkPSJNMS41NjYuMDMxQzIuNDc5LjAzMSwyLjktLjQxOCwyLjktLjk0M2MwLTEuMy0yLjItLjcwOS0yLjItMS42ODgsMC0uMzU3LjI5MS0uNjQ4Ljk0NC0uNjQ4YTEuODM4LDEuODM4LDAsMCwxLC45NzkuM2wuMTI4LS4zQTIuMDE1LDIuMDE1LDAsMCwwLDEuNjQyLTMuNmMtLjkwOCwwLTEuMzE2LjQ1NC0xLjMxNi45NzksMCwxLjMyMSwyLjIuNzE5LDIuMiwxLjcsMCwuMzUyLS4yOTEuNjMyLS45NTkuNjMyQTEuNzUzLDEuNzUzLDAsMCwxLC4zODgtLjcyNEwuMjQtLjQzM0ExLjkzNiwxLjkzNiwwLDAsMCwxLjU2Ni4wMzFaTTQuNDU3LjAyNUExLjMyLDEuMzIsMCwwLDAsNS44MTktMS4zNDEsMS4zMTUsMS4zMTUsMCwwLDAsNC40NTctMi43LDEuMzE5LDEuMzE5LDAsMCwwLDMuMDkxLTEuMzQxLDEuMzI0LDEuMzI0LDAsMCwwLDQuNDU3LjAyNVptMC0uMzIxYS45ODIuOTgyLDAsMCwxLTEtMS4wNDYuOTgyLjk4MiwwLDAsMSwxLTEuMDQ1Ljk3OC45NzgsMCwwLDEsLjk5NSwxLjA0NUEuOTc4Ljk3OCwwLDAsMSw0LjQ1Ny0uM1pNNy4zOS4wMjVBMS4xODMsMS4xODMsMCwwLDAsOC40NDEtLjVMOC4xNy0uNjg4QS45MDcuOTA3LDAsMCwxLDcuMzktLjMuOTgzLjk4MywwLDAsMSw2LjM3NS0xLjM0MS45ODYuOTg2LDAsMCwxLDcuMzktMi4zODdhLjkxMi45MTIsMCwwLDEsLjc4LjRsLjI3LS4xODRBMS4xNzIsMS4xNzIsMCwwLDAsNy4zOS0yLjcsMS4zMiwxLjMyLDAsMCwwLDYuMDA4LTEuMzQxLDEuMzI1LDEuMzI1LDAsMCwwLDcuMzkuMDI1Wk05LjA1My0zLjI2OWEuMjYuMjYsMCwwLDAsLjI2NS0uMjY1LjI1Ny4yNTcsMCwwLDAtLjI2NS0uMjUuMjYxLjI2MSwwLDAsMC0uMjY1LjI1NUEuMjYyLjI2MiwwLDAsMCw5LjA1My0zLjI2OVpNOC44NjksMGguMzYyVi0yLjY4M0g4Ljg2OVpNMTAuOS0yLjdhMS43LDEuNywwLDAsMC0xLjA5MS4zNjJsLjE2My4yN2ExLjM2NywxLjM2NywwLDAsMSwuODkyLS4zMTZjLjUsMCwuNzYuMjUuNzYuNzA5di4xNjNoLS44NTJjLS43NywwLTEuMDM1LjM0Ny0xLjAzNS43NiwwLC40NjQuMzcyLjc4Ljk3OS43OGExLjAwNywxLjAwNywwLDAsMCwuOTIzLS40NDRWMGguMzQ3Vi0xLjY2M0EuOTUzLjk1MywwLDAsMCwxMC45LTIuN1pNMTAuNzcxLS4yNmMtLjQyOCwwLS42NzgtLjE5NC0uNjc4LS41LDAtLjI3NS4xNjgtLjQ3OS42ODktLjQ3OWguODQydi40MzlBLjg1OC44NTgsMCwwLDEsMTAuNzcxLS4yNlpNMTIuNjg5LDBoLjM2MlYtMy43ODRoLS4zNjJaIiB0cmFuc2Zvcm09InRyYW5zbGF0ZSgyIDEwLjcpIi8+CiAgPC9nPgo8L3N2Zz4K');

    }

    /**
     * Render the content of the menu page.
     */
    public function render_component_library() {
        echo '<div id="' . self::ROOT_ID . '" class="wrap"></div>';
    }

    /**
     * New instance.
     */
    public static function instance() {
        return new Page();
    }
}