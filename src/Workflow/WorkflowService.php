<?php

namespace SilverStripe\Workflow;

use SilverStripe\Core\Resettable;
use Symfony\Component\Workflow\Registry;
use SilverStripe\Core\Injector\Injectable;

class WorkflowService implements Resettable
{
    use Injectable;
    
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
