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
        if ( ! empty($config['page_selected']) ) {
            // Find the first occurrence of the page_id
            $this->path = $this->findPath($this->menu, (int)$config['page_selected']);

            // Trim the non-needed limbs off the menu
            $this->menu = $this->trimMenu($this->menu);
        }

        // If there is a limit to the levels to display from the root
        if ( ! empty($config['display_levels']) || ! empty($config['skip_levels']) ) {
            $this->menu = $this->menuSlice( $this->menu, (int)$config['skip_levels'], (int)$config['display_levels'] );
        }

        return $this->menu;
    }

    /**
     * @param array $menu
     * @param $page_id
     * @return array
     */
    protected function findPath( array $menu, $page_id )
    {
        $path = array();

        foreach ( $menu as $item ) {
            if ( ! empty($item['submenu']) ) {
                $sub_found = $this->findPath($item['submenu'], $page_id);
                $path = array_merge($path, $sub_found);

                if ( $sub_found ) {
                    $path[] = $item['menu_item_id'];
                }
            }

            if ( $item['page_id'] == $page_id ) {
                $path[] = $item['menu_item_id'];
            }
        }

        return $path;
    }

    /**
     * @param array $menu
     * @return array
     */
    protected function trimMenu( array $menu )
    {
        $path_menu = array();

        foreach ($menu as $item) {
            if ( in_array($item['menu_item_id'], $this-> path) ) {
                $item['is_selected'] = true;

                if ( ! empty($item['submenu']) ) {
                    $item['submenu'] = $this->trimMenu( $item['submenu'] );
                }
            } else {
                $item['is_selected'] = false;
                $item['submenu'] = array();
            }

            $path_menu[] = $item;
        }

        return $path_menu;
    }

    protected function menuSlice ( array $menu, $start, $end, $level = 0 )
    {
        $slice_menu = array();

        foreach ( $menu as $item ) {
            if ( $level >= $start && $level < $end ) {
                if ( ! empty($item['submenu']) ) {
                    $item['submenu'] = $this->menuSlice( $item['submenu'], $start, $end, ($level+1) );
                }

                $slice_menu[] = $item;
            }

        }

        return $slice_menu;
    }
}
