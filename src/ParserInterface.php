<?php namespace Waynestate\Menuitems;

/**
 * Interface ParserInterface
 * @package Waynestate\Menuitems
 */
interface ParserInterface
{
    /**
     * @param array $menu
     * @param array $config
     * @return array
     */
    public function parse(array &$menu, array $config);
}