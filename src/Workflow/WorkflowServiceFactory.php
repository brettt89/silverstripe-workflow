<?php

namespace SilverStripe\Workflow;

use SilverStripe\Core\Injector\Factory as InjectorFactory;
use SilverStripe\Core\Config\Config;
use SilverStripe\Workflow\MarkingStore\ViewableDataMarkingStore;
use SilverStripe\Workflow\MarkingStore\DataObjectMarkingStore;
use Symfony\Component\Workflow\Definition;
use Symfony\Component\Workflow\DefinitionBuilder;
use Symfony\Component\Workflow\MarkingStore\MarkingStoreInterface;
use Symfony\Component\Workflow\MarkingStore\MethodMarkingStore;
use Symfony\Component\Workflow\Workflow;
use Symfony\Component\Workflow\Transition;
use Symfony\Component\Workflow\SupportStrategy\InstanceOfSupportStrategy;

class WorkflowServiceFactory implements InjectorFactory
{   
    const DEFAULT_METHOD = 'ViewableData';
    const DEFAULT_PROPERTY = 'CurrentState';

    public $markingStore;
    
    /**
     * Creates a new service instance.
     *
     * @param string $service The 'Name' of the workflow
     * @param array $params The constructor parameters.
     * 
     * @return object The created service instances.
     */
    public function create($service, array $params = []) {
        // Start processing the parameters
        $definition = $this->getDefinition($params);
        $markingStore = $this->getMarkingStore($params);

        $supportClass = $this->getSupportClass($params);
        $supportStrategy = new InstanceOfSupportStrategy($supportClass);

        // Create workflow
        $workflow = new Workflow($definition, $markingStore, null, $service);
        
        // Register
        WorkflowService::registry()->addWorkflow($workflow, $supportStrategy);

        return $workflow;
    }

    /**
     * Create MarkingStore from Factory Parameters
     * 
     * @param array $params Array of parameters passed to self::create() function.
     * @return MarkingStoreInterface
     */
    private function getMarkingStore(array $params = []): MarkingStoreInterface {
        if (!array_key_exists('marking_store', $params)) {
            return new ViewableDataMarkingStore(true, self::DEFAULT_PROPERTY);
        }

        $type = array_key_exists('type', $params['marking_store']) 
            ? $params['marking_store']['type'] 
            : self::DEFAULT_METJOD;
        
        $property = array_key_exists('property', $params['marking_store']) 
            ? $params['marking_store']['property'] 
            : self::DEFAULT_PROPERTY;

        switch (strtolower($type)) {
            case 'method':
                return new MethodMarkingStore(true, $property);
                break;
            case 'dataobject':
                return new DataObjectMarkingStore($property);
                break;
            case 'viewabledata':
                return new ViewableDataMarkingStore($property);
                break;
            default:
                if (array_key_exists('supports', $params) && is_array($params['supports']) && count($params['supports'])) {
                    // @todo add support for multiple datatypes and stores.
                    $className = array_shift($params['supports']);

                    if (is_a($className, DataObject::class)) {
                        return new DataObjectMarkingStore($property);
                    } elseif (is_a($className, ViewableData::class)) {
                        return new ViewableDataMarkingStore($property);
                    }
                }

                return new MethodMarkingStore(true, $property);
                break;
        }
    }

    /**
     * Create Definition from Factory Parameters
     * 
     * @param array $params Array of parameters passed to self::create() function.
     * @return Definition
     */
    private function getDefinition(array $params = []): Definition {
        $places = array_key_exists('places', $params) && is_array($params['places']) 
            ? $params['places'] 
            : [];
        
        $_transitions = array_key_exists('transitions', $params) && is_array($params['transitions']) 
            ? $params['transitions'] 
            : [];

        // Create builder with Places.
        $builder = new DefinitionBuilder($places);

        // If initial_marking is set, then push it to builder
        if (array_key_exists('initial_marking', $params)) {
            $builder->setInitialPlaces($params['initial_marking']);
        }

        // Add Transactions
        $transitions = [];
        foreach ($_transitions as $name => $direction) {
            if (!array_key_exists('to', $direction) || !array_key_exists('from', $direction)) {
                continue;
            }
            $builder->addTransition(new Transition($name, $direction['from'], $direction['to']));
        }

        // Create Definition.
        return $builder->build();
    }

    /**
     * Get support class from Factory Parameters
     * 
     * @param array $params Array of parameters passed to self::create() function.
     * @return mixed
     */
    private function getSupportClass(array $params = []) {
        return array_key_exists('supports', $params) && is_array($params['supports'])
            ? array_shift($params['supports']) 
            : null;
    }
}