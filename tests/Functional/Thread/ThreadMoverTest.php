<?php

declare(strict_types=1);

namespace Podium\Tests\Functional\Thread;

use Podium\Api\Events\MoveEvent;
use Podium\Api\Interfaces\ForumRepositoryInterface;
use Podium\Api\Interfaces\ThreadRepositoryInterface;
use Podium\Api\Services\Thread\ThreadMover;
use Podium\Tests\AppTestCase;
use yii\base\Event;

class ThreadMoverTest extends AppTestCase
{
    private ThreadMover $service;

    private array $eventsRaised;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ThreadMover();
        $this->eventsRaised = [];
    }

    public function testMoveShouldTriggerBeforeAndAfterEventsWhenMovingIsDone(): void
    {
        $beforeHandler = function ($event) {
            $this->eventsRaised[ThreadMover::EVENT_BEFORE_MOVING] = $event instanceof MoveEvent;
        };
        Event::on(ThreadMover::class, ThreadMover::EVENT_BEFORE_MOVING, $beforeHandler);
        $afterHandler = function ($event) {
            $this->eventsRaised[ThreadMover::EVENT_AFTER_MOVING] = $event instanceof MoveEvent
                && 54 === $event->repository->getId();
        };
        Event::on(ThreadMover::class, ThreadMover::EVENT_AFTER_MOVING, $afterHandler);

        $thread = $this->createMock(ThreadRepositoryInterface::class);
        $thread->method('move')->willReturn(true);
        $thread->method('getId')->willReturn(54);
        $forum = $this->createMock(ForumRepositoryInterface::class);
        $forum->method('updateCounters')->willReturn(true);
        $thread->method('getParent')->willReturn($forum);
        $this->service->move($thread, $forum);

        self::assertTrue($this->eventsRaised[ThreadMover::EVENT_BEFORE_MOVING]);
        self::assertTrue($this->eventsRaised[ThreadMover::EVENT_AFTER_MOVING]);

        Event::off(ThreadMover::class, ThreadMover::EVENT_BEFORE_MOVING, $beforeHandler);
        Event::off(ThreadMover::class, ThreadMover::EVENT_AFTER_MOVING, $afterHandler);
    }

    public function testMoveShouldOnlyTriggerBeforeEventWhenMovingErrored(): void
    {
        $beforeHandler = function () {
            $this->eventsRaised[ThreadMover::EVENT_BEFORE_MOVING] = true;
        };
        Event::on(ThreadMover::class, ThreadMover::EVENT_BEFORE_MOVING, $beforeHandler);
        $afterHandler = function () {
            $this->eventsRaised[ThreadMover::EVENT_AFTER_MOVING] = true;
        };
        Event::on(ThreadMover::class, ThreadMover::EVENT_AFTER_MOVING, $afterHandler);

        $thread = $this->createMock(ThreadRepositoryInterface::class);
        $thread->method('move')->willReturn(false);
        $this->service->move($thread, $this->createMock(ForumRepositoryInterface::class));

        self::assertTrue($this->eventsRaised[ThreadMover::EVENT_BEFORE_MOVING]);
        self::assertArrayNotHasKey(ThreadMover::EVENT_AFTER_MOVING, $this->eventsRaised);

        Event::off(ThreadMover::class, ThreadMover::EVENT_BEFORE_MOVING, $beforeHandler);
        Event::off(ThreadMover::class, ThreadMover::EVENT_AFTER_MOVING, $afterHandler);
    }

    public function testMoveShouldReturnErrorWhenEventPreventsMoving(): void
    {
        $handler = static function (MoveEvent $event) {
            $event->canMove = false;
        };
        Event::on(ThreadMover::class, ThreadMover::EVENT_BEFORE_MOVING, $handler);

        $result = $this->service->move(
            $this->createMock(ThreadRepositoryInterface::class),
            $this->createMock(ForumRepositoryInterface::class)
        );
        self::assertFalse($result->getResult());
        self::assertEmpty($result->getErrors());

        Event::off(ThreadMover::class, ThreadMover::EVENT_BEFORE_MOVING, $handler);
    }
}
