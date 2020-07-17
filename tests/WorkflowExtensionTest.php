<?php

use SilverStripe\Dev\SapphireTest;
use SilverStripe\View\ViewableData;
use Silverstripe\Workflow\WorkflowExtension;

class WorkflowExtensionTest extends SapphireTest
{
    /** 
     * Defines the fixture file to use for this test class
     * @var string
     */
    protected static $fixture_file = 'WorkflowExtensionTest.yml';

    protected static $required_extensions = [
        ViewableData::class => [WorkflowExtension::class]
    ];

    public function testExtensions() {
        $data = $this->objFromFixture(ViewableData::class, 'jack');
    }
}