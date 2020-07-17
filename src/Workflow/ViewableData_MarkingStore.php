<?php

namespace SilverStripe\Workflow;

use Symfony\Component\Workflow\Exception\LogicException;
use Symfony\Component\Workflow\Marking;
use Symfony\Component\Workflow\MarkingStore\MarkingStoreInterface;

class ViewableData_MarkingStore implements MarkingStoreInterface
{
    private $singleState;
    private $property;

    /**
     * @param string $property Used to determine methods to call
     *                         The `getMarking` method will use `return $subject->Property`
     *                         The `setMarking` method will use `$subject->Property = (string)`
     */
    public function __construct(bool $singleState = false, string $property = 'marking')
    {
        $this->singleState = $singleState;
        $this->property = $property;
    }

    /**
     * {@inheritdoc}
     */
    public function getMarking(object $subject): Marking
    {
        if (!$subject->hasField($this->property)) {
            throw new LogicException(sprintf('Field "%s" does not exist in "%s".', $this->property, get_debug_type($subject)));
        }

        $marking = $subject->getField($this->property);

        if (null === $marking) {
            return new Marking();
        }

        if ($this->singleState) {
            $marking = [(string) $marking => 1];
        }

        return new Marking($marking);
    }

    /**
     * {@inheritdoc}
     *
     * @todo - Currently $context is not used.
     */
    public function setMarking(object $subject, Marking $marking, array $context = [])
    {
        $marking = $marking->getPlaces();

        if ($this->singleState) {
            $marking = key($marking);
        }

        if (!$subject->hasField($this->property)) {
            throw new LogicException(sprintf('Field "%s" does not exist in "%s".', $this->property, get_debug_type($subject)));
        }

        $subject->{$this->property} = $marking;
    }
}
