<?php

namespace SilverStripe\Workflow\Tests;

use SilverStripe\Dev\SapphireTest;
use SilverStripe\View\ViewableData;
use SilverStripe\Workflow\MarkingStore\DataObjectMarkingStore;
use SilverStripe\Workflow\MarkingStore\ViewableDataMarkingStore;
use Symfony\Component\Workflow\Marking;
use Symfony\Component\Workflow\Exception\InvalidArgumentException;
use Symfony\Component\Workflow\Exception\LogicException;

class MarkingStoreTest extends SapphireTest
{
    public function testDataObjectSetBadSubject() {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('"stdClass" is not an instance of "SilverStripe\ORM\DataObject"');
        
        $markingStore = new DataObjectMarkingStore();
        $markingStore->setMarking(new \stdClass(), new Marking());
    }

    public function testViewableDataSetBadSubject() { 
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('"stdClass" is not an instance of "SilverStripe\View\ViewableData"');
        
        $markingStore = new ViewableDataMarkingStore();
        $markingStore->setMarking(new \stdClass(), new Marking());
    }

    public function testViewableDataSetBadProperty() {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Field "marking" does not exist in subject "SilverStripe\View\ViewableData"');

        $markingStore = new ViewableDataMarkingStore();
        $markingStore->setMarking(new ViewableData(), new Marking());
    }

    public function testViewableDataGetBadSubject() { 
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('"stdClass" is not an instance of "SilverStripe\View\ViewableData"');
        
        $markingStore = new ViewableDataMarkingStore();
        $markingStore->getMarking(new \stdClass());
    }

    public function testViewableDataGetBadProperty() {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Field "marking" does not exist in subject "SilverStripe\View\ViewableData"');

        $markingStore = new ViewableDataMarkingStore();
        $markingStore->getMarking(new ViewableData());
    }
}