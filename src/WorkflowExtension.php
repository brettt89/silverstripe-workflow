<?php

namespace Silverstripe\Workflow;

use SilverStripe\ORM\DataExtension;
use SilverStripe\ORM\ValidationException;
use SilverStripe\Core\Extensible;
use Symfony\Component\Workflow\Registry;
use Symfony\Component\Workflow\Exception\LogicException;
use \Exception;

class WorkflowExtension extends DataExtension
{   
    /**
     * Array of Extension points and corresponding Workflows and transitions
     * E.g.
     * array(
     *     'onBeforePublish' => 
     *         'DefaultPublishWorkflow' => 'publish'
     * )
     * @var array
     */
    private $extensions;
    
    /**
     * @var Registry
     */
    private $registry;

    /**
     * Dependancy mapping for Injector
     */
    static $dependencies = [
        'extension' => [],
        'registry'  => '%$Registry'
    ];

    /**
     * @param array $extension Extensions to register.
     */
    public function __construct(array $extensions = [], Registry $registry = null)
    {
        parent::construct();

        // Extensible trait is required.
        if (!in_array(Extensible::class, class_uses($this->owner))) {
            throw new Exception(sprintf('Unable to extend, Class \'%s\' must use Extensible Trait'), $this->owner->getClassName());
        }

        if ($registry) {
            $this->setRegistry($registry);
        }

        $this->setExtensions($extensions);
    }

    /**
     * Public method for setting all Extensions in one hit
     * 
     * @param array $extensions
     */
    public function setExtensions(array $extensions) {
        foreach ($extensions as $extension => $wfData) {
            foreach ($wfData as $workflow => $transition) {
                $this->setWorkflowTransition($extension, $workflow, $transition);
            }
        }
    }

    /**
     * Public method for setting Registry
     * 
     * @param array $extensions
     */
    public function setRegistry(Registry $registry) {
        $this->registry = $registry;
    }

    /**
     * Public method for setting Registry
     * 
     * @param array $extensions
     */
    public function getRegistry() {
        return $this->registry;
    }

    /**
     * Check if a workflow exists for Extension
     * 
     * @param string $extension Extension name
     * 
     * @return bool
     */
    public function hasWorkflow(string $extension) {
        if (array_key_exists($extension, $this->extensions)) {
            if (is_array($this->extensions[$extension]) && count($this->extensions[$extension]) >= 1) {
                foreach ($this->extensions[$extension] as $workflow => $transition) {
                    if (!is_string($workfow) || !is_string($transition)) {
                        return false;
                    }
                }
                return true;
            }
        }
        return false;
    }

    /**
     * Get Workflow by extension name
     * 
     * @param string                Extension name (e.g. updateValidator)
     * 
     * @return WorkflowInterface    Workflow registered for this Extension.
     */
    public function getWorkflows(string $extension) {
        if ($this->hasWorkflow($extension)) {
            return $this->extensions[$extension];
        }
    }

    /**
     * Set the Workflow transition to be associated with Extension.
     * 
     * @param string                    $extension     Extension name, null to remove workflows
     * @param string|WorfklowInterface  $workflow      Workflow name
     * @param string|Transition         $transition    Transition name
     */
    public function setWorkflowTransition(string $extension, string $workflow, string $transition) {
        if (!isset($this->extensions[$extension])) {
            $this->extensions[$extension] = [(string) $workflow => (string) $transition];
        } else {
            $this->extensions[$extension][(string) $workflow] = (string) $transition;
        }
    }

    /**
     * Remove Workflow from Extension.
     * 
     * @param string $extension Extension name
     */
    public function removeWorkflow(string $extension) {
        unset($this->extensions[$extension]);
    }

    /**
     * If Extension has a registered Workflow and transition. Execute it.
     * 
     * @param string $func Name of function being called.
     * @param array $params Array of parameters provided to funciton (ignored)
     * 
     * @throws ValidationException
     */
    public function __call($func, $params) {
        if (!$this->hasWorkflow($func)) {
            return;
        }

        foreach($this->getWorkflows($func) as $workflowName => $transition) {
            $workflow = $this->getRegistry()->get($this->owner, $workflowName);
            try {
                $workflow->apply($this->owner, $transition);
            } catch (LogicException $e) {
                throw new ValidationException($e->getMessage(), $e->getCode());
            }
        }
    }
}