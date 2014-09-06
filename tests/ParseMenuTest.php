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
        $this->menu = array();

    }

    /**
     * @test
     */
    public function noConfigNoChange()
    {
        $config = array(
            'page_selected' => 1
        );

        // Parse the promotions based on the config
        $parsed = $this->parser->parse($this->menu, $config);

        // Verify there is only one element in the 'one' group
        $this->assertEmpty(array_diff($parsed, $this->menu));
    }
}