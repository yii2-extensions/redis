<?php

declare(strict_types=1);

namespace yiiunit\extensions\redis\data\ar;

/**
 * Class OrderItem
 *
 * @property int $order_id
 * @property int $item_id
 * @property int $quantity
 * @property string $subtotal
 */
class OrderItemWithNullFK extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function primaryKey()
    {
        return ['order_id', 'item_id'];
    }

    /**
     * @inheritdoc
     */
    public function attributes()
    {
        return ['order_id', 'item_id', 'quantity', 'subtotal'];
    }
}
