<?php

declare(strict_types=1);

namespace Podium\Tests\Unit\Group;

use Exception;
use PHPUnit\Framework\TestCase;
use Podium\Api\Interfaces\GroupRepositoryInterface;
use Podium\Api\Interfaces\RepositoryInterface;
use Podium\Api\Services\Group\GroupBuilder;

class GroupBuilderTest extends TestCase
{
    private GroupBuilder $service;

    protected function setUp(): void
    {
        $this->service = new GroupBuilder();
    }

    public function testBeforeCreateShouldReturnTrue(): void
    {
        self::assertTrue($this->service->beforeCreate());
    }

    public function testCreateShouldReturnErrorWhenCreatingErrored(): void
    {
        $group = $this->createMock(GroupRepositoryInterface::class);
        $group->method('create')->willReturn(false);
        $group->method('getErrors')->willReturn([1]);
        $result = $this->service->create($group);

        self::assertFalse($result->getResult());
        self::assertSame([1], $result->getErrors());
    }

    public function testCreateShouldReturnErrorWhenRepositoryIsWrong(): void
    {
        $result = $this->service->create($this->createMock(RepositoryInterface::class));

        self::assertFalse($result->getResult());
        self::assertEmpty($result->getErrors());
    }

    public function testCreateShouldReturnSuccessWhenCreatingIsDone(): void
    {
        $group = $this->createMock(GroupRepositoryInterface::class);
        $group->method('create')->willReturn(true);
        $result = $this->service->create($group);

        self::assertTrue($result->getResult());
    }

    public function testCreateShouldReturnErrorWhenCreatingThrowsException(): void
    {
        $group = $this->createMock(GroupRepositoryInterface::class);
        $group->method('create')->willThrowException(new Exception('exc'));
        $result = $this->service->create($group);

        self::assertFalse($result->getResult());
        self::assertSame('exc', $result->getErrors()['exception']->getMessage());
    }

    public function testBeforeEditShouldReturnTrue(): void
    {
        self::assertTrue($this->service->beforeEdit());
    }

    public function testEditShouldReturnErrorWhenEditingErrored(): void
    {
        $group = $this->createMock(GroupRepositoryInterface::class);
        $group->method('edit')->willReturn(false);
        $group->method('getErrors')->willReturn([1]);
        $result = $this->service->edit($group);

        self::assertFalse($result->getResult());
        self::assertSame([1], $result->getErrors());
    }

    public function testEditShouldReturnErrorWhenRepositoryIsWrong(): void
    {
        $result = $this->service->edit($this->createMock(RepositoryInterface::class));

        self::assertFalse($result->getResult());
        self::assertEmpty($result->getErrors());
    }

    public function testEditShouldReturnSuccessWhenEditingIsDone(): void
    {
        $group = $this->createMock(GroupRepositoryInterface::class);
        $group->method('edit')->willReturn(true);
        $result = $this->service->edit($group);

        self::assertTrue($result->getResult());
    }

    public function testEditShouldReturnErrorWhenEditingThrowsException(): void
    {
        $rank = $this->createMock(GroupRepositoryInterface::class);
        $rank->method('edit')->willThrowException(new Exception('exc'));
        $result = $this->service->edit($rank);

        self::assertFalse($result->getResult());
        self::assertSame('exc', $result->getErrors()['exception']->getMessage());
    }
}