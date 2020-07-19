# Using the Event Dispatcher

An event dispatcher provides a method of attaching actions to registered events. As this module uses Symfony Workflow, it utelizes the `EventDispatcherInterface` to call a set of registered listeners which trigger other events.

## Workflow Dispatcher Events

See [Symfony Workflow Events](https://symfony.com/doc/current/workflow.html#using-events).

## Adding the EventDispatcher via WorkflowServiceFactory

If using the `WorkflowServiceFactory`, you can define a Dispatcher and attach it when creating your workflow in YAML using the Injector.

```yaml
SilverStripe\Core\Injector\Injector:
  WorkflowEventDispatcher:
    class: Symfony\Component\EventDispatcher\EventDispatcher

  MyWorkflow:
    factory: SilverStripe\Workflow\WorkflowServiceFactory
    constructor:
      marking_store:
        type: 'DataObject'
        property: 'CurrentState'
      supports:
        - SilverStripe\Workflow\Tests\TestObject
      dispatcher: '%$WorkflowEventDispatcher'
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

You can then add Listers or Subscribers to your dispatcher like follows. This listener 'completed' is called when the Transition that was called is completed.

```php
$dispatcher = Injector::inst()->get('WorkflowEventDispatcher');

$listener = new AcmeListener();
$dispatcher->addListener('workflow.MyWorkflow.completed', [$listener, 'onCompletedAction']);
```

## Adding the EventDispatcher via PHP

You can also add your dispatcher directly when constructing your Workflow.

```php
$definition = ...
$marking = ...

$dispatcher = new EventDispatcher();
$listener = new AcmeListener();

$dispatcher->addListener('workflow.MyWorkflow.completed', [$listener, 'onCompletedAction']);

$workflow = new Workflow($definition, $marking, $dispatcher, 'MyWorkflow');
```

## Listeners, Subscribers and EventDispatchers

For more information on Listeners, Subscribers and EventDispatchers, see the [Symfony Event Dispatcher](https://symfony.com/doc/current/components/event_dispatcher.html) documentation.
