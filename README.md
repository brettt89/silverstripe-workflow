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

## Introduction

The [WorkflowService](src/Workflow/WorkflowService.php) class is your primary engagement point for all Workflow related activies. It holds a static registry of all Workflows created for your application.

The registry returned by [WorkflowService](src/Workflow/WorkflowService.php) is a [Symfony Workflow Registry](https://github.com/symfony/symfony/blob/master/src/Symfony/Component/Workflow/Registry.php) class.

You can interact with the Workflow Registry by calling the `registry()` command on the [WorkflowService](src/Workflow/WorkflowService.php) class.

```
$registry = WorkflowService::registry();
```

## Adding a Symfony Workflow

Creating a workflow in PHP is the same process as with Symfony Workflow documentation.

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
$workflow = new Workflow($definition, $marking);
```

Then add the Workflow to the Registry.

```
WorkflowService::registry()->addWorkflow($testWorkflow, new InstanceOfSupportStrategy(MyApp\MyDataObject::class));
```

> A `ViewableData_MarkingStore` class has been provided for easy usage with DataObjects and any other class that extends `ViewableData`. Using this store will allow you to define a Database Field as the "State Storage" propery.

## Impementing a Workflow

Your Workflow can be accessed from anywher inside of a SilverStripe application, so usage of Workflows can be expanded on almost anywhere. Below is a example of how a Workflow might be used when publishing a comment on a blog article.

```
namespace MyApp;

use SilverStripe\ORM\DataObject;
use SilverStripe\Workflow\WorkflowService;

class BlogArtice extends DataObject
{   
    // ...
    
    public function publishComment(Comment $comment)
    {
        $workflow = WorkflowService::registry()->get($comment);

        // Update the currentState on the post
        try {
            $workflow->apply($post, 'publish');
        } catch (LogicException $exception) {
            // ...
        }
        // ...
    }

    public function rejectComment(Comment $comment)
    {
        $workflow = WorkflowService::registry()->get($comment);

        // Update the currentState on the post
        try {
            $workflow->apply($post, 'reject');
        } catch (LogicException $exception) {
            // ...
        }
        // ...
    }
}

```

## Documentation

For further information on usage of Workflows and other functionality such as Events, etc. See the [Symfony Workflow](https://symfony.com/doc/current/workflow.html) documentation.

## Contributing
