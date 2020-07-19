<?php

namespace SilverStripe\Workflow\Tests;

use SilverStripe\Dev\SapphireTest;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Workflow\WorkflowService;
use SilverStripe\Workflow\MarkingStore\ViewableDataMarkingStore;
use Symfony\Component\Workflow\MarkingStore\MethodMarkingStore;

class WorkflowServiceFactoryTest extends SapphireTest
{
    public static $extra_dataobjects = [
        TestObject::class,
    ];
    
    /**
     * Test factory creation of Workflows.
     */
    public function testInjection() {
        $workflow = Injector::inst()->get('MyWorkflow');

        $obj = new TestObject();
        $this->assertTrue(WorkflowService::registry()->has($obj, 'MyWorkflow'));
        $workflow = WorkflowService::registry()->get($obj);

        $markingStore = $workflow->getMarkingStore();
        $this->assertInstanceOf(MethodMarkingStore::class, $markingStore);
        $places = $workflow->getDefinition()->getPlaces();
        $this->assertContains('draft', $places);
        $this->assertContains('reviewed', $places);
        $this->assertContains('rejected', $places);
        $this->assertContains('published', $places);

        $transitions = $workflow->getDefinition()->getTransitions();
        $this->assertCount(3, $transitions);

        $this->assertContains('draft', $transitions[0]->getFroms());
        $this->assertContains('reviewed', $transitions[1]->getFroms());
        $this->assertContains('reviewed', $transitions[2]->getFroms());

        $this->assertContains('reviewed', $transitions[0]->getTos());
        $this->assertContains('published', $transitions[1]->getTos());
        $this->assertContains('rejected', $transitions[2]->getTos());

        $this->assertTrue($workflow->can($obj, 'to_review'));
        $this->assertFalse($workflow->can($obj, 'publish'));

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
            ->with($this->equalTo('Blog post (id: "0") performed transition "to_review" from "" to "reviewed"'));
        
        // Create EventDispatcher and add Test Subscriber.
        $dispatcher = Injector::inst()->get('EventDispatcher');
        $dispatcher->addSubscriber(new TestLoggerEventSubscriber($mockLogger));

        $workflow->apply($obj, 'to_review');

        $this->assertTrue($workflow->can($obj, 'publish'));
        $this->assertTrue($workflow->can($obj, 'reject'));
    }

    public function testWithDifferentWorkflow() {
        $workflow = Injector::inst()->get('MySecondWorkflow');

        $obj = new TestObject();
        $this->assertTrue(WorkflowService::registry()->has($obj, 'MySecondWorkflow'));
        $workflow = WorkflowService::registry()->get($obj);

        $markingStore = $workflow->getMarkingStore();
        $this->assertInstanceOf(ViewableDataMarkingStore::class, $markingStore);
        
        $places = $workflow->getDefinition()->getPlaces();
        $this->assertContains('draft', $places);

        // Test that bad transition did not get added.
        $transitions = $workflow->getDefinition()->getTransitions();
        $this->assertCount(2, $transitions);

        $this->assertContains('reviewed', $transitions[0]->getFroms());
        $this->assertContains('reviewed', $transitions[1]->getFroms());

        $this->assertContains('published', $transitions[0]->getTos());
        $this->assertContains('rejected', $transitions[1]->getTos());
    }
}