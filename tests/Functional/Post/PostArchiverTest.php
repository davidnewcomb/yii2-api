<?php

declare(strict_types=1);

namespace Podium\Tests\Functional\Post;

use Podium\Api\Events\ArchiveEvent;
use Podium\Api\Interfaces\PostRepositoryInterface;
use Podium\Api\Services\Post\PostArchiver;
use Podium\Tests\AppTestCase;
use yii\base\Event;

class PostArchiverTest extends AppTestCase
{
    private PostArchiver $service;

    private array $eventsRaised;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new PostArchiver();
        $this->eventsRaised = [];
    }

    public function testArchiveShouldTriggerBeforeAndAfterEventsWhenArchivingIsDone(): void
    {
        $beforeHandler = function ($event) {
            $this->eventsRaised[PostArchiver::EVENT_BEFORE_ARCHIVING] = $event instanceof ArchiveEvent;
        };
        Event::on(PostArchiver::class, PostArchiver::EVENT_BEFORE_ARCHIVING, $beforeHandler);
        $afterHandler = function ($event) {
            $this->eventsRaised[PostArchiver::EVENT_AFTER_ARCHIVING] = $event instanceof ArchiveEvent
                && 99 === $event->repository->getId();
        };
        Event::on(PostArchiver::class, PostArchiver::EVENT_AFTER_ARCHIVING, $afterHandler);

        $post = $this->createMock(PostRepositoryInterface::class);
        $post->method('isArchived')->willReturn(false);
        $post->method('archive')->willReturn(true);
        $post->method('getId')->willReturn(99);
        $this->service->archive($post);

        self::assertTrue($this->eventsRaised[PostArchiver::EVENT_BEFORE_ARCHIVING]);
        self::assertTrue($this->eventsRaised[PostArchiver::EVENT_AFTER_ARCHIVING]);

        Event::off(PostArchiver::class, PostArchiver::EVENT_BEFORE_ARCHIVING, $beforeHandler);
        Event::off(PostArchiver::class, PostArchiver::EVENT_AFTER_ARCHIVING, $afterHandler);
    }

    public function testArchiveShouldOnlyTriggerBeforeEventWhenArchivingErrored(): void
    {
        $beforeHandler = function () {
            $this->eventsRaised[PostArchiver::EVENT_BEFORE_ARCHIVING] = true;
        };
        Event::on(PostArchiver::class, PostArchiver::EVENT_BEFORE_ARCHIVING, $beforeHandler);
        $afterHandler = function () {
            $this->eventsRaised[PostArchiver::EVENT_AFTER_ARCHIVING] = true;
        };
        Event::on(PostArchiver::class, PostArchiver::EVENT_AFTER_ARCHIVING, $afterHandler);

        $post = $this->createMock(PostRepositoryInterface::class);
        $post->method('isArchived')->willReturn(false);
        $post->method('archive')->willReturn(false);
        $this->service->archive($post);

        self::assertTrue($this->eventsRaised[PostArchiver::EVENT_BEFORE_ARCHIVING]);
        self::assertArrayNotHasKey(PostArchiver::EVENT_AFTER_ARCHIVING, $this->eventsRaised);

        Event::off(PostArchiver::class, PostArchiver::EVENT_BEFORE_ARCHIVING, $beforeHandler);
        Event::off(PostArchiver::class, PostArchiver::EVENT_AFTER_ARCHIVING, $afterHandler);
    }

    public function testArchiveShouldOnlyTriggerBeforeEventWhenPostIsAlreadyArchived(): void
    {
        $beforeHandler = function () {
            $this->eventsRaised[PostArchiver::EVENT_BEFORE_ARCHIVING] = true;
        };
        Event::on(PostArchiver::class, PostArchiver::EVENT_BEFORE_ARCHIVING, $beforeHandler);
        $afterHandler = function () {
            $this->eventsRaised[PostArchiver::EVENT_AFTER_ARCHIVING] = true;
        };
        Event::on(PostArchiver::class, PostArchiver::EVENT_AFTER_ARCHIVING, $afterHandler);

        $post = $this->createMock(PostRepositoryInterface::class);
        $post->method('isArchived')->willReturn(true);
        $this->service->archive($post);

        self::assertTrue($this->eventsRaised[PostArchiver::EVENT_BEFORE_ARCHIVING]);
        self::assertArrayNotHasKey(PostArchiver::EVENT_AFTER_ARCHIVING, $this->eventsRaised);

        Event::off(PostArchiver::class, PostArchiver::EVENT_BEFORE_ARCHIVING, $beforeHandler);
        Event::off(PostArchiver::class, PostArchiver::EVENT_AFTER_ARCHIVING, $afterHandler);
    }

    public function testArchiveShouldReturnErrorWhenEventPreventsArchiving(): void
    {
        $handler = static function (ArchiveEvent $event) {
            $event->canArchive = false;
        };
        Event::on(PostArchiver::class, PostArchiver::EVENT_BEFORE_ARCHIVING, $handler);

        $result = $this->service->archive($this->createMock(PostRepositoryInterface::class));
        self::assertFalse($result->getResult());
        self::assertEmpty($result->getErrors());

        Event::off(PostArchiver::class, PostArchiver::EVENT_BEFORE_ARCHIVING, $handler);
    }

    public function testReviveShouldTriggerBeforeAndAfterEventsWhenRevivingIsDone(): void
    {
        $beforeHandler = function ($event) {
            $this->eventsRaised[PostArchiver::EVENT_BEFORE_REVIVING] = $event instanceof ArchiveEvent;
        };
        Event::on(PostArchiver::class, PostArchiver::EVENT_BEFORE_REVIVING, $beforeHandler);
        $afterHandler = function ($event) {
            $this->eventsRaised[PostArchiver::EVENT_AFTER_REVIVING] = $event instanceof ArchiveEvent
                && 101 === $event->repository->getId();
        };
        Event::on(PostArchiver::class, PostArchiver::EVENT_AFTER_REVIVING, $afterHandler);

        $post = $this->createMock(PostRepositoryInterface::class);
        $post->method('isArchived')->willReturn(true);
        $post->method('revive')->willReturn(true);
        $post->method('getId')->willReturn(101);
        $this->service->revive($post);

        self::assertTrue($this->eventsRaised[PostArchiver::EVENT_BEFORE_REVIVING]);
        self::assertTrue($this->eventsRaised[PostArchiver::EVENT_AFTER_REVIVING]);

        Event::off(PostArchiver::class, PostArchiver::EVENT_BEFORE_REVIVING, $beforeHandler);
        Event::off(PostArchiver::class, PostArchiver::EVENT_AFTER_REVIVING, $afterHandler);
    }

    public function testReviveShouldOnlyTriggerBeforeEventWhenRevivingErrored(): void
    {
        $beforeHandler = function () {
            $this->eventsRaised[PostArchiver::EVENT_BEFORE_REVIVING] = true;
        };
        Event::on(PostArchiver::class, PostArchiver::EVENT_BEFORE_REVIVING, $beforeHandler);
        $afterHandler = function () {
            $this->eventsRaised[PostArchiver::EVENT_AFTER_REVIVING] = true;
        };
        Event::on(PostArchiver::class, PostArchiver::EVENT_AFTER_REVIVING, $afterHandler);

        $post = $this->createMock(PostRepositoryInterface::class);
        $post->method('isArchived')->willReturn(true);
        $post->method('revive')->willReturn(false);
        $this->service->revive($post);

        self::assertTrue($this->eventsRaised[PostArchiver::EVENT_BEFORE_REVIVING]);
        self::assertArrayNotHasKey(PostArchiver::EVENT_AFTER_REVIVING, $this->eventsRaised);

        Event::off(PostArchiver::class, PostArchiver::EVENT_BEFORE_REVIVING, $beforeHandler);
        Event::off(PostArchiver::class, PostArchiver::EVENT_AFTER_REVIVING, $afterHandler);
    }

    public function testReviveShouldOnlyTriggerBeforeEventWhenPostIsNotArchived(): void
    {
        $beforeHandler = function () {
            $this->eventsRaised[PostArchiver::EVENT_BEFORE_REVIVING] = true;
        };
        Event::on(PostArchiver::class, PostArchiver::EVENT_BEFORE_REVIVING, $beforeHandler);
        $afterHandler = function () {
            $this->eventsRaised[PostArchiver::EVENT_AFTER_REVIVING] = true;
        };
        Event::on(PostArchiver::class, PostArchiver::EVENT_AFTER_REVIVING, $afterHandler);

        $post = $this->createMock(PostRepositoryInterface::class);
        $post->method('isArchived')->willReturn(false);
        $this->service->revive($post);

        self::assertTrue($this->eventsRaised[PostArchiver::EVENT_BEFORE_REVIVING]);
        self::assertArrayNotHasKey(PostArchiver::EVENT_AFTER_REVIVING, $this->eventsRaised);

        Event::off(PostArchiver::class, PostArchiver::EVENT_BEFORE_REVIVING, $beforeHandler);
        Event::off(PostArchiver::class, PostArchiver::EVENT_AFTER_REVIVING, $afterHandler);
    }

    public function testReviveShouldReturnErrorWhenEventPreventsReviving(): void
    {
        $handler = static function (ArchiveEvent $event) {
            $event->canRevive = false;
        };
        Event::on(PostArchiver::class, PostArchiver::EVENT_BEFORE_REVIVING, $handler);

        $result = $this->service->revive($this->createMock(PostRepositoryInterface::class));
        self::assertFalse($result->getResult());
        self::assertEmpty($result->getErrors());

        Event::off(PostArchiver::class, PostArchiver::EVENT_BEFORE_REVIVING, $handler);
    }
}
