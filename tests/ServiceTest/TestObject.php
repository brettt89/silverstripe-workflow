<?php

namespace SilverStripe\Workflow\Tests\ServiceTest;

use SilverStripe\Dev\TestOnly;
use SilverStripe\ORM\DataObject;
use SilverStripe\Workflow\WorkflowService;
use Symfony\Component\Workflow\Exception\LogicException;

class TestObject extends DataObject implements TestOnly
{
    private static $table_name = 'WorkflowTest_DataObject';

    private static $db = [
        'CurrentState' => 'Varchar',
    ];

    public function onBeforeWrite()
    {
        parent::onBeforeWrite();

        try {
            WorkflowService::registry()->get($this)->apply($this, 'publish');
        } catch (LogicException $e) {
            user_error($e->getMessage(), E_USER_ERROR);
            exit();
        }
    }
}
