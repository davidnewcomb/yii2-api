<?php

declare(strict_types=1);

namespace Podium\Tests\Unit\Group;

use Exception;
use Podium\Api\Interfaces\GroupRepositoryInterface;
use Podium\Api\Interfaces\RepositoryInterface;
use Podium\Api\Services\Group\GroupRemover;
use Podium\Tests\AppTestCase;

class GroupRemoverTest extends AppTestCase
{
    private GroupRemover $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new GroupRemover();
    }

    public function testRemoveShouldReturnErrorWhenRepositoryIsWrong(): void
    {
        $result = $this->service->remove($this->createMock(RepositoryInterface::class));

        self::assertFalse($result->getResult());
        self::assertSame(
            'Group must be instance of Podium\Api\Interfaces\GroupRepositoryInterface!',
            $result->getErrors()['exception']->getMessage()
        );
    }

    public function testRemoveShouldReturnErrorWhenRemovingErrored(): void
    {
        $this->transaction->expects(self::once())->method('rollBack');

        $group = $this->createMock(GroupRepositoryInterface::class);
        $group->method('delete')->willReturn(false);
        $result = $this->service->remove($group);

        self::assertFalse($result->getResult());
        self::assertEmpty($result->getErrors());
    }

    public function testRemoveShouldReturnSuccessWhenRemovingIsDone(): void
    {
        $this->transaction->expects(self::once())->method('commit');

        $group = $this->createMock(GroupRepositoryInterface::class);
        $group->method('delete')->willReturn(true);
        $result = $this->service->remove($group);

        self::assertTrue($result->getResult());
    }

    public function testRemoveShouldReturnErrorWhenRemovingThrowsException(): void
    {
        $this->transaction->expects(self::once())->method('rollBack');
        $this->logger->expects(self::once())->method('log')->with(
            self::callback(
                static function (array $data) {
                    return 3 === count($data) && 'Exception while deleting group' === $data[0] && 'exc' === $data[1];
                }
            ),
            1,
            'podium'
        );

        $group = $this->createMock(GroupRepositoryInterface::class);
        $group->method('delete')->willThrowException(new Exception('exc'));
        $result = $this->service->remove($group);

        self::assertFalse($result->getResult());
        self::assertSame('exc', $result->getErrors()['exception']->getMessage());
    }
}
