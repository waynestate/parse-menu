ParseMenu Class
============

Parse menus from the Wayne State University API

[![Latest Stable Version](https://poser.pugx.org/waynestate/parse-menu/v/stable.svg)](https://packagist.org/packages/waynestate/parse-menu)
[![Build Status](https://travis-ci.org/waynestate/parse-menu.svg?branch=develop)](https://travis-ci.org/waynestate/parse-menu)
[![Total Downloads](https://poser.pugx.org/waynestate/parse-menu/downloads.svg)](https://packagist.org/packages/waynestate/parse-menu)
[![License](https://poser.pugx.org/waynestate/parse-menu/license.svg)](https://packagist.org/packages/waynestate/parse-menu)

Usage
------------

Pull in with composer

    # composer.json

    {
        "require": {
            "waynestate/parse-menu": "0.1.*"
        }
    }

Create the object

    # start.php

    use Waynestate\Menuitems\ParseMenu;

    ...

    $parseMenu = new ParseMenu;

Make an API call for the menu

    # controller.php

    // Pull a specific menu from the API for a site
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

    // Just display the first level in the header
    $top_menu_config = array(
        'display_levels' => 1,
    );

    // Parse the existing menu with the specific top_menu config
    $top_menu = $parseMenu->parse($main_menu, $top_menu_config);

Config Options

    'page_selected' = Page ID for selection path
    'skip_levels' = Number of levels to skip from the root (requires page_selected)
    'display_levels' = Number of levels to display from the root (requires page_selected, if > 1)
    TODO: 'show_levels' = Number of levels to display from the leaf (requires page_selected)
    TODO: 'add_home' = Add 'Home' as the first menu item (this may not be needed)

Tests

    phpunit

Code Coverage

    phpunit --coverage-html ./coverage