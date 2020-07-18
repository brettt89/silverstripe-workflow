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

class WorkflowFactory implements InjectorFactory
{   
    const DEFAULT_METHOD = 'ViewableData';
    const DEFAULT_PROPERTY = 'CurrentState';
    
    /**
     * Creates a new service instance.
     *
     * @param string $service The class name of the service.
     * @param array $params The constructor parameters.
     * @return object The created service instances.
     */
    public function create($service, array $params = []) {
        // Start processing the parameters
        $definition = $this->getDefinition($params);
        $markingStore = $this->getMarkingStore($params);

        $supportClass = $this->getSupportClass($params);
        $supportStrategy = new InstanceOfSupportStrategy($supportClass);

        // Set initial marking on Object
        $initialMarking = array_key_exists('initial_marking', $params) && is_string($params['initial_marking']) 
            ? $params['initial_marking'] 
            : null;
        
        if ($initialMarking && Config::inst()->exists($supportClass, 'defaults')) {
            Config::modify()->merge($supportClass, 'defaults', [
                $initialMarking
            ]);
        }

        // Create workflow
        $workflow = new Workflow($definition, $markingStore, null, $service);
        
        // Register
        WorkflowService::registry()->addWorkflow($workflow, $supportStrategy);

        return $workflow;
    }

    /**
     * Get marking store by name.
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

    private function getDefinition(array $params = []): Definition {
        $places = array_key_exists('places', $params) && is_array($params['places']) 
            ? $params['places'] 
            : [];
        
        $_transitions = array_key_exists('transitions', $params) && is_array($params['transitions']) 
            ? $params['transitions'] 
            : [];
        
        $transitions = [];

        $builder = new DefinitionBuilder($places);
        foreach ($_transitions as $name => $direction) {
            if (!array_key_exists('to', $direction) || !array_key_exists('from', $direction)) {
                continue;
            }
            $builder->addTransition(new Transition($name, $direction['from'], $direction['to']));
        }

        return $builder->build();
    }

    private function getSupportClass(array $params = []) {
        return array_key_exists('supports', $params) && is_array($params['supports'])
            ? array_shift($params['supports']) 
            : null;
    }
}