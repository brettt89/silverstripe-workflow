<?php

use SilverStripe\ORM\DataExtension;
use Symfony\Component\Workflow\Registry;
use Symfony\Component\Workflow\Transition;
use Symfony\Component\Workflow\MarkingStore\MethodMarkingStore;
use Symfony\Component\Workflow\SupportStrategy\InstanceOfSupportStrategy;

namespace Silverstripe\Workflow;

class WorkflowExtension extends DataExtension
{   
    private static $db = [
        'CurrentState' => 'Varchar'
    ];
    
    /**
     * @var string
     */
    public $stateProperty;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var MarkingStoreInterface
     */
    private $markingStore;

    /**
     * Array of Extension points and corresponding Workflows and transitions
     * E.g.
     * array(
     *     'onBeforePublish' => 
     *         'DefaultPublishWorkflow' => 'publish'
     * )
     * @var array
     */
    private $extensions = [];

    /**
     * @param MarkingStoreInterface    Marking store
     */
    public function __construct(\Symfony\Component\Workflow\MarkingStore\MarkingStoreInterface $markingStore = null)
    {
        parent::construct();

        if (!isset($this->stateProperty)) {
            $this->stateProperty = array_key_first(self::$db);
        }

        $this->registry = new Registry();
        $this->markingStore = $method ?: new MethodMarkingStore(true, $this->stateProperty);
    }

    /**
     * Return current state from the database.
     * 
     * @return string
     */
    public function getCurrentState()
    {
        return $this->owner->CurrentState;
    }

    /**
     * Update the current state in the database.
     * 
     * @param string $state State of current workflow.
     */
    public function setCurrentState(string $state)
    {
        $this->owner->CurrentState = $state;
    }

    /**
     * Add Workflow to registry with a SupportStrategy defined for extended object.
     * 
     * @param WorkflowInterface                 $workflow           Workflow to add
     * @param WorkflowSupportStrategyInterface  $supportStrategy    Support Strategy to use
     */
    public function addWorkflowStrategy(
        Symfony\Component\Workflow\WorkflowInterface $workflow,
        \Symfony\Component\Workflow\SupportStrategy\WorkflowSupportStrategyInterface $supportStrategy
    ) {
        $this->registry->addWorkflow($workflow, $supportStrategy);
    }

    /**
     * Add Workflow to registry for extended object.
     * 
     * @param WorkflowInterface $workflow Workflow to add
     */
    public function addWorkflow(Symfony\Component\Workflow\WorkflowInterface $workflow) {
        $this->addWorkflowStrategy($workflow, new InstanceOfSupportStrategy($this->owner->getClassName()));
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
            if (is_array($this->extensions[$extension]) && count($this->extensions[$extension]) === 1) {
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
    public function getWorkflow(string $extension) {
        if ($this->hasWorkflow($extension)) {
            $wfName = array_key_first($this->extensions[$extension]);
            return $this->registry->get($this->owner, $wfName);
        }
    }

    /**
     * Set the Workflow transition to be associated with Extension.
     * 
     * @param string                    $extension     Extension name, null to remove workflows
     * @param string|WorfklowInterface  $workflow      Workflow name
     * @param string|Transition         $transition    Transition name
     */
    public function setWorkflowTransition(string $extension, $workflow, $transition) {
        if ($this->hasWorkflow($extension)) {
            user_error(sprintf('Extension \'%s\' alreay has a workflow', $extension), E_USER_WARNING);
        }

        if ($workflow instanceof WorfklowInterface) {
            $workflow = $workflow->getName();
        }

        if ($transition instanceof Transition) {
            $transition = $transition->getName();
        }

        $this->extensions[$extension] = [(string) $workflow => (string) $transition];
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
     * Get Workflow by extension name
     * 
     * @param string                Extension name (e.g. updateValidator)
     * 
     * @return WorkflowInterface    Workflow registered for this Extension.
     */
    public function getTransition(string $extension) {
        if ($this->hasWorkflow($extension)) {
            return reset($this->extensions[$extension]);
        }
    }

    /**
     * If Extension has a registered Workflow and transition. Execute it.
     */
    public function __call($func, $params) {
        $extension = $func;
        if (!$this->hasWorkflow($extension)) {
            return;
        }

        $workflow = $this->getWorkflow($extension);
        $transition = $this->getTransition($extension);

        if ($workflow->can($this->owner, $transition)) {
            $workflow->apply($this->owner, $transition);
        } else {
            if ($extension === 'validate' && count($params) >= 1) {
                $result = $params[0];
                $result->addError('Unable to transition to next phase');
                return $result;
            }


            // @todo ERROR? Warning? Stop things from happening?
            //
            // This is probably where the main stuff happpens
        }
    }
}