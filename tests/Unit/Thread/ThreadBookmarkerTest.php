<?php

declare(strict_types=1);

namespace Podium\Tests\Unit\Thread;

use Exception;
use Podium\Api\Interfaces\BookmarkRepositoryInterface;
use Podium\Api\Interfaces\MemberRepositoryInterface;
use Podium\Api\Interfaces\PostRepositoryInterface;
use Podium\Api\Interfaces\ThreadRepositoryInterface;
use Podium\Api\Services\Thread\ThreadBookmarker;
use Podium\Tests\AppTestCase;

class ThreadBookmarkerTest extends AppTestCase
{
    private ThreadBookmarker $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ThreadBookmarker();
    }

    public function testBeforeMarkShouldReturnTrue(): void
    {
        self::assertTrue($this->service->beforeMark());
    }

    public function testMarkShouldReturnTrueIfMarkingIsDone(): void
    {
        $bookmark = $this->createMock(BookmarkRepositoryInterface::class);
        $bookmark->method('fetchOne')->willReturn(true);
        $bookmark->expects(self::never())->method('prepare');
        $bookmark->method('getLastSeen')->willReturn(1);
        $bookmark->method('mark')->willReturn(true);

        $post = $this->createMock(PostRepositoryInterface::class);
        $post->method('getParent')->willReturn($this->createMock(ThreadRepositoryInterface::class));
        $post->method('getCreatedAt')->willReturn(2);

        $result = $this->service->mark($bookmark, $post, $this->createMock(MemberRepositoryInterface::class));

        self::assertTrue($result->getResult());
    }

    public function testMarkShouldReturnTrueIfBookmarkIsSeenAfterPostCreation(): void
    {
        $bookmark = $this->createMock(BookmarkRepositoryInterface::class);
        $bookmark->method('fetchOne')->willReturn(true);
        $bookmark->expects(self::never())->method('prepare');
        $bookmark->method('getLastSeen')->willReturn(2);
        $bookmark->expects(self::never())->method('mark');

        $post = $this->createMock(PostRepositoryInterface::class);
        $post->method('getParent')->willReturn($this->createMock(ThreadRepositoryInterface::class));
        $post->method('getCreatedAt')->willReturn(1);

        $result = $this->service->mark($bookmark, $post, $this->createMock(MemberRepositoryInterface::class));

        self::assertTrue($result->getResult());
    }

    public function testMarkShouldPrepareBookmarkWhenItDoesntExist(): void
    {
        $bookmark = $this->createMock(BookmarkRepositoryInterface::class);
        $bookmark->method('fetchOne')->willReturn(false);
        $bookmark->expects(self::once())->method('prepare');
        $bookmark->method('getLastSeen')->willReturn(1);
        $bookmark->method('mark')->willReturn(true);

        $post = $this->createMock(PostRepositoryInterface::class);
        $post->method('getParent')->willReturn($this->createMock(ThreadRepositoryInterface::class));
        $post->method('getCreatedAt')->willReturn(2);

        $result = $this->service->mark($bookmark, $post, $this->createMock(MemberRepositoryInterface::class));

        self::assertTrue($result->getResult());
    }

    public function testMarkShouldReturnErrorWhenMarkingThrowsException(): void
    {
        $bookmark = $this->createMock(BookmarkRepositoryInterface::class);
        $bookmark->method('fetchOne')->willReturn(true);
        $bookmark->expects(self::never())->method('prepare');
        $bookmark->method('getLastSeen')->willReturn(1);
        $bookmark->method('mark')->willThrowException(new Exception('exc'));

        $post = $this->createMock(PostRepositoryInterface::class);
        $post->method('getParent')->willReturn($this->createMock(ThreadRepositoryInterface::class));
        $post->method('getCreatedAt')->willReturn(2);

        $result = $this->service->mark($bookmark, $post, $this->createMock(MemberRepositoryInterface::class));

        self::assertFalse($result->getResult());
        self::assertSame('exc', $result->getErrors()['exception']->getMessage());
    }
}
