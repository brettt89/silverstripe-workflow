<?php

namespace SilverStripe\Workflow\Tests;

use SilverStripe\Dev\SapphireTest;
use SilverStripe\Workflow\WorkflowFactory;
use SilverStripe\Workflow\WorkflowService;
use SilverStripe\Workflow\MarkingStore\DataObjectMarkingStore;

class WorkflowFactoryTest extends SapphireTest
{
    public static $extra_dataobjects = [
        TestObject::class,
    ];

    public function tearDown()
    {
        parent::tearDown();
        WorkflowService::reset();
    }
    
    /**
     * Test factory creation of Workflows.
     * Would be nicer if I could test using Yaml. But I guess this works.
     */
    public function testInjection() {
        $factory = new WorkflowFactory();
        $workflow = $factory->create('testWorkflow', [
            'marking_store' => [
                'type' => 'DataObject',
                'property' => 'CurrentState',
            ],
            'supports' => [
                TestObject::class,
            ],
            'initial_marking' => 'draft',
            'places' => [
                'draft',
                'reviewed',
                'rejected',
                'published',
            ],
            'transitions' =>[
                'to_review' =>[
                    'from' => 'draft',
                    'to' =>   'reviewed',
                ],
                'publish' => [
                    'from' => 'reviewed',
                    'to' =>   'published',
                ],
                'reject' => [
                    'from' => 'reviewed',
                    'to' =>   'rejected',
                ],
            ],
        ]);

        $obj = new TestObject();
        $this->assertTrue(WorkflowService::registry()->has($obj, 'testWorkflow'));
        $workflow = WorkflowService::registry()->get($obj, 'testWorkflow');

        $markingStore = $workflow->getMarkingStore();
        $this->assertInstanceOf(DataObjectMarkingStore::class, $markingStore);
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

        $workflow->apply($obj, 'to_review');

        $this->assertTrue($workflow->can($obj, 'publish'));
        $this->assertTrue($workflow->can($obj, 'reject'));
    }
}