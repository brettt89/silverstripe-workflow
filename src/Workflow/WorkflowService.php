<?php

namespace SilverStripe\Workflow;

use SilverStripe\Core\Resettable;
use SilverStripe\Core\Injector\Injector;

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
            self::$registry = Injector::inst()->get(Registry::class);
        }

        return self::$registry;
    }

    public static function reset()
    {
        self::$registry = null;
    }
}
