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
        }

        var_dump($this->path);

        return $menu;
    }

    protected function findPath( array &$menu, $page_id )
    {
        $path = array();

        foreach ( $menu as $item ) {
            if ( count($item['submenu']) > 0 ) {
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
}
