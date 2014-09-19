<?php namespace Waynestate\Menuitems;

use Waynestate\Menuitems\ParserInterface;
use Waynestate\Menuitems\InvalidDisplayLevelsException;
use Waynestate\Menuitems\InvalidSkipLevelsException;

/**
 * Class ParseMenu
 * @package Waynestate
 */
class ParseMenu implements ParserInterface {

    /**
     * @var
     */
    protected $menu;

    /**
     * @var
     */
    protected $path = array();

    /**
     * @param array $menu
     * @param array $config
     * @throws InvalidDisplayLevelsException
     * @throws InvalidSkipLevelsException
     * @return array
     */
    function parse( array &$menu, array $config = array() )
    {
        // Set the menu locally
        $this->menu = $menu;

        // Set a default levels to skip from root
        $skip = isset($config['skip_levels']) ? (int)$config['skip_levels'] : 0;

        // Set a default levels to display
        $display = isset($config['display_levels']) ? (int)$config['display_levels'] : 0;

        // If a page should be selected
        if ( isset($config['page_selected']) ) {
            // Find the first occurrence of the page_id
            $this->path = $this->findPath( $this->menu, (int)$config['page_selected'] );

            // Trim the non-needed limbs off the menu
            $this->menu = $this->trimMenu( $this->menu );
        }

        // If there is a need to skip levels from the root
        if ( $skip > 0 ) {
            $this->menu = $this->sliceFromRoot($this->menu, $skip);
        }

        // If there is a specified levels to display and it is smaller than the path
        if ( $display > 0 &&
             ( count($this->path) == 0 || $display <= ( count($this->path) - $skip ) )
        ) {
            $this->menu = $this->menuSlice( $this->menu, $display );
        }

        // The menu now has been modified with $config
        return $this->menu;
    }

    /**
     * @param array $menu
     * @param $page_id
     * @return array
     */
    protected function findPath( array $menu, $page_id )
    {
        // Start with no path
        $path = array();

        // Loop through each menu
        foreach ( $menu as $item ) {

            // If there is a submenu for this item
            if ( ! empty($item['submenu']) ) {

                // Try to look for the page within the submenu
                $sub_found = $this->findPath( $item['submenu'], $page_id );

                // Append the path to get the trail
                $path = array_merge( $path, $sub_found );

                // If there was something found build the path
                if ( $sub_found ) {
                    $path[] = $item['menu_item_id'];
                }
            }

            // If this individual item matches the page, keep track of it
            if ( $item['page_id'] == $page_id ) {
                $path[] = $item['menu_item_id'];
            }
        }

        // Return an array of menu_item_id's to show a trail to the page_id
        return $path;
    }

    /**
     * @param array $menu
     * @return array
     */
    protected function trimMenu( array $menu )
    {
        // Start with the blank new menu
        $path_menu = array();

        // Loop through each menu item
        foreach ($menu as $item) {
            // Default each menu item to not-selected
            $item['is_selected'] = false;

            // If this menu item is found in the path
            if ( in_array( $item['menu_item_id'], $this->path ) ) {

                // This item should be in the selected path
                $item['is_selected'] = true;

                // If there is a submenu trim it too
                if ( ! empty($item['submenu']) ) {
                    $item['submenu'] = $this->trimMenu( $item['submenu'] );
                }
            } else {
                // If this menu item is not in the path, ignore any submenus
                $item['submenu'] = array();
            }

            // Add this item to the newly trimmed menu
            $path_menu[] = $item;
        }

        // Return the trimmed menu
        return $path_menu;
    }

    /**
     * @param array $menu
     * @param $start
     * @param int $level
     * @throws InvalidSkipLevelsException
     * @return array
     */
    protected function sliceFromRoot( array $menu, $start, $level = 0 )
    {
        // Require the skip to be less than the selected path
        if ( $start > 0 && $start >= count($this->path) ) {
            throw new InvalidSkipLevelsException('Selected path must be deeper than the levels to skip.');
        }

        // If we have reached our start level
        if ( $level >= $start ) {

            // Return the rest of the menu
            return $menu;
        }

        // Loop through each menu item
        foreach ( $menu as $item ) {

            // If there are sub menu items
            if ( ! empty($item['submenu']) ) {

                // Dig deeper into the next level
                return $this->sliceFromRoot( $item['submenu'], $start, ++ $level );
            }
        }

        // If the menu does not have 'submenu' array (invalid $menu array format)
        return array();
    }

    /**
     * @param array $menu
     * @param $end
     * @param int $level
     * @throws InvalidDisplayLevelsException
     * @return array
     */
    protected function menuSlice ( array $menu, $end, $level = 1 )
    {
        // Require a path selection to display more than one level of the menu
        if ( $end > 1 && count($this->path) < 1 ) {
            throw new InvalidDisplayLevelsException('Page must be selected to display more than one level.');
        }

        // Start with a blank sliced array
        $slice_menu = array();

        // If we have reached the final level to display
        if ( $level > $end ) {

            // Chop off any submenus and return
            return array();
        } else {
            // Loop through each item in the menu
            foreach ( $menu as $item ) {

                // If there is a submenu
                if ( ! empty($item) ) {

                    // Dig deeper into the next level of the menu
                    $item['submenu'] = $this->menuSlice( $menu, $end, ++ $level );
                }

                // If in bounds, add the item to the new menu
                $slice_menu[] = $item;
            }
        }

        // Should never get to this point
        return $slice_menu;
    }
}
