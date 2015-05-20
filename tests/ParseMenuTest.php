<?php
use Waynestate\Menuitems\ParseMenu;

/**
 * Class ParseMenuTest
 */
class ParseMenuTest extends PHPUnit_Framework_TestCase
{
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
                'parent_id' => 0,
                'display_name' => 'First',
                'submenu' => array(
                    array(
                        'menu_item_id' => 3,
                        'menu_id' => 1,
                        'page_id' => 4,
                        'parent_id' => 1,
                        'display_name' => 'Nest One',
                        'submenu' => array(),
                    ),
                    array(
                        'menu_item_id' => 4,
                        'menu_id' => 1,
                        'page_id' => 5,
                        'parent_id' => 1,
                        'display_name' => 'Nest Two',
                        'submenu' => array(),
                    ),
                    array(
                        'menu_item_id' => 5,
                        'menu_id' => 1,
                        'page_id' => 6,
                        'parent_id' => 1,
                        'display_name' => 'Nest Three',
                        'submenu' => array(
                            array(
                                'menu_item_id' => 8,
                                'menu_id' => 1,
                                'page_id' => 9,
                                'parent_id' => 5,
                                'display_name' => 'Nest Nest One',
                                'submenu' => array(),
                            ),
                            array(
                                'menu_item_id' => 9,
                                'menu_id' => 1,
                                'page_id' => 10,
                                'parent_id' => 5,
                                'display_name' => 'Nest Nest Two',
                                'submenu' => array(),
                            ),
                        ),
                    ),
                ),
            ),
            array(
                'menu_item_id' => 2,
                'menu_id' => 1,
                'page_id' => 2,
                'parent_id' => 0,
                'display_name' => 'Second',
                'submenu' => array(
                    array(
                        'menu_item_id' => 6,
                        'menu_id' => 1,
                        'page_id' => 7,
                        'parent_id' => 2,
                        'display_name' => 'Two Nest One',
                        'submenu' => array(),
                    ),
                    array(
                        'menu_item_id' => 7,
                        'menu_id' => 1,
                        'page_id' => 8,
                        'parent_id' => 2,
                        'display_name' => 'Two Nest Two',
                        'submenu' => array(),
                    ),
                ),
            ),
            array(
                'menu_item_id' => 10,
                'menu_id' => 1,
                'page_id' => 11,
                'parent_id' => 0,
                'display_name' => 'Third',
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
        $this->assertEmpty($this->arrayRecursiveDiff($parsed['menu'], $this->menu));

        // Meta information should be default
        $this->assertFalse($parsed['meta']['has_selected']);
        $this->assertEquals(0, $parsed['meta']['depth']);
        $this->assertEmpty($parsed['meta']['path']);
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
        $this->assertTrue($parsed['menu'][0]['is_selected']);

        // Verify meta information matches
        $this->assertTrue($parsed['meta']['has_selected']);
        $this->assertEquals(1, $parsed['meta']['depth']);
        $this->assertEquals(array(1), array_values($parsed['meta']['path']));
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
        $this->assertCount(0, $parsed['menu'][0]['submenu']);

        // Verify meta information matches
        $this->assertTrue($parsed['meta']['has_selected']);
        $this->assertEquals(2, $parsed['meta']['depth']);
        $this->assertEquals(array(2, 7), array_values($parsed['meta']['path']));
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
        foreach ($parsed['menu'] as $item) {
            $this->assertFalse($item['is_selected']);
        }

        $this->assertFalse($parsed['meta']['has_selected']);
        $this->assertEquals(0, $parsed['meta']['depth']);
        $this->assertEmpty($parsed['meta']['path']);
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
        foreach ($parsed['menu'] as $item) {
            // Ensure each main item no longer has sub menu items
            $this->assertCount(0, $item['submenu']);
        }

        $this->assertTrue($parsed['meta']['has_selected']);
        $this->assertEquals(1, $parsed['meta']['depth']);
        $this->assertEquals(array(2), array_values($parsed['meta']['path']));
    }

    /**
     * @test
     */
    public function shouldLimitToMultipleDeep()
    {
        // Determine a page to be selected
        $config = array(
            'page_selected' => 5,
            'display_levels' => 2,
        );

        // Parse the menu based on the config
        $parsed = $this->parser->parse($this->menu, $config);

        // Loop through all main level items
        foreach ($parsed['menu'] as $item) {
            // If this item is in the path
            if ($item['is_selected']) {
                // There should be sub menu items
                $this->assertGreaterThan(0, count($item['submenu']));
            } else {
                // There should not be sub menu items
                $this->assertCount(0, $item['submenu']);
            }
        }

        $this->assertTrue($parsed['meta']['has_selected']);
        $this->assertEquals(2, $parsed['meta']['depth']);
        $this->assertEquals(array(1, 4), array_values($parsed['meta']['path']));
    }

    /**
     * @test
     */
    public function shouldSkipOneLevelFromRoot()
    {
        // Determine a page to be selected
        $config = array(
            'page_selected' => 5,
            'skip_levels' => 1,
        );

        // Parse the menu based on the config
        $parsed = $this->parser->parse($this->menu, $config);

        // Loop through all main level items
        foreach ($parsed['menu'] as $item) {
            // The parent_id of each of these items should not be the root '0' item
            $this->assertNotEquals(0, $item['parent_id']);
        }

        $this->assertTrue($parsed['meta']['has_selected']);
        $this->assertEquals(1, $parsed['meta']['depth']);
        $this->assertEquals(array(4), array_values($parsed['meta']['path']));
    }

    /**
     * @test
     */
    public function shouldSkipAndLimitDisplayLevels()
    {
        // Determine a page to be selected
        $config = array(
            'page_selected' => 10,
            'skip_levels' => 2,
            'display_levels' => 1,
        );

        // Parse the menu based on the config
        $parsed = $this->parser->parse($this->menu, $config);

        // Loop through all main level items
        foreach ($parsed['menu'] as $item) {
            // There should not be sub menu items
            $this->assertCount(0, $item['submenu']);
        }

        $this->assertTrue($parsed['meta']['has_selected']);
        $this->assertEquals(1, $parsed['meta']['depth']);
        $this->assertEquals(array(9), array_values($parsed['meta']['path']));
    }

    /**
     * @test
     */
    public function shouldAllowDisplayLevelOneWithoutPageSelection()
    {
        // Determine a page to be selected
        $config = array(
            'display_levels' => 1,
        );

        // Parse the menu based on the config
        $parsed = $this->parser->parse($this->menu, $config);

        // Loop through all main level items
        foreach ($parsed['menu'] as $item) {
            // There should not be sub menu items
            $this->assertCount(0, $item['submenu']);
        }

        $this->assertFalse($parsed['meta']['has_selected']);
        $this->assertEquals(0, $parsed['meta']['depth']);
        $this->assertEmpty($parsed['meta']['path']);
    }

    /**
     * @test
     * @expectedException Waynestate\Menuitems\InvalidDisplayLevelsException
     */
    public function shouldNotAllowDisplayLevelTwoWithoutPageSelection()
    {
        // Determine a page to be selected
        $config = array(
            'display_levels' => 2,
        );

        // Parse the menu based on the config
        $parsed = $this->parser->parse($this->menu, $config);
    }

    /**
     * @test
     */
    public function shouldAllowLargeDisplayLevelWithClosePageSelection()
    {
        // Determine a page to be selected
        $config = array(
            'page_selected' => 4,
            'display_levels' => 999,
        );

        // Parse the menu based on the config
        $parsed = $this->parser->parse($this->menu, $config);

        // Loop through all main level items
        foreach ($parsed['menu'] as $item) {
            // If this item is in the path
            if ($item['is_selected']) {
                // There should be sub menu items
                $this->assertNotCount(0, $item['submenu']);
            } else {
                // There should not be sub menu items
                $this->assertCount(0, $item['submenu']);
            }
        }

        $this->assertTrue($parsed['meta']['has_selected']);
        $this->assertEquals(2, $parsed['meta']['depth']);
        $this->assertEquals(array(1, 3), array_values($parsed['meta']['path']));
    }

    /**
     * @test
     * @expectedException Waynestate\Menuitems\InvalidSkipLevelsException
     */
    public function shouldNotAllowSkipMoreLevelsThanSelected()
    {
        // Determine a page to be selected
        $config = array(
            'page_selected' => 7,
            'skip_levels' => 2,
        );

        // Parse the menu based on the config
        $parsed = $this->parser->parse($this->menu, $config);
    }

    /**
     * @param $aArray1
     * @param $aArray2
     * @return array
     */
    protected function arrayRecursiveDiff($aArray1, $aArray2)
    {
        $aReturn = array();

        foreach ($aArray1 as $mKey => $mValue) {
            if (array_key_exists($mKey, $aArray2)) {
                if (is_array($mValue)) {
                    $aRecursiveDiff = $this->arrayRecursiveDiff($mValue, $aArray2[$mKey]);
                    if (count($aRecursiveDiff)) {
                        $aReturn[$mKey] = $aRecursiveDiff;
                    }
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
