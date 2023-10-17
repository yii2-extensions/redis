<?php

declare(strict_types=1);

namespace yiiunit\extensions\redis\data\ar;

/**
 * Class Order
 *
 * @property int $id
 * @property int $customer_id
 * @property int $created_at
 * @property string $total
 */
class OrderWithNullFK extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function primaryKey()
    {
        return ['id'];
    }

    /**
     * @inheritdoc
     */
    public function attributes()
    {
        return ['id', 'customer_id', 'created_at', 'total'];
    }
}
