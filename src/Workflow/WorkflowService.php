<?php

namespace SilverStripe\Workflow;

use SilverStripe\Core\Resettable;
use Symfony\Component\Workflow\Registry;

class WorkflowService implements Resettable
{
    /**
     * @var Registry Workflow Registry
     */
    private static $registry;

    /**
     * Public method for getting Registry.
     *
     * @return Registry
     */
    public static function registry()
    {
        if (!self::$registry) {
            self::$registry = new Registry();
        }

        return self::$registry;
    }

    public static function reset()
    {
        self::$registry = null;
    }
}
