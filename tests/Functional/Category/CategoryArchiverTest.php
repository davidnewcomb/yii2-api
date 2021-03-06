<?php

declare(strict_types=1);

namespace Podium\Tests\Functional\Category;

use Podium\Api\Events\ArchiveEvent;
use Podium\Api\Interfaces\CategoryRepositoryInterface;
use Podium\Api\Services\Category\CategoryArchiver;
use Podium\Tests\AppTestCase;
use yii\base\Event;

class CategoryArchiverTest extends AppTestCase
{
    private CategoryArchiver $service;

    private array $eventsRaised;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new CategoryArchiver();
        $this->eventsRaised = [];
    }

    public function testArchiveShouldTriggerBeforeAndAfterEventsWhenArchivingIsDone(): void
    {
        $beforeHandler = function ($event) {
            $this->eventsRaised[CategoryArchiver::EVENT_BEFORE_ARCHIVING] = $event instanceof ArchiveEvent;
        };
        Event::on(CategoryArchiver::class, CategoryArchiver::EVENT_BEFORE_ARCHIVING, $beforeHandler);
        $afterHandler = function ($event) {
            $this->eventsRaised[CategoryArchiver::EVENT_AFTER_ARCHIVING] = $event instanceof ArchiveEvent
                && 99 === $event->repository->getId();
        };
        Event::on(CategoryArchiver::class, CategoryArchiver::EVENT_AFTER_ARCHIVING, $afterHandler);

        $category = $this->createMock(CategoryRepositoryInterface::class);
        $category->method('isArchived')->willReturn(false);
        $category->method('archive')->willReturn(true);
        $category->method('getId')->willReturn(99);
        $this->service->archive($category);

        self::assertTrue($this->eventsRaised[CategoryArchiver::EVENT_BEFORE_ARCHIVING]);
        self::assertTrue($this->eventsRaised[CategoryArchiver::EVENT_AFTER_ARCHIVING]);

        Event::off(CategoryArchiver::class, CategoryArchiver::EVENT_BEFORE_ARCHIVING, $beforeHandler);
        Event::off(CategoryArchiver::class, CategoryArchiver::EVENT_AFTER_ARCHIVING, $afterHandler);
    }

    public function testArchiveShouldOnlyTriggerBeforeEventWhenArchivingErrored(): void
    {
        $beforeHandler = function () {
            $this->eventsRaised[CategoryArchiver::EVENT_BEFORE_ARCHIVING] = true;
        };
        Event::on(CategoryArchiver::class, CategoryArchiver::EVENT_BEFORE_ARCHIVING, $beforeHandler);
        $afterHandler = function () {
            $this->eventsRaised[CategoryArchiver::EVENT_AFTER_ARCHIVING] = true;
        };
        Event::on(CategoryArchiver::class, CategoryArchiver::EVENT_AFTER_ARCHIVING, $afterHandler);

        $category = $this->createMock(CategoryRepositoryInterface::class);
        $category->method('isArchived')->willReturn(false);
        $category->method('archive')->willReturn(false);
        $this->service->archive($category);

        self::assertTrue($this->eventsRaised[CategoryArchiver::EVENT_BEFORE_ARCHIVING]);
        self::assertArrayNotHasKey(CategoryArchiver::EVENT_AFTER_ARCHIVING, $this->eventsRaised);

        Event::off(CategoryArchiver::class, CategoryArchiver::EVENT_BEFORE_ARCHIVING, $beforeHandler);
        Event::off(CategoryArchiver::class, CategoryArchiver::EVENT_AFTER_ARCHIVING, $afterHandler);
    }

    public function testArchiveShouldOnlyTriggerBeforeEventWhenCategoryIsAlreadyArchived(): void
    {
        $beforeHandler = function () {
            $this->eventsRaised[CategoryArchiver::EVENT_BEFORE_ARCHIVING] = true;
        };
        Event::on(CategoryArchiver::class, CategoryArchiver::EVENT_BEFORE_ARCHIVING, $beforeHandler);
        $afterHandler = function () {
            $this->eventsRaised[CategoryArchiver::EVENT_AFTER_ARCHIVING] = true;
        };
        Event::on(CategoryArchiver::class, CategoryArchiver::EVENT_AFTER_ARCHIVING, $afterHandler);

        $category = $this->createMock(CategoryRepositoryInterface::class);
        $category->method('isArchived')->willReturn(true);
        $this->service->archive($category);

        self::assertTrue($this->eventsRaised[CategoryArchiver::EVENT_BEFORE_ARCHIVING]);
        self::assertArrayNotHasKey(CategoryArchiver::EVENT_AFTER_ARCHIVING, $this->eventsRaised);

        Event::off(CategoryArchiver::class, CategoryArchiver::EVENT_BEFORE_ARCHIVING, $beforeHandler);
        Event::off(CategoryArchiver::class, CategoryArchiver::EVENT_AFTER_ARCHIVING, $afterHandler);
    }

    public function testArchiveShouldReturnErrorWhenEventPreventsArchiving(): void
    {
        $handler = static function (ArchiveEvent $event) {
            $event->canArchive = false;
        };
        Event::on(CategoryArchiver::class, CategoryArchiver::EVENT_BEFORE_ARCHIVING, $handler);

        $result = $this->service->archive($this->createMock(CategoryRepositoryInterface::class));
        self::assertFalse($result->getResult());
        self::assertEmpty($result->getErrors());

        Event::off(CategoryArchiver::class, CategoryArchiver::EVENT_BEFORE_ARCHIVING, $handler);
    }

    public function testReviveShouldTriggerBeforeAndAfterEventsWhenRevivingIsDone(): void
    {
        $beforeHandler = function ($event) {
            $this->eventsRaised[CategoryArchiver::EVENT_BEFORE_REVIVING] = $event instanceof ArchiveEvent;
        };
        Event::on(CategoryArchiver::class, CategoryArchiver::EVENT_BEFORE_REVIVING, $beforeHandler);
        $afterHandler = function ($event) {
            $this->eventsRaised[CategoryArchiver::EVENT_AFTER_REVIVING] = $event instanceof ArchiveEvent
                && 101 === $event->repository->getId();
        };
        Event::on(CategoryArchiver::class, CategoryArchiver::EVENT_AFTER_REVIVING, $afterHandler);

        $category = $this->createMock(CategoryRepositoryInterface::class);
        $category->method('isArchived')->willReturn(true);
        $category->method('revive')->willReturn(true);
        $category->method('getId')->willReturn(101);
        $this->service->revive($category);

        self::assertTrue($this->eventsRaised[CategoryArchiver::EVENT_BEFORE_REVIVING]);
        self::assertTrue($this->eventsRaised[CategoryArchiver::EVENT_AFTER_REVIVING]);

        Event::off(CategoryArchiver::class, CategoryArchiver::EVENT_BEFORE_REVIVING, $beforeHandler);
        Event::off(CategoryArchiver::class, CategoryArchiver::EVENT_AFTER_REVIVING, $afterHandler);
    }

    public function testReviveShouldOnlyTriggerBeforeEventWhenRevivingErrored(): void
    {
        $beforeHandler = function () {
            $this->eventsRaised[CategoryArchiver::EVENT_BEFORE_REVIVING] = true;
        };
        Event::on(CategoryArchiver::class, CategoryArchiver::EVENT_BEFORE_REVIVING, $beforeHandler);
        $afterHandler = function () {
            $this->eventsRaised[CategoryArchiver::EVENT_AFTER_REVIVING] = true;
        };
        Event::on(CategoryArchiver::class, CategoryArchiver::EVENT_AFTER_REVIVING, $afterHandler);

        $category = $this->createMock(CategoryRepositoryInterface::class);
        $category->method('isArchived')->willReturn(true);
        $category->method('revive')->willReturn(false);
        $this->service->revive($category);

        self::assertTrue($this->eventsRaised[CategoryArchiver::EVENT_BEFORE_REVIVING]);
        self::assertArrayNotHasKey(CategoryArchiver::EVENT_AFTER_REVIVING, $this->eventsRaised);

        Event::off(CategoryArchiver::class, CategoryArchiver::EVENT_BEFORE_REVIVING, $beforeHandler);
        Event::off(CategoryArchiver::class, CategoryArchiver::EVENT_AFTER_REVIVING, $afterHandler);
    }

    public function testReviveShouldOnlyTriggerBeforeEventWhenCategoryIsNotArchived(): void
    {
        $beforeHandler = function () {
            $this->eventsRaised[CategoryArchiver::EVENT_BEFORE_REVIVING] = true;
        };
        Event::on(CategoryArchiver::class, CategoryArchiver::EVENT_BEFORE_REVIVING, $beforeHandler);
        $afterHandler = function () {
            $this->eventsRaised[CategoryArchiver::EVENT_AFTER_REVIVING] = true;
        };
        Event::on(CategoryArchiver::class, CategoryArchiver::EVENT_AFTER_REVIVING, $afterHandler);

        $category = $this->createMock(CategoryRepositoryInterface::class);
        $category->method('isArchived')->willReturn(false);
        $this->service->revive($category);

        self::assertTrue($this->eventsRaised[CategoryArchiver::EVENT_BEFORE_REVIVING]);
        self::assertArrayNotHasKey(CategoryArchiver::EVENT_AFTER_REVIVING, $this->eventsRaised);

        Event::off(CategoryArchiver::class, CategoryArchiver::EVENT_BEFORE_REVIVING, $beforeHandler);
        Event::off(CategoryArchiver::class, CategoryArchiver::EVENT_AFTER_REVIVING, $afterHandler);
    }

    public function testReviveShouldReturnErrorWhenEventPreventsReviving(): void
    {
        $handler = static function (ArchiveEvent $event) {
            $event->canRevive = false;
        };
        Event::on(CategoryArchiver::class, CategoryArchiver::EVENT_BEFORE_REVIVING, $handler);

        $result = $this->service->revive($this->createMock(CategoryRepositoryInterface::class));
        self::assertFalse($result->getResult());
        self::assertEmpty($result->getErrors());

        Event::off(CategoryArchiver::class, CategoryArchiver::EVENT_BEFORE_REVIVING, $handler);
    }
}
