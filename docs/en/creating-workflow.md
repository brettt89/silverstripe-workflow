# Creating a Workflow

Workflows can be created using YML via the Injector, or directly in PHP. For more information on using Workflows, we recommend checking out the [Symfony Workflow Documentation](https://symfony.com/doc/current/workflow.html).

## Creating a Workflow using the Factory

This module comes with a `WorkflowServiceFactory` which can be used to create (and register) a workflow using the same format as documented on the [Symfony Workflow Documentation](https://symfony.com/doc/current/workflow.html).

[Example YML](./examples/workflow.yml)
```yaml
SilverStripe\Core\Injector\Injector:
  MyWorkflow:
    factory: SilverStripe\Workflow\WorkflowServiceFactory
    constructor:
      marking_store:
        type: 'DataObject'
        property: 'CurrentState'
      supports:
        - SilverStripe\Workflow\Tests\TestObject
      initial_marking: draft
      places:
        - draft
        - reviewed
        - rejected
        - published
      transitions:
        to_review:
          from: draft
          to:   reviewed
        publish:
          from: reviewed
          to:   published
        reject:
          from: reviewed
          to:   rejected
```

This will create your Workflow and register it with the `WorkflowService` registry. which you can then get by name anywhere in your code.

```php
use SilverStripe\Workflow\Tests\TestObject;

$workflow = WorkflowService::registry()->get(new TestObject(), 'MyWorkflow');
```

## Creating a Workflow in PHP

The below example was based upon the [Symfony Workflow Documentation](https://symfony.com/doc/current/workflow.html). We would recommend reading those docs to get the full picture of creating a workflow.

```php
use SilverStripe\Workflow\MarkingStore\DataObjectMarkingStore;
use Symfony\Component\Workflow\DefinitionBuilder;
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

$property = 'currentState'; // subject property name where the state is stored
$marking = new DataObjectMarkingStore($property);
$workflow = new Workflow($definition, $marking);
```

You can then add your Workflow directly to the `WorkflowService` registry to be referenced elsewhere in your codebase.

```php
WorkflowService::registry()->addWorkflow($workflow, new InstanceOfSupportStrategy(BlogPost::class));
```

## (Advanced) Manually creating your Workflow using YML

While using the `WorkflowServiceFactory` for building your workflows is simple, you might find it lacking in flexibility. This is where creating your resources individually and using the Injector directly may suit your needs better.

- [Manual Workflow - Example YML](./examples/manual-workflow.yml)

The above example displays how you might create your workflow specifying a bit more detail. You can then use the Injector to get your Workflow (or individual components).

```php
$workflow = Injector::inst()->get('MyWorkflow');
WorkflowService::registry()->addWorkflow($workflow, new InstanceOfSupportStrategy(BlogPost::class));
```