<?php

use SilverStripe\Dev\SapphireTest;
use SilverStripe\View\ViewableData;
use Silverstripe\Workflow\WorkflowExtension;

class WorkflowExtensionTest extends SapphireTest
{
    protected static $required_extensions = [
        ViewableData::class => [WorkflowExtension::class]
    ];

    /**
     * @useDatabase false
     */
    public function testExtensions() {
        $data = new ViewableData();
    }
}