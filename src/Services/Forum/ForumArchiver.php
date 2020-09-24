<?php

declare(strict_types=1);

namespace Podium\Api\Services\Forum;

use Podium\Api\Components\PodiumResponse;
use Podium\Api\Events\ArchiveEvent;
use Podium\Api\Interfaces\ArchiverInterface;
use Podium\Api\Interfaces\ForumRepositoryInterface;
use Podium\Api\Interfaces\RepositoryInterface;
use Throwable;
use Yii;
use yii\base\Component;

final class ForumArchiver extends Component implements ArchiverInterface
{
    public const EVENT_BEFORE_ARCHIVING = 'podium.forum.archiving.before';
    public const EVENT_AFTER_ARCHIVING = 'podium.forum.archiving.after';
    public const EVENT_BEFORE_REVIVING = 'podium.forum.reviving.before';
    public const EVENT_AFTER_REVIVING = 'podium.forum.reviving.after';

    /**
     * Calls before archiving the forum.
     */
    public function beforeArchive(): bool
    {
        $event = new ArchiveEvent();
        $this->trigger(self::EVENT_BEFORE_ARCHIVING, $event);

        return $event->canArchive;
    }

    /**
     * Archives the forum.
     */
    public function archive(RepositoryInterface $forum): PodiumResponse
    {
        if (!$forum instanceof ForumRepositoryInterface || !$this->beforeArchive()) {
            return PodiumResponse::error();
        }

        try {
            if (!$forum->archive()) {
                return PodiumResponse::error($forum->getErrors());
            }

            $this->afterArchive($forum);

            return PodiumResponse::success();
        } catch (Throwable $exc) {
            Yii::error(['Exception while archiving forum', $exc->getMessage(), $exc->getTraceAsString()], 'podium');

            return PodiumResponse::error(['exception' => $exc]);
        }
    }

    /**
     * Calls after archiving the forum successfully.
     */
    public function afterArchive(ForumRepositoryInterface $forum): void
    {
        $this->trigger(self::EVENT_AFTER_ARCHIVING, new ArchiveEvent(['repository' => $forum]));
    }

    /**
     * Calls before reviving the forum.
     */
    public function beforeRevive(): bool
    {
        $event = new ArchiveEvent();
        $this->trigger(self::EVENT_BEFORE_REVIVING, $event);

        return $event->canRevive;
    }

    /**
     * Revives the forum.
     */
    public function revive(RepositoryInterface $forum): PodiumResponse
    {
        if (!$forum instanceof ForumRepositoryInterface || !$this->beforeRevive()) {
            return PodiumResponse::error();
        }

        try {
            if (!$forum->revive()) {
                return PodiumResponse::error($forum->getErrors());
            }

            $this->afterRevive($forum);

            return PodiumResponse::success();
        } catch (Throwable $exc) {
            Yii::error(['Exception while reviving forum', $exc->getMessage(), $exc->getTraceAsString()], 'podium');

            return PodiumResponse::error(['exception' => $exc]);
        }
    }

    /**
     * Calls after reviving the forum successfully.
     */
    public function afterRevive(ForumRepositoryInterface $forum): void
    {
        $this->trigger(self::EVENT_AFTER_REVIVING, new ArchiveEvent(['repository' => $forum]));
    }
}
