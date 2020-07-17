# SilverStripe Workflow Module

![PHPUnit Tests](https://github.com/brettt89/silverstripe-workflow/workflows/PHPUnit%20Tests/badge.svg)

## Overview

This Workflow module uses a static interface for interacting with the WorkflowService. You can call Workflow actions from anywhere needed. 

Provides `symfony/workflow` functionality to SilverStripe Framework.

## Requirements

 * SilverStripe Framework 4.x
 * Symfony Workflow 5.x

## Installation

```
composer require silverstripe/workflow
```

## Documentation

This uses the Symfony Workflow component and brings its features forward to SilverStripe through the class WorkflowService.

https://symfony.com/doc/current/components/workflow.html

Example uses.

src/MyApp/_config.php
```
use SilverStripe\Workflow\ViewableData_MarkingStore;
use SilverStripe\Workflow\WorkflowService;
use Symfony\Component\Workflow\DefinitionBuilder;
use Symfony\Component\Workflow\SupportStrategy\InstanceOfSupportStrategy;
use Symfony\Component\Workflow\Transition;
use Symfony\Component\Workflow\Workflow;

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

// Add your workflow to the Registry
WorkflowService::registry()->addWorkflow($testWorkflow, new InstanceOfSupportStrategy(ServiceTest\TestObject::class));
```

src/MyApp/Blog.php
```
namespace MyApp;

use SilverStripe\ORM\DataObject;
use SilverStripe\Workflow\WorkflowService;

class Blog extends DataObject
{
    private static $db = [
        'CurrentState' => 'Varchar',
    ];

    public function publishComment($comment)
    {
        $workflow = WorkflowService::registry()->get($this);
        if (!$workflow->can($this, 'publish')) {
            trigger_error('Not able to move to next phase');
            exit();
        }
        workflow->apply($this, 'publish');
        // {Publish $comment code}
    }

    public function approveComment($comment)
    {
        $workflow = WorkflowService::registry()->get($this);
        if (!$workflow->can($this, 'reviewed')) {
            trigger_error('Not able to move to next phase');
            exit();
        }
        workflow->apply($this, 'reviewed');
        // {Approve $comment code}
    }
}

```

## Contributing
