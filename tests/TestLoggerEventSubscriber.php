<?php

namespace SilverStripe\Workflow\Tests;

use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use SilverStripe\Dev\TestOnly;
use Symfony\Component\Workflow\Event\TransitionEvent;

class TestLoggerEventSubscriber implements EventSubscriberInterface, TestOnly
{
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function onTransition(TransitionEvent $event)
    {
        $this->logger->info(sprintf(
            'Blog post (id: "%s") performed transition "%s" from "%s" to "%s"',
            $event->getSubject()->ID,
            $event->getTransition()->getName(),
            implode(', ', array_keys($event->getMarking()->getPlaces())),
            implode(', ', $event->getTransition()->getTos())
        ));
    }

    public static function getSubscribedEvents()
    {
        return [
            'workflow.transition' => 'onTransition',
        ];
    }
}