<?php

declare(strict_types=1);

namespace yii\redis;

use yii\db\Exception;

/**
 * SocketException indicates a socket connection failure in [[Connection]].
 */
class SocketException extends Exception
{
    /**
     * @return string the user-friendly name of this exception
     */
    public function getName()
    {
        return 'Redis Socket Exception';
    }
}
