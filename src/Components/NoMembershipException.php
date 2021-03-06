<?php

declare(strict_types=1);

namespace Podium\Api\Components;

use yii\base\Exception;

class NoMembershipException extends Exception
{
    public function getName(): string
    {
        return 'No Membership Exception';
    }
}
