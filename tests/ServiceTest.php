<?php

namespace SilverStripe\Workflow\Tests;

use SilverStripe\Dev\SapphireTest;
use SilverStripe\Workflow\ViewableData_MarkingStore;
use SilverStripe\Workflow\WorkflowService;
use Symfony\Component\Workflow\DefinitionBuilder;
use Symfony\Component\Workflow\SupportStrategy\InstanceOfSupportStrategy;
use Symfony\Component\Workflow\Transition;
use Symfony\Component\Workflow\Workflow;

class ServiceTest extends SapphireTest
{
    public static $extra_dataobjects = [
        ServiceTest\TestObject::class,
    ];

    public function setUp()
    {
        parent::setUp();
        $definitionBuilder = new DefinitionBuilder();
        $definition = $definitionBuilder->addPlaces(['draft', 'reviewed', 'rejected', 'published'])
            // Transitions are defined with a unique name, an origin place and a destination place
            ->addTransition(new Transition('to_review', 'draft', 'reviewed'))
            ->addTransition(new Transition('publish', 'reviewed', 'published'))
            ->addTransition(new Transition('reject', 'reviewed', 'rejected'))
            ->build()
        ;

        $singleState = true; // true if the subject can be in only one state at a given time
        $property = 'CurrentState'; // subject property name where the state is stored
        $marking = new ViewableData_MarkingStore($singleState, $property);
        $testWorkflow = new Workflow($definition, $marking);

        WorkflowService::registry()->addWorkflow($testWorkflow, new InstanceOfSupportStrategy(ServiceTest\TestObject::class));
    }

    /**
     * @expectedException \PHPUnit_Framework_Error
     */
    public function testBadWorkflow()
    {
        $object = new ServiceTest\TestObject();
        $object->write();
    }

    public function testGoodWorkflow()
    {
        $object = new ServiceTest\TestObject();
        $object->CurrentState = 'reviewed';
        $object->write();
    }
}
