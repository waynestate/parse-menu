ParseMenu Class
============

Parse menus from the Wayne State University API

[![Latest Stable Version](https://poser.pugx.org/waynestate/parse-menu/v/stable.svg)](https://packagist.org/packages/waynestate/parse-menu)
[![Build Status](https://travis-ci.org/waynestate/parse-menu.svg?branch=develop)](https://travis-ci.org/waynestate/parse-menu)
[![Total Downloads](https://poser.pugx.org/waynestate/parse-menu/downloads.svg)](https://packagist.org/packages/waynestate/parse-menu)
[![License](https://poser.pugx.org/waynestate/parse-menu/license.svg)](https://packagist.org/packages/waynestate/parse-menu)


Installation
------------

To install this library, run the command below and you will get the latest version

    composer require waynestate/parse-menu
    
Usage
------------

Create the object

    # start.php

    use Waynestate\Menuitems\ParseMenu;

    ...

    $parseMenu = new ParseMenu;

Make an API call for the menu

    # controller.php

    try {
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
        
        // Get the breadcrumbs from the parsed menu $main_menu
        $breadcrumbs = array();
        if(count($site_menu['meta']['path']) > 0) {
            $breadcrumbs = $parseMenu->getBreadCrumbs($main_menu);
            
            // Add the site root crumb
            $root_crumb = [
                'display_name' => 'Home',
                'relative_url' => '/',
            ];
            $breadcrumbs = $parseMenu->prependBreadCrumb($breadcrumbs, $root_crumb);
        }
        
        // Just display the first level in the header
        $top_menu_config = array(
            'display_levels' => 1,
        );

        // Parse the existing menu with the specific top_menu config
        $top_menu = $parseMenu->parse($main_menu, $top_menu_config);

    } catch (Exception $e) {

        echo 'Caught exception: '.  $e->getMessage(). "\n";
    }
    
Return values

    $main_menu['meta'] = [
        'has_selected' => boolean, // default: false
        'has_submenu' => boolean, // default: false
        'depth' => integer, // default: 0
        'path' => array, // default: array()
    ]
    
    $main_menu['menu'] = [
        // Array of the menu
    ]
    
    $breadcrumbs = [
        // Sequential array of individual breadcrumbs in order based on the $main_menu['meta']['path']
    ]
    
Config Options

    'page_selected' = Page ID for selection path (optional)
    'skip_levels' = Number of levels to skip from the root (requires page_selected)
    'display_levels' = Number of levels to display from the root (requires page_selected, if > 1)
    'full_menu' = Return the full menu regardless if there is a page selected (boolean, default: false)
    TODO: 'show_levels' = Number of levels to display from the leaf (requires page_selected)
    TODO: 'add_home' = Add 'Home' as the first menu item (this may not be needed)

Exceptions

    InvalidDisplayLevelsException = If 'display_levels' > 1 and no 'page_selected' found

Tests

    phpunit

Code Coverage

    phpunit --coverage-html ./coverage
