<?php

namespace Silverstripe\Workflow\MarkingStore;

use SilverStripe\ORM\DataObject;
use Symfony\Component\Workflow\Marking;
use Symfony\Component\Workflow\Exception\InvalidArgumentException;

class DataObjectMarkingStore extends ViewableDataMarkingStore
{
    /**
     * {@inheritdoc}
     *
     * @todo - Currently $context is not used.
     */
    public function setMarking(object $subject, Marking $marking, array $context = [])
    {
        if (!$subject instanceof DataObject) {
            throw new InvalidArgumentException(sprintf('"%s" is not an instance of "%s"', get_debug_type($subject), DataObject::class));
        }

        parent::setMarking($subject, $marking, $context);

        $subject->write();
    }
}