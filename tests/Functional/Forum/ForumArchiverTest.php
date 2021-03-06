<?php

declare(strict_types=1);

namespace Podium\Tests\Functional\Forum;

use Podium\Api\Events\ArchiveEvent;
use Podium\Api\Interfaces\ForumRepositoryInterface;
use Podium\Api\Services\Forum\ForumArchiver;
use Podium\Tests\AppTestCase;
use yii\base\Event;

class ForumArchiverTest extends AppTestCase
{
    private ForumArchiver $service;

    private array $eventsRaised;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ForumArchiver();
        $this->eventsRaised = [];
    }

    public function testArchiveShouldTriggerBeforeAndAfterEventsWhenArchivingIsDone(): void
    {
        $beforeHandler = function ($event) {
            $this->eventsRaised[ForumArchiver::EVENT_BEFORE_ARCHIVING] = $event instanceof ArchiveEvent;
        };
        Event::on(ForumArchiver::class, ForumArchiver::EVENT_BEFORE_ARCHIVING, $beforeHandler);
        $afterHandler = function ($event) {
            $this->eventsRaised[ForumArchiver::EVENT_AFTER_ARCHIVING] = $event instanceof ArchiveEvent
                && 99 === $event->repository->getId();
        };
        Event::on(ForumArchiver::class, ForumArchiver::EVENT_AFTER_ARCHIVING, $afterHandler);

        $forum = $this->createMock(ForumRepositoryInterface::class);
        $forum->method('isArchived')->willReturn(false);
        $forum->method('archive')->willReturn(true);
        $forum->method('getId')->willReturn(99);
        $this->service->archive($forum);

        self::assertTrue($this->eventsRaised[ForumArchiver::EVENT_BEFORE_ARCHIVING]);
        self::assertTrue($this->eventsRaised[ForumArchiver::EVENT_AFTER_ARCHIVING]);

        Event::off(ForumArchiver::class, ForumArchiver::EVENT_BEFORE_ARCHIVING, $beforeHandler);
        Event::off(ForumArchiver::class, ForumArchiver::EVENT_AFTER_ARCHIVING, $afterHandler);
    }

    public function testArchiveShouldOnlyTriggerBeforeEventWhenArchivingErrored(): void
    {
        $beforeHandler = function () {
            $this->eventsRaised[ForumArchiver::EVENT_BEFORE_ARCHIVING] = true;
        };
        Event::on(ForumArchiver::class, ForumArchiver::EVENT_BEFORE_ARCHIVING, $beforeHandler);
        $afterHandler = function () {
            $this->eventsRaised[ForumArchiver::EVENT_AFTER_ARCHIVING] = true;
        };
        Event::on(ForumArchiver::class, ForumArchiver::EVENT_AFTER_ARCHIVING, $afterHandler);

        $forum = $this->createMock(ForumRepositoryInterface::class);
        $forum->method('isArchived')->willReturn(false);
        $forum->method('archive')->willReturn(false);
        $this->service->archive($forum);

        self::assertTrue($this->eventsRaised[ForumArchiver::EVENT_BEFORE_ARCHIVING]);
        self::assertArrayNotHasKey(ForumArchiver::EVENT_AFTER_ARCHIVING, $this->eventsRaised);

        Event::off(ForumArchiver::class, ForumArchiver::EVENT_BEFORE_ARCHIVING, $beforeHandler);
        Event::off(ForumArchiver::class, ForumArchiver::EVENT_AFTER_ARCHIVING, $afterHandler);
    }

    public function testArchiveShouldOnlyTriggerBeforeEventWhenForumIsAlreadyArchived(): void
    {
        $beforeHandler = function () {
            $this->eventsRaised[ForumArchiver::EVENT_BEFORE_ARCHIVING] = true;
        };
        Event::on(ForumArchiver::class, ForumArchiver::EVENT_BEFORE_ARCHIVING, $beforeHandler);
        $afterHandler = function () {
            $this->eventsRaised[ForumArchiver::EVENT_AFTER_ARCHIVING] = true;
        };
        Event::on(ForumArchiver::class, ForumArchiver::EVENT_AFTER_ARCHIVING, $afterHandler);

        $forum = $this->createMock(ForumRepositoryInterface::class);
        $forum->method('isArchived')->willReturn(true);
        $this->service->archive($forum);

        self::assertTrue($this->eventsRaised[ForumArchiver::EVENT_BEFORE_ARCHIVING]);
        self::assertArrayNotHasKey(ForumArchiver::EVENT_AFTER_ARCHIVING, $this->eventsRaised);

        Event::off(ForumArchiver::class, ForumArchiver::EVENT_BEFORE_ARCHIVING, $beforeHandler);
        Event::off(ForumArchiver::class, ForumArchiver::EVENT_AFTER_ARCHIVING, $afterHandler);
    }

    public function testArchiveShouldReturnErrorWhenEventPreventsArchiving(): void
    {
        $handler = static function (ArchiveEvent $event) {
            $event->canArchive = false;
        };
        Event::on(ForumArchiver::class, ForumArchiver::EVENT_BEFORE_ARCHIVING, $handler);

        $result = $this->service->archive($this->createMock(ForumRepositoryInterface::class));
        self::assertFalse($result->getResult());
        self::assertEmpty($result->getErrors());

        Event::off(ForumArchiver::class, ForumArchiver::EVENT_BEFORE_ARCHIVING, $handler);
    }

    public function testReviveShouldTriggerBeforeAndAfterEventsWhenRevivingIsDone(): void
    {
        $beforeHandler = function ($event) {
            $this->eventsRaised[ForumArchiver::EVENT_BEFORE_REVIVING] = $event instanceof ArchiveEvent;
        };
        Event::on(ForumArchiver::class, ForumArchiver::EVENT_BEFORE_REVIVING, $beforeHandler);
        $afterHandler = function ($event) {
            $this->eventsRaised[ForumArchiver::EVENT_AFTER_REVIVING] = $event instanceof ArchiveEvent
                && 101 === $event->repository->getId();
        };
        Event::on(ForumArchiver::class, ForumArchiver::EVENT_AFTER_REVIVING, $afterHandler);

        $forum = $this->createMock(ForumRepositoryInterface::class);
        $forum->method('isArchived')->willReturn(true);
        $forum->method('revive')->willReturn(true);
        $forum->method('getId')->willReturn(101);
        $this->service->revive($forum);

        self::assertTrue($this->eventsRaised[ForumArchiver::EVENT_BEFORE_REVIVING]);
        self::assertTrue($this->eventsRaised[ForumArchiver::EVENT_AFTER_REVIVING]);

        Event::off(ForumArchiver::class, ForumArchiver::EVENT_BEFORE_REVIVING, $beforeHandler);
        Event::off(ForumArchiver::class, ForumArchiver::EVENT_AFTER_REVIVING, $afterHandler);
    }

    public function testReviveShouldOnlyTriggerBeforeEventWhenRevivingErrored(): void
    {
        $beforeHandler = function () {
            $this->eventsRaised[ForumArchiver::EVENT_BEFORE_REVIVING] = true;
        };
        Event::on(ForumArchiver::class, ForumArchiver::EVENT_BEFORE_REVIVING, $beforeHandler);
        $afterHandler = function () {
            $this->eventsRaised[ForumArchiver::EVENT_AFTER_REVIVING] = true;
        };
        Event::on(ForumArchiver::class, ForumArchiver::EVENT_AFTER_REVIVING, $afterHandler);

        $forum = $this->createMock(ForumRepositoryInterface::class);
        $forum->method('isArchived')->willReturn(true);
        $forum->method('revive')->willReturn(false);
        $this->service->revive($forum);

        self::assertTrue($this->eventsRaised[ForumArchiver::EVENT_BEFORE_REVIVING]);
        self::assertArrayNotHasKey(ForumArchiver::EVENT_AFTER_REVIVING, $this->eventsRaised);

        Event::off(ForumArchiver::class, ForumArchiver::EVENT_BEFORE_REVIVING, $beforeHandler);
        Event::off(ForumArchiver::class, ForumArchiver::EVENT_AFTER_REVIVING, $afterHandler);
    }

    public function testReviveShouldOnlyTriggerBeforeEventWhenForumIsNotArchived(): void
    {
        $beforeHandler = function () {
            $this->eventsRaised[ForumArchiver::EVENT_BEFORE_REVIVING] = true;
        };
        Event::on(ForumArchiver::class, ForumArchiver::EVENT_BEFORE_REVIVING, $beforeHandler);
        $afterHandler = function () {
            $this->eventsRaised[ForumArchiver::EVENT_AFTER_REVIVING] = true;
        };
        Event::on(ForumArchiver::class, ForumArchiver::EVENT_AFTER_REVIVING, $afterHandler);

        $forum = $this->createMock(ForumRepositoryInterface::class);
        $forum->method('isArchived')->willReturn(false);
        $this->service->revive($forum);

        self::assertTrue($this->eventsRaised[ForumArchiver::EVENT_BEFORE_REVIVING]);
        self::assertArrayNotHasKey(ForumArchiver::EVENT_AFTER_REVIVING, $this->eventsRaised);

        Event::off(ForumArchiver::class, ForumArchiver::EVENT_BEFORE_REVIVING, $beforeHandler);
        Event::off(ForumArchiver::class, ForumArchiver::EVENT_AFTER_REVIVING, $afterHandler);
    }

    public function testReviveShouldReturnErrorWhenEventPreventsReviving(): void
    {
        $handler = static function (ArchiveEvent $event) {
            $event->canRevive = false;
        };
        Event::on(ForumArchiver::class, ForumArchiver::EVENT_BEFORE_REVIVING, $handler);

        $result = $this->service->revive($this->createMock(ForumRepositoryInterface::class));
        self::assertFalse($result->getResult());
        self::assertEmpty($result->getErrors());

        Event::off(ForumArchiver::class, ForumArchiver::EVENT_BEFORE_REVIVING, $handler);
    }
}
