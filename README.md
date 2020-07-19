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
composer require silverstripe/silverstripe-workflow
```

## Introduction

The `WorkflowService` class is your primary engagement point for all Workflow related activies. It holds a static registry of all Workflows created for your application.

The registry returned by `WorkflowService` is a [Symfony Workflow Registry](https://github.com/symfony/symfony/blob/master/src/Symfony/Component/Workflow/Registry.php) class.

You can interact with the Workflow Registry by calling the `registry()` command on the `WorkflowService` class.

```
$registry = WorkflowService::registry();
```

## Documentation \ How to

For SilverStripe documentation, see [SilverStripe Workflow Documentation](./docs/en/index.md).

For further information on usage of Workflows and other functionality such as Events, etc. See the [Symfony Workflow](https://symfony.com/doc/current/workflow.html) documentation.

## Contributing

Please submit all contributions to this repository as pull requests to the `master` branch.