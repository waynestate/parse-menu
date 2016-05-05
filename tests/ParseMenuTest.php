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
                'is_active' => true,
                'display_name' => 'First',
                'submenu' => array(
                    array(
                        'menu_item_id' => 3,
                        'menu_id' => 1,
                        'page_id' => 4,
                        'parent_id' => 1,
                        'is_active' => true,
                        'display_name' => 'Nest One',
                        'submenu' => array(),
                    ),
                    array(
                        'menu_item_id' => 4,
                        'menu_id' => 1,
                        'page_id' => 5,
                        'parent_id' => 1,
                        'is_active' => true,
                        'display_name' => 'Nest Two',
                        'submenu' => array(),
                    ),
                    array(
                        'menu_item_id' => 5,
                        'menu_id' => 1,
                        'page_id' => 6,
                        'parent_id' => 1,
                        'is_active' => true,
                        'display_name' => 'Nest Three',
                        'submenu' => array(
                            array(
                                'menu_item_id' => 8,
                                'menu_id' => 1,
                                'page_id' => 9,
                                'parent_id' => 5,
                                'is_active' => true,
                                'display_name' => 'Nest Nest One',
                                'submenu' => array(),
                            ),
                            array(
                                'menu_item_id' => 9,
                                'menu_id' => 1,
                                'page_id' => 10,
                                'parent_id' => 5,
                                'is_active' => true,
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
                'is_active' => true,
                'display_name' => 'Second',
                'submenu' => array(
                    array(
                        'menu_item_id' => 6,
                        'menu_id' => 1,
                        'page_id' => 7,
                        'parent_id' => 2,
                        'is_active' => false,
                        'display_name' => 'Two Nest One',
                        'submenu' => array(),
                    ),
                    array(
                        'menu_item_id' => 7,
                        'menu_id' => 1,
                        'page_id' => 8,
                        'parent_id' => 2,
                        'is_active' => false,
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
                'is_active' => true,
                'display_name' => 'Third',
                'submenu' => array(),
            ),
        );
    }

    /**
     * @test
     */
    public function noConfigWillTrimAllButFirstLevel()
    {
        // No configuration options
        $config = array(
        );

        // Parse the menu
        $parsed = $this->parser->parse($this->menu, $config);

        foreach($parsed['menu'] as $item) {
            // No sub menus should be found
            $this->assertCount(0, $item['submenu']);

            // is_selected should be applied to all menu items
            $this->assertArrayHasKey('is_selected', $item);
        }

        // Meta information should be default
        $this->assertFalse($parsed['meta']['has_selected']);
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
        $this->assertTrue($parsed['menu'][1]['is_selected']);

        // Verify meta information matches
        $this->assertTrue($parsed['meta']['has_selected']);
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
        $this->assertCount(0, $parsed['menu'][1]['submenu']);

        // Verify meta information matches
        $this->assertTrue($parsed['meta']['has_selected']);
        $this->assertEquals(array(2, 7), array_values($parsed['meta']['path']));
    }

    /**
     * @test
     */
    public function noPageSelectedShouldReturnFullMenu()
    {
        // No configuration options
        $config = array(
            'page_selected' => 8,
            'full_menu' => true,
        );

        // Parse the menu
        $selected_menu = $this->parser->parse($this->menu, $config);

        // Verify the first menu item no longer has submenu items to display
        $this->assertCount(count($this->menu[0]['submenu']), $selected_menu['menu'][1]['submenu']);

        // No configuration options
        $config = array(
            'full_menu' => true,
        );

        // Parse the menu
        $full_menu = $this->parser->parse($this->menu, $config);

        // Verify the first menu item no longer has submenu items to display
        $this->assertCount(count($this->menu[0]['submenu']), $full_menu['menu'][1]['submenu']);
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
        $this->assertEmpty($parsed['meta']['path']);
    }

    /**
     * @test
     */
    public function breadcrumbsShouldMatchPathCount()
    {
        // No configuration options
        $config = array(
            'page_selected' => 9,
        );

        $parsed = $this->parser->parse($this->menu, $config);
        $breadcrumbs = $this->parser->getBreadCrumbs($parsed);

        // # of breadcrumbs in $breadcrumbs is the same as the parsed path count
        $this->assertCount(count($parsed['meta']['path']), $breadcrumbs);
    }

    /**
     * @test
     */
    public function breadcrumbsMenuItemIDShouldBeInPath()
    {
        // No configuration options
        $config = array(
            'page_selected' => 9,
        );

        $parsed = $this->parser->parse($this->menu, $config);
        $breadcrumbs = $this->parser->getBreadCrumbs($parsed);

        // # of breadcrumbs in $breadcrumbs is the same as the parsed path count
        foreach((array)$breadcrumbs as $key => $crumb){
            $this->assertContains($crumb['menu_item_id'], $parsed['meta']['path']);
        }
    }

    /**
     * @test
     */
    public function prependBreadCrumbsShouldAddBreadCrumb()
    {
        // No configuration options
        $config = array(
            'page_selected' => 9,
        );

        $parsed = $this->parser->parse($this->menu, $config);
        $breadcrumbs = $this->parser->getBreadCrumbs($parsed);

        $root_crumb = array(
                'menu_item_id' => 11,
                'menu_id' => 1,
                'page_id' => 11,
                'parent_id' => 0,
                'is_active' => true,
                'display_name' => 'BreadCrumb',
                'submenu' => null
        );

        $breadcrumbs = $this->parser->prependBreadCrumb($breadcrumbs, $root_crumb);

        $this->assertEquals($breadcrumbs[0], $root_crumb);
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
     * @test
     */
    public function shouldNotHaveSubMenuWithNoItems()
    {
        // Ensure the page selected should not have a sub menu
        $config = array(
            'page_selected' => 11,
        );

        // Parse the menu based on the config
        $parsed = $this->parser->parse($this->menu, $config);

        // This page should not have a submenu
        $this->assertFalse($parsed['meta']['has_submenu']);
    }

    /**
     * @test
     */
    public function shouldNotHaveSubMenuWithInactiveItems()
    {
        // Ensure the page selected should not have a sub menu
        $config = array(
            'page_selected' => 2,
        );

        // Parse the menu based on the config
        $parsed = $this->parser->parse($this->menu, $config);

        // This page should not have a submenu
        $this->assertFalse($parsed['meta']['has_submenu']);
    }

    /**
     * @test
     */
    public function shouldHaveSubMenuAtLevelOne()
    {
        // Ensure the page selected should not have a sub menu
        $config = array(
            'page_selected' => 1,
        );

        // Parse the menu based on the config
        $parsed = $this->parser->parse($this->menu, $config);

        // This page should not have a submenu
        $this->assertTrue($parsed['meta']['has_submenu']);
    }

    /**
     * @test
     */
    public function shouldHaveSubMenuAtInteriorLevel()
    {
        // Ensure the page selected should not have a sub menu
        $config = array(
            'page_selected' => 6,
        );

        // Parse the menu based on the config
        $parsed = $this->parser->parse($this->menu, $config);

        // This page should not have a submenu
        $this->assertTrue($parsed['meta']['has_submenu']);
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
