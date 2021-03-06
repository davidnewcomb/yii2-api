<?php

declare(strict_types=1);

namespace Podium\Api\Services\Forum;

use InvalidArgumentException;
use Podium\Api\Events\BuildEvent;
use Podium\Api\Interfaces\CategorisedBuilderInterface;
use Podium\Api\Interfaces\CategoryRepositoryInterface;
use Podium\Api\Interfaces\ForumRepositoryInterface;
use Podium\Api\Interfaces\MemberRepositoryInterface;
use Podium\Api\Interfaces\RepositoryInterface;
use Podium\Api\PodiumResponse;
use Podium\Api\Services\ServiceException;
use Throwable;
use Yii;
use yii\base\Component;
use yii\db\Transaction;

final class ForumBuilder extends Component implements CategorisedBuilderInterface
{
    public const EVENT_BEFORE_CREATING = 'podium.forum.creating.before';
    public const EVENT_AFTER_CREATING = 'podium.forum.creating.after';
    public const EVENT_BEFORE_EDITING = 'podium.forum.editing.before';
    public const EVENT_AFTER_EDITING = 'podium.forum.editing.after';

    /**
     * Calls before creating the forum.
     */
    private function beforeCreate(): bool
    {
        $event = new BuildEvent();
        $this->trigger(self::EVENT_BEFORE_CREATING, $event);

        return $event->canCreate;
    }

    /**
     * Creates new forum.
     */
    public function create(
        RepositoryInterface $forum,
        MemberRepositoryInterface $author,
        RepositoryInterface $category,
        array $data = []
    ): PodiumResponse {
        if (!$forum instanceof ForumRepositoryInterface) {
            return PodiumResponse::error(
                [
                    'exception' => new InvalidArgumentException(
                        'Forum must be instance of Podium\Api\Interfaces\ForumRepositoryInterface!'
                    ),
                ]
            );
        }

        if (!$category instanceof CategoryRepositoryInterface) {
            return PodiumResponse::error(
                [
                    'exception' => new InvalidArgumentException(
                        'Category must be instance of Podium\Api\Interfaces\CategoryRepositoryInterface!'
                    ),
                ]
            );
        }

        if (!$this->beforeCreate()) {
            return PodiumResponse::error();
        }

        /** @var Transaction $transaction */
        $transaction = Yii::$app->db->beginTransaction();
        try {
            if ($author->isBanned()) {
                throw new ServiceException(['api' => Yii::t('podium.error', 'member.banned')]);
            }

            if (!$forum->create($author, $category, $data)) {
                throw new ServiceException($forum->getErrors());
            }

            $transaction->commit();
        } catch (ServiceException $exc) {
            $transaction->rollBack();

            return PodiumResponse::error($exc->getErrorList());
        } catch (Throwable $exc) {
            $transaction->rollBack();
            Yii::error(['Exception while creating forum', $exc->getMessage(), $exc->getTraceAsString()], 'podium');

            return PodiumResponse::error(['exception' => $exc]);
        }

        $this->afterCreate($forum);

        return PodiumResponse::success();
    }

    /**
     * Calls after creating the forum successfully.
     */
    private function afterCreate(ForumRepositoryInterface $forum): void
    {
        $this->trigger(self::EVENT_AFTER_CREATING, new BuildEvent(['repository' => $forum]));
    }

    /**
     * Calls before editing the forum.
     */
    private function beforeEdit(): bool
    {
        $event = new BuildEvent();
        $this->trigger(self::EVENT_BEFORE_EDITING, $event);

        return $event->canEdit;
    }

    /**
     * Edits the forum.
     */
    public function edit(RepositoryInterface $forum, array $data = []): PodiumResponse
    {
        if (!$forum instanceof ForumRepositoryInterface) {
            return PodiumResponse::error(
                [
                    'exception' => new InvalidArgumentException(
                        'Forum must be instance of Podium\Api\Interfaces\ForumRepositoryInterface!'
                    ),
                ]
            );
        }

        if (!$this->beforeEdit()) {
            return PodiumResponse::error();
        }

        /** @var Transaction $transaction */
        $transaction = Yii::$app->db->beginTransaction();
        try {
            if (!$forum->edit($data)) {
                throw new ServiceException($forum->getErrors());
            }

            $transaction->commit();
        } catch (ServiceException $exc) {
            $transaction->rollBack();

            return PodiumResponse::error($exc->getErrorList());
        } catch (Throwable $exc) {
            $transaction->rollBack();
            Yii::error(['Exception while editing forum', $exc->getMessage(), $exc->getTraceAsString()], 'podium');

            return PodiumResponse::error(['exception' => $exc]);
        }

        $this->afterEdit($forum);

        return PodiumResponse::success();
    }

    /**
     * Calls after editing the forum successfully.
     */
    private function afterEdit(ForumRepositoryInterface $forum): void
    {
        $this->trigger(self::EVENT_AFTER_EDITING, new BuildEvent(['repository' => $forum]));
    }
}
