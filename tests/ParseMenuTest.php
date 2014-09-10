<?php
use Waynestate\Menuitems\ParseMenu;

/**
 * Class ParseMenuTest
 */
class ParseMenuTest extends PHPUnit_Framework_TestCase {
    /**
     * @var
     */
    protected $menu;

    /**
     * @var
     */
    protected $parser;

    /**
     * Setup
     */
    protected function setUp()
    {
        // Create the parser
        $this->parser = new ParseMenu();

        // Stub
        $this->menu = array(
            array(
                'menu_item_id' => 1,
                'menu_id' => 1,
                'page_id' => 1,
                'display_name' => 'First',
                'submenu' => array(),
            ),
            array(
                'menu_item_id' => 2,
                'menu_id' => 1,
                'page_id' => 3,
                'display_name' => 'Second',
                'submenu' => array(),
            ),
        );

    }

    /**
     * @test
     */
    public function noConfigNoChange()
    {
        // No configuration options
        $config = array(
        );

        // Parse the menu
        $parsed = $this->parser->parse($this->menu, $config);

        // There should be no different in the base and resulting array
        // TODO: Make this investigate multi-dimensions
        $this->assertEmpty(array_diff($parsed, $this->menu));
    }

    /**
     * @test
     */
    public function pageSelectionBoolean()
    {
        // Determine a page to be selected
        $config = array(
            'page_selected' => 1
        );

        // Parse the menu based on the config
        $parsed = $this->parser->parse($this->menu, $config);

        // Verify menu item has a boolean flag
        $this->assertTrue($parsed[0]['is_selected']);
    }
}