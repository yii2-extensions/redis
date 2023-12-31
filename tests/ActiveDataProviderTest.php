<?php

declare(strict_types=1);

namespace yiiunit\extensions\redis;

use yii\data\ActiveDataProvider;
use yiiunit\extensions\redis\data\ar\ActiveRecord;
use yiiunit\extensions\redis\data\ar\Item;

/**
 * @group redis
 */
class ActiveDataProviderTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        ActiveRecord::$db = $this->getConnection();

        $item = new Item();
        $item->setAttributes(['name' => 'abc', 'category_id' => 1], false);
        $item->save(false);
        $item = new Item();
        $item->setAttributes(['name' => 'def', 'category_id' => 2], false);
        $item->save(false);
    }

    public function testQuery(): void
    {
        $query = Item::find();
        $provider = new ActiveDataProvider(['query' => $query]);
        $this->assertCount(2, $provider->getModels());

        $query = Item::find()->where(['category_id' => 1]);
        $provider = new ActiveDataProvider(['query' => $query]);
        $this->assertCount(1, $provider->getModels());
    }
}
