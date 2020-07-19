<?php

namespace SilverStripe\Workflow\MarkingStore;

use SilverStripe\View\ViewableData;
use Symfony\Component\Workflow\Exception\InvalidArgumentException;
use Symfony\Component\Workflow\Exception\LogicException;
use Symfony\Component\Workflow\Marking;
use Symfony\Component\Workflow\MarkingStore\MarkingStoreInterface;

class ViewableDataMarkingStore implements MarkingStoreInterface
{
    private $property;

    /**
     * @param string $property Used to determine methods to call
     *                         The `getMarking` method will use `return $subject->getField(Property)`
     *                         The `setMarking` method will use `$subject->setField(property, (string))`
     */
    public function __construct(string $property = 'marking')
    {
        $this->property = $property;
    }

    /**
     * {@inheritdoc}
     */
    public function getMarking(object $subject): Marking
    {
        if (!$subject instanceof ViewableData) {
            throw new InvalidArgumentException(sprintf('"%s" is not an instance of "%s"', get_debug_type($subject), ViewableData::class));
        }

        if (!$subject->hasField($this->property)) {
            throw new LogicException(sprintf('Field "%s" does not exist in subject "%s"', $this->property, get_debug_type($subject)));
        }

        $marking = $subject->getField($this->property);

        if (null === $marking) {
            return new Marking();
        }

        return new Marking([(string) $marking => 1]);
    }

    /**
     * {@inheritdoc}
     *
     * @todo - Currently $context is not used.
     */
    public function setMarking(object $subject, Marking $marking, array $context = [])
    {
        if (!$subject instanceof ViewableData) {
            throw new InvalidArgumentException(sprintf('"%s" is not an instance of "%s"', get_debug_type($subject), ViewableData::class));
        }

        if (!$subject->hasField($this->property)) {
            throw new LogicException(sprintf('Field "%s" does not exist in subject "%s"', $this->property, get_debug_type($subject)));
        }

        $marking = $marking->getPlaces();

        $subject->setField($this->property, key($marking));
    }
}
