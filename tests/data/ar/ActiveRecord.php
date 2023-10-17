<?php

declare(strict_types=1);

namespace yiiunit\extensions\redis\data\ar;

class ActiveRecord extends \yii\redis\ActiveRecord
{
    /**
     * @return \yii\redis\Connection
     */
    public static $db;

    /**
     * @return \yii\redis\Connection
     */
    public static function getDb()
    {
        return self::$db;
    }
}
