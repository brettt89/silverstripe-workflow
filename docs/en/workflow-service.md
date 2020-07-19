# Using WorkflowService

WorkflowService is a registry service that you can register and retrieve Workflows from anywhere in your codebase.

## Getting the registry

Workflows can be added to the registry of this service to store and access them as needed. You can access the registry by calling `registry()` on `WorkflowService`.
```php
$registry = WorkflowService::registry();
```

## Adding a Workflow to the registry

You can add a workflow to by calling `addWorkflow()` on the registry.

```php
$workflow = ...
WorkflowService::registry()->addWorkflow($workflow, new InstanceOfSupportStrategy(BlogPost::class));
```

## Resetting the registry

You can reset the registry at anytime by calling `reset()`. This will clear all workflows from the registry.

```php
WorkflowService::reset();
```

