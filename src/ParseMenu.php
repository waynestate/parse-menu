<?php namespace Waynestate\Menuitems;

/**
 * Class ParseMenu
 * @package Waynestate
 */
class ParseMenu {

    /**
     * @var
     */
    protected $menu;

    /**
     * @var
     */
    protected $path;

    /**
     * Parse the menu items array
     *
     * @param array $menu
     * @param array $config
     * @return array
     */
    function parse( array &$menu, $config = array() )
    {
        // Set the menu locally
        $this->menu = $menu;

        // If a page should be selected
        if ( isset($config['page_selected']) ) {
            // Find the first occurrence of the page_id
            $this->path = $this->findPath($this->menu, (int)$config['page_selected']);

            // Trim the non-needed limbs off the menu
            $this->menu = $this->trimMenu($this->menu);
        }

        // If there is a limit to the levels to display from the root
        if ( isset($config['display_levels']) || isset($config['skip_levels']) ) {
            // Set some defaults
            $skip = isset($config['skip_levels']) ? (int)$config['skip_levels'] : 0;
            $display = isset($config['display_levels']) ? (int)$config['display_levels'] : 1;

            // Ensure the display is greater than the skip
            // TODO: Probably should throw an error
            if ( $skip <= $display ) {
                // Slice off the beginning and end of the array if needed
                $this->menu = $this->menuSlice( $this->menu, $skip, $display );
            }
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
                $sub_found = $this->findPath($item['submenu'], $page_id);

                // Append the path to get the trail
                $path = array_merge($path, $sub_found);

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
            if ( in_array($item['menu_item_id'], $this-> path) ) {

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
     * @param $end
     * @param int $level
     * @return array
     */
    protected function menuSlice ( array $menu, $start, $end, $level = 0 )
    {
        // Start with a blank sliced array
        $slice_menu = array();

        // Loop through each menu item
        foreach ( $menu as $item ) {

            // If we are within the bounds of the slice
            if ( $level >= $start && $level < $end ) {

                // If there are submenu items, dive into that new level
                if ( ! empty($item['submenu']) ) {
                    $item['submenu'] = $this->menuSlice( $item['submenu'], $start, $end, ($level+1) );
                }

                // If in bounds, add the item to the new menu
                $slice_menu[] = $item;
            }
        }

        // Returned the sliced menu
        return $slice_menu;
    }
}
