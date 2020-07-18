<?php

namespace SilverStripe\Workflow\Tests;

use SilverStripe\Dev\SapphireTest;
use SilverStripe\Workflow\MarkingStore\DataObjectMarkingStore;
use SilverStripe\Workflow\WorkflowService;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Workflow\DefinitionBuilder;
use Symfony\Component\Workflow\SupportStrategy\InstanceOfSupportStrategy;
use Symfony\Component\Workflow\Transition;
use Symfony\Component\Workflow\Workflow;

class WorkflowServiceTest extends SapphireTest
{
    public static $extra_dataobjects = [
        TestObject::class,
    ];

    public function tearDown()
    {
        parent::tearDown();
        WorkflowService::reset();
    }

    public function testBadWorkflow()
    {
        $this->setUpBasicDataObjectWorkflow();

        $this->expectException(\PHPUnit_Framework_Error::class);
        $this->expectExceptionMessage('Transition "publish" is not enabled for workflow "unnamed".');
        
        $object = new TestObject();
        $object->write();

        // Publish (workflow action)
        $object->publish();
    }

    public function testGoodWorkflow()
    {
        $this->setUpBasicDataObjectWorkflow();
        
        $object = new TestObject();
        $object->CurrentState = 'reviewed';
        $object->write();

        // Publish (workflow action)
        $object->publish();

        $this->assertEquals('published', $object->CurrentState);
    }

    public function testLoggerEventSubscriber()
    {
        // Mock LoggerInterface for Test Subscriber
        // Little error_reporting hack to allow for getMockBuilder to work in PHP 7.4
        $er = error_reporting();
        error_reporting(E_ALL & ~E_STRICT & ~E_DEPRECATED);
        $mockLogger = $this->getMockBuilder(\Psr\Log\LoggerInterface::class)
            ->setMethods([
                'emergency',
                'alert',
                'critical',
                'error',
                'warning',
                'notice',
                'info',
                'debug',
                'log'
            ])
            ->getMock();
        error_reporting($er);

		$mockLogger->expects($this->once())
            ->method('info')
            ->with($this->equalTo('Blog post (id: "3") performed transition "publish" from "" to "published"'));
        
        // Create EventDispatcher and add Test Subscriber.
        $dispatcher = new EventDispatcher();
        $dispatcher->addSubscriber(new WorkflowServiceTest\TestLoggerEventSubscriber($mockLogger));
        
        // Create basic DataObject Workflow and give dispatcher to Workflow.
        $this->setUpBasicDataObjectWorkflow($dispatcher);

        $object = new TestObject();
        $object->CurrentState = 'reviewed';
        $object->write();

        // Publish (workflow action)
        $object->publish();
    }

    /**
     * Return Workflow using ViewableData MarkingStore and basic workflow.
     */
    private function setUpBasicDataObjectWorkflow($dispatcher = null)
    {
        $definition = $this->getTestBlogDefinition();

        $property = 'CurrentState'; // subject property name where the state is stored
        $marking = new DataObjectMarkingStore($property);
        $testWorkflow = new Workflow($definition, $marking, $dispatcher);
        
        WorkflowService::registry()->addWorkflow($testWorkflow, new InstanceOfSupportStrategy(TestObject::class));
    }

    /**
     * Return common Workflow to use
     */
    private function getTestBlogDefinition()
    {
        $definitionBuilder = new DefinitionBuilder();
        return $definitionBuilder->addPlaces(['draft', 'reviewed', 'rejected', 'published'])
            // Transitions are defined with a unique name, an origin place and a destination place
            ->addTransition(new Transition('to_review', 'draft', 'reviewed'))
            ->addTransition(new Transition('publish', 'reviewed', 'published'))
            ->addTransition(new Transition('reject', 'reviewed', 'rejected'))
            ->build()
        ;
    }
}
