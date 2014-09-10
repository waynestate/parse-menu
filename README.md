ParseMenu Class
============

Parse menus from the Wayne State University API

Usage
------------

Pull in with composer

    # composer.json

    {
        "require": {
            "waynestate/parse-promos": "0.1.*"
        }
    }

Create the object

    # start.php

    use Waynestate\Promotions\ParsePromos;

    ...

    $parseMenu = new ParseMenu;

Make an API call for the menu

    # controller.php

    // Get the menu items and have them ready for display
    $params = array(
        'site_id' => 1,
        'menu_id' => 2,
        'ttl' => TTL,
    );
    $menus = $api->sendRequest('cms.menuitems.listing', $params);

    // Menu config
    $menu_config = array(
        'page_selected' => 1,
    );

    // Get a final array to display the main menu
    $main_menu = $parseMenu->parse($menus[2], $menu_config);

    $top_menu_config = array(
        'display_levels' => 1,
    );

    // Just display the first level to display in the header
    $top_menu = $parseMenu->parse($main_menu, $top_menu_config);

Config Options

    'page_selected' = Page ID for selection path
    TODO: 'skip_levels' = Number of levels to skip from the root (requires page_selected)
    TODO: 'display_levels' = Number of levels to display from the root (requires page_selected, if > 1)
    TODO: 'show_levels' = Number of levels to display from the leaf (requires page_selected)
    TODO: 'add_home' = Add 'Home' as the first menu item (this may not be needed)

