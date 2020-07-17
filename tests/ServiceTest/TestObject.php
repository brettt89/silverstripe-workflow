<?php

namespace SilverStripe\Workflow\Tests\ServiceTest;

use SilverStripe\Dev\TestOnly;
use SilverStripe\ORM\DataObject;
use SilverStripe\Workflow\WorkflowService;

class TestObject extends DataObject implements TestOnly
{
    private static $table_name = 'WorkflowTest_DataObject';

    private static $db = [
        'CurrentState' => 'Varchar',
    ];

    public function onBeforeWrite()
    {
        parent::onBeforeWrite();
        $workflow = WorkflowService::registry()->get($this);
        if (!$workflow->can($this, 'publish')) {
            trigger_error('Not able to move to next phase');
            exit();
        }
    }
}
