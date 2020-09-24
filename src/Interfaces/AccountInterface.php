<?php

declare(strict_types=1);

namespace Podium\Api\Interfaces;

use Podium\Api\Components\PodiumResponse;
use Podium\Api\Module;

interface AccountInterface
{
    public function setPodium(Module $podium): void;

    public function getPodium(): Module;

    public function joinGroup(GroupRepositoryInterface $group): PodiumResponse;

    public function leaveGroup(GroupRepositoryInterface $group): PodiumResponse;

    public function createCategory(array $data = []): PodiumResponse;

    public function createForum(CategoryRepositoryInterface $category, array $data = []): PodiumResponse;

    public function createThread(ForumRepositoryInterface $forum, array $data = []): PodiumResponse;

    public function createPost(ThreadRepositoryInterface $thread, array $data = []): PodiumResponse;

    public function markPost(PostRepositoryInterface $post): PodiumResponse;

    public function subscribeThread(ThreadRepositoryInterface $thread): PodiumResponse;

    public function unsubscribeThread(ThreadRepositoryInterface $thread): PodiumResponse;

    public function thumbUpPost(PostRepositoryInterface $post): PodiumResponse;

    public function thumbDownPost(PostRepositoryInterface $post): PodiumResponse;

    public function thumbResetPost(PostRepositoryInterface $post): PodiumResponse;

    public function votePoll(PollPostRepositoryInterface $post, array $answer): PodiumResponse;

    public function edit(array $data = []): PodiumResponse;

    public function befriendMember(MemberRepositoryInterface $target): PodiumResponse;

    public function unfriendMember(MemberRepositoryInterface $target): PodiumResponse;

    public function ignoreMember(MemberRepositoryInterface $target): PodiumResponse;

    public function sendMessage(
        MemberRepositoryInterface $receiver,
        MessageRepositoryInterface $replyTo = null,
        array $data = []
    ): PodiumResponse;

    public function removeMessage(MessageRepositoryInterface $message): PodiumResponse;

    public function archiveMessage(MessageRepositoryInterface $message): PodiumResponse;

    public function reviveMessage(MessageRepositoryInterface $message): PodiumResponse;
}
