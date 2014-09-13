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
                'submenu' => array(
                    array(
                        'menu_item_id' => 3,
                        'menu_id' => 1,
                        'page_id' => 4,
                        'display_name' => 'Nest One',
                        'submenu' => array(),
                    ),
                    array(
                        'menu_item_id' => 4,
                        'menu_id' => 1,
                        'page_id' => 5,
                        'display_name' => 'Nest Two',
                        'submenu' => array(),
                    ),
                    array(
                        'menu_item_id' => 5,
                        'menu_id' => 1,
                        'page_id' => 6,
                        'display_name' => 'Nest Three',
                        'submenu' => array(),
                    ),
                ),
            ),
            array(
                'menu_item_id' => 2,
                'menu_id' => 1,
                'page_id' => 2,
                'display_name' => 'Second',
                'submenu' => array(
                    array(
                        'menu_item_id' => 6,
                        'menu_id' => 1,
                        'page_id' => 7,
                        'display_name' => 'Two Nest One',
                        'submenu' => array(),
                    ),
                    array(
                        'menu_item_id' => 7,
                        'menu_id' => 1,
                        'page_id' => 8,
                        'display_name' => 'Two Nest Two',
                        'submenu' => array(),
                    ),
                ),
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
        $this->assertEmpty($this->arrayRecursiveDiff($parsed, $this->menu));
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

    /**
     * @test
     */
    public function trimNonSelectedMenus()
    {
        // Determine a page to be selected
        $config = array(
            'page_selected' => 8
        );

        // Parse the menu based on the config
        $parsed = $this->parser->parse($this->menu, $config);

        // Verify the first menu item no longer has submenu items to display
        $this->assertCount(0, $parsed[0]['submenu']);
    }

    /**
     * @test
     */
    public function pageNotFoundAndNothingSelected()
    {
        // Determine a page to be selected
        $config = array(
            'page_selected' => 999
        );

        // Parse the menu based on the config
        $parsed = $this->parser->parse($this->menu, $config);

        // Verify no main menu items have the is_selected flag
        foreach ( $parsed as $item ) {
            $this->assertFalse( $item['is_selected'] );
        }
    }

    /**
     * @test
     */
    public function shouldLimitToOneLevelDeep()
    {
        // Determine a page to be selected
        $config = array(
            'page_selected' => 8,
            'display_levels' => 1,
        );

        // Parse the menu based on the config
        $parsed = $this->parser->parse($this->menu, $config);

        // Loop through all main level items
        foreach ($parsed as $item) {
            // Ensure each main item no longer has sub menu items
            $this->assertCount(0, $item['submenu']);
        }
    }

    protected function arrayRecursiveDiff($aArray1, $aArray2) {
        $aReturn = array();

        foreach ($aArray1 as $mKey => $mValue) {
            if (array_key_exists($mKey, $aArray2)) {
                if (is_array($mValue)) {
                    $aRecursiveDiff = $this->arrayRecursiveDiff($mValue, $aArray2[$mKey]);
                    if (count($aRecursiveDiff)) { $aReturn[$mKey] = $aRecursiveDiff; }
                } else {
                    if ($mValue != $aArray2[$mKey]) {
                        $aReturn[$mKey] = $mValue;
                    }
                }
            } else {
                $aReturn[$mKey] = $mValue;
            }
        }
        return $aReturn;
    }
}