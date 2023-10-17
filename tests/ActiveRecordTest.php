<?php

declare(strict_types=1);

namespace yiiunit\extensions\redis;

use yii\redis\ActiveQuery;
use yii\redis\LuaScriptBuilder;
use yiiunit\extensions\redis\data\ar\ActiveRecord;
use yiiunit\extensions\redis\data\ar\ActiveRecordTestTrait;
use yiiunit\extensions\redis\data\ar\Customer;
use yiiunit\extensions\redis\data\ar\Item;
use yiiunit\extensions\redis\data\ar\Order;
use yiiunit\extensions\redis\data\ar\OrderItem;
use yiiunit\extensions\redis\data\ar\OrderItemWithNullFK;
use yiiunit\extensions\redis\data\ar\OrderWithNullFK;

/**
 * @group redis
 */
class ActiveRecordTest extends TestCase
{
    use ActiveRecordTestTrait;

    /**
     * @return string
     */
    public function getCustomerClass()
    {
        return Customer::className();
    }

    /**
     * @return string
     */
    public function getItemClass()
    {
        return Item::className();
    }

    /**
     * @return string
     */
    public function getOrderClass()
    {
        return Order::className();
    }

    /**
     * @return string
     */
    public function getOrderItemClass()
    {
        return OrderItem::className();
    }

    /**
     * @return string
     */
    public function getOrderWithNullFKClass()
    {
        return OrderWithNullFK::className();
    }

    /**
     * @return string
     */
    public function getOrderItemWithNullFKmClass()
    {
        return OrderItemWithNullFK::className();
    }

    public function setUp(): void
    {
        parent::setUp();
        ActiveRecord::$db = $this->getConnection();

        $customer = new Customer();
        $customer->setAttributes(['email' => 'user1@example.com', 'name' => 'user1', 'address' => 'address1', 'status' => 1, 'profile_id' => 1], false);
        $customer->save(false);
        $customer = new Customer();
        $customer->setAttributes(['email' => 'user2@example.com', 'name' => 'user2', 'address' => 'address2', 'status' => 1, 'profile_id' => null], false);
        $customer->save(false);
        $customer = new Customer();
        $customer->setAttributes(['email' => 'user3@example.com', 'name' => 'user3', 'address' => 'address3', 'status' => 2, 'profile_id' => 2], false);
        $customer->save(false);

        //		INSERT INTO category (name) VALUES ('Books');
        //		INSERT INTO category (name) VALUES ('Movies');

        $item = new Item();
        $item->setAttributes(['name' => 'Agile Web Application Development with Yii1.1 and PHP5', 'category_id' => 1], false);
        $item->save(false);
        $item = new Item();
        $item->setAttributes(['name' => 'Yii 1.1 Application Development Cookbook', 'category_id' => 1], false);
        $item->save(false);
        $item = new Item();
        $item->setAttributes(['name' => 'Ice Age', 'category_id' => 2], false);
        $item->save(false);
        $item = new Item();
        $item->setAttributes(['name' => 'Toy Story', 'category_id' => 2], false);
        $item->save(false);
        $item = new Item();
        $item->setAttributes(['name' => 'Cars', 'category_id' => 2], false);
        $item->save(false);

        $order = new Order();
        $order->setAttributes(['customer_id' => 1, 'created_at' => 1_325_282_384, 'total' => 110.0], false);
        $order->save(false);
        $order = new Order();
        $order->setAttributes(['customer_id' => 2, 'created_at' => 1_325_334_482, 'total' => 33.0], false);
        $order->save(false);
        $order = new Order();
        $order->setAttributes(['customer_id' => 2, 'created_at' => 1_325_502_201, 'total' => 40.0], false);
        $order->save(false);

        $orderItem = new OrderItem();
        $orderItem->setAttributes(['order_id' => 1, 'item_id' => 1, 'quantity' => 1, 'subtotal' => 30.0], false);
        $orderItem->save(false);
        $orderItem = new OrderItem();
        $orderItem->setAttributes(['order_id' => 1, 'item_id' => 2, 'quantity' => 2, 'subtotal' => 40.0], false);
        $orderItem->save(false);
        $orderItem = new OrderItem();
        $orderItem->setAttributes(['order_id' => 2, 'item_id' => 4, 'quantity' => 1, 'subtotal' => 10.0], false);
        $orderItem->save(false);
        $orderItem = new OrderItem();
        $orderItem->setAttributes(['order_id' => 2, 'item_id' => 5, 'quantity' => 1, 'subtotal' => 15.0], false);
        $orderItem->save(false);
        $orderItem = new OrderItem();
        $orderItem->setAttributes(['order_id' => 2, 'item_id' => 3, 'quantity' => 1, 'subtotal' => 8.0], false);
        $orderItem->save(false);
        $orderItem = new OrderItem();
        $orderItem->setAttributes(['order_id' => 3, 'item_id' => 2, 'quantity' => 1, 'subtotal' => 40.0], false);
        $orderItem->save(false);
        // insert a record with non-integer PK
        $orderItem = new OrderItem();
        $orderItem->setAttributes(['order_id' => 3, 'item_id' => 'nostr', 'quantity' => 1, 'subtotal' => 40.0], false);
        $orderItem->save(false);

        $order = new OrderWithNullFK();
        $order->setAttributes(['customer_id' => 1, 'created_at' => 1_325_282_384, 'total' => 110.0], false);
        $order->save(false);
        $order = new OrderWithNullFK();
        $order->setAttributes(['customer_id' => 2, 'created_at' => 1_325_334_482, 'total' => 33.0], false);
        $order->save(false);
        $order = new OrderWithNullFK();
        $order->setAttributes(['customer_id' => 2, 'created_at' => 1_325_502_201, 'total' => 40.0], false);
        $order->save(false);

        $orderItem = new OrderItemWithNullFK();
        $orderItem->setAttributes(['order_id' => 1, 'item_id' => 1, 'quantity' => 1, 'subtotal' => 30.0], false);
        $orderItem->save(false);
        $orderItem = new OrderItemWithNullFK();
        $orderItem->setAttributes(['order_id' => 1, 'item_id' => 2, 'quantity' => 2, 'subtotal' => 40.0], false);
        $orderItem->save(false);
        $orderItem = new OrderItemWithNullFK();
        $orderItem->setAttributes(['order_id' => 2, 'item_id' => 4, 'quantity' => 1, 'subtotal' => 10.0], false);
        $orderItem->save(false);
        $orderItem = new OrderItemWithNullFK();
        $orderItem->setAttributes(['order_id' => 2, 'item_id' => 5, 'quantity' => 1, 'subtotal' => 15.0], false);
        $orderItem->save(false);
        $orderItem = new OrderItemWithNullFK();
        $orderItem->setAttributes(['order_id' => 2, 'item_id' => 5, 'quantity' => 1, 'subtotal' => 8.0], false);
        $orderItem->save(false);
        $orderItem = new OrderItemWithNullFK();
        $orderItem->setAttributes(['order_id' => 3, 'item_id' => 2, 'quantity' => 1, 'subtotal' => 40.0], false);
        $orderItem->save(false);
    }

    /**
     * overridden because null values are not part of the asArray result in redis
     */
    public function testFindAsArray(): void
    {
        /* @var $customerClass \yii\db\ActiveRecordInterface */
        $customerClass = $this->getCustomerClass();

        // asArray
        $customer = $customerClass::find()->where(['id' => 2])->asArray()->one();
        $this->assertEquals([
            'id' => 2,
            'email' => 'user2@example.com',
            'name' => 'user2',
            'address' => 'address2',
            'status' => 1,
        ], $customer);

        // find all asArray
        $customers = $customerClass::find()->asArray()->all();
        $this->assertCount(3, $customers);
        $this->assertArrayHasKey('id', $customers[0]);
        $this->assertArrayHasKey('name', $customers[0]);
        $this->assertArrayHasKey('email', $customers[0]);
        $this->assertArrayHasKey('address', $customers[0]);
        $this->assertArrayHasKey('status', $customers[0]);
        $this->assertArrayHasKey('id', $customers[1]);
        $this->assertArrayHasKey('name', $customers[1]);
        $this->assertArrayHasKey('email', $customers[1]);
        $this->assertArrayHasKey('address', $customers[1]);
        $this->assertArrayHasKey('status', $customers[1]);
        $this->assertArrayHasKey('id', $customers[2]);
        $this->assertArrayHasKey('name', $customers[2]);
        $this->assertArrayHasKey('email', $customers[2]);
        $this->assertArrayHasKey('address', $customers[2]);
        $this->assertArrayHasKey('status', $customers[2]);
    }

    public function testStatisticalFind(): void
    {
        // find count, sum, average, min, max, scalar
        $this->assertEquals(3, Customer::find()->count());
        $this->assertEquals(6, Customer::find()->sum('id'));
        $this->assertEquals(2, Customer::find()->average('id'));
        $this->assertEquals(1, Customer::find()->min('id'));
        $this->assertEquals(3, Customer::find()->max('id'));

        $this->assertEquals(7, OrderItem::find()->count());
        $this->assertEquals(8, OrderItem::find()->sum('quantity'));
    }

    // TODO test serial column incr

    public function testUpdatePk(): void
    {
        // updateCounters
        $pk = ['order_id' => 2, 'item_id' => 4];
        /** @var OrderItem $orderItem */
        $orderItem = OrderItem::findOne($pk);
        $this->assertEquals(2, $orderItem->order_id);
        $this->assertEquals(4, $orderItem->item_id);

        $orderItem->order_id = 2;
        $orderItem->item_id = 10;
        $orderItem->save();

        $this->assertNull(OrderItem::findOne($pk));
        $this->assertNotNull(OrderItem::findOne(['order_id' => 2, 'item_id' => 10]));
    }

    public function testFilterWhere(): void
    {
        // should work with hash format
        $query = new ActiveQuery('dummy');
        $query->filterWhere([
            'id' => 0,
            'title' => '   ',
            'author_ids' => [],
        ]);
        $this->assertEquals(['id' => 0], $query->where);

        $query->andFilterWhere(['status' => null]);
        $this->assertEquals(['id' => 0], $query->where);

        $query->orFilterWhere(['name' => '']);
        $this->assertEquals(['id' => 0], $query->where);

        // should work with operator format
        $query = new ActiveQuery('dummy');
        $condition = ['like', 'name', 'Alex'];
        $query->filterWhere($condition);
        $this->assertEquals($condition, $query->where);

        $query->andFilterWhere(['between', 'id', null, null]);
        $this->assertEquals($condition, $query->where);

        $query->orFilterWhere(['not between', 'id', null, null]);
        $this->assertEquals($condition, $query->where);

        $query->andFilterWhere(['in', 'id', []]);
        $this->assertEquals($condition, $query->where);

        $query->andFilterWhere(['not in', 'id', []]);
        $this->assertEquals($condition, $query->where);

        $query->andFilterWhere(['not in', 'id', []]);
        $this->assertEquals($condition, $query->where);

        $query->andFilterWhere(['like', 'id', '']);
        $this->assertEquals($condition, $query->where);

        $query->andFilterWhere(['or like', 'id', '']);
        $this->assertEquals($condition, $query->where);

        $query->andFilterWhere(['not like', 'id', '   ']);
        $this->assertEquals($condition, $query->where);

        $query->andFilterWhere(['or not like', 'id', null]);
        $this->assertEquals($condition, $query->where);

        $query->andFilterWhere(['>', 'id', null]);
        $this->assertEquals($condition, $query->where);

        $query->andFilterWhere(['>=', 'id', null]);
        $this->assertEquals($condition, $query->where);

        $query->andFilterWhere(['<', 'id', null]);
        $this->assertEquals($condition, $query->where);

        $query->andFilterWhere(['<=', 'id', null]);
        $this->assertEquals($condition, $query->where);
    }

    public function testFilterWhereRecursively(): void
    {
        $query = new ActiveQuery('dummy');
        $query->filterWhere(['and', ['like', 'name', ''], ['like', 'title', ''], ['id' => 1], ['not', ['like', 'name', '']]]);
        $this->assertEquals(['and', ['id' => 1]], $query->where);
    }

    public function testAutoIncrement(): void
    {
        Customer::getDb()->executeCommand('FLUSHDB');

        $customer = new Customer();
        $customer->setAttributes(['id' => 4, 'email' => 'user4@example.com', 'name' => 'user4', 'address' => 'address4', 'status' => 1, 'profile_id' => null], false);
        $customer->save(false);
        $this->assertEquals(4, $customer->id);
        $customer = new Customer();
        $customer->setAttributes(['email' => 'user5@example.com', 'name' => 'user5', 'address' => 'address5', 'status' => 1, 'profile_id' => null], false);
        $customer->save(false);
        $this->assertEquals(5, $customer->id);

        $customer = new Customer();
        $customer->setAttributes(['id' => 1, 'email' => 'user1@example.com', 'name' => 'user1', 'address' => 'address1', 'status' => 1, 'profile_id' => null], false);
        $customer->save(false);
        $this->assertEquals(1, $customer->id);
        $customer = new Customer();
        $customer->setAttributes(['email' => 'user6@example.com', 'name' => 'user6', 'address' => 'address6', 'status' => 1, 'profile_id' => null], false);
        $customer->save(false);
        $this->assertEquals(6, $customer->id);


        /** @var Customer $customer */
        $customer = Customer::findOne(4);
        $this->assertNotNull($customer);
        $this->assertEquals('user4', $customer->name);

        $customer = Customer::findOne(5);
        $this->assertNotNull($customer);
        $this->assertEquals('user5', $customer->name);

        $customer = Customer::findOne(1);
        $this->assertNotNull($customer);
        $this->assertEquals('user1', $customer->name);

        $customer = Customer::findOne(6);
        $this->assertNotNull($customer);
        $this->assertEquals('user6', $customer->name);
    }

    public function testEscapeData(): void
    {
        $customer = new Customer();
        $customer->email = "the People's Republic of China";
        $customer->save(false);

        /** @var Customer $c */
        $c = Customer::findOne(['email' => "the People's Republic of China"]);
        $this->assertSame("the People's Republic of China", $c->email);
    }

    public function testFindEmptyWith(): void
    {
        Order::getDb()->flushdb();
        $orders = Order::find()
            ->where(['total' => 100000])
            ->orWhere(['total' => 1])
            ->with('customer')
            ->all();
        $this->assertEquals([], $orders);
    }

    public function testEmulateExecution(): void
    {
        $rows = Order::find()
            ->emulateExecution()
            ->all();
        $this->assertSame([], $rows);

        $row = Order::find()
            ->emulateExecution()
            ->one();
        $this->assertSame(null, $row);

        $exists = Order::find()
            ->emulateExecution()
            ->exists();
        $this->assertSame(false, $exists);

        $count = Order::find()
            ->emulateExecution()
            ->count();
        $this->assertSame(0, $count);

        $sum = Order::find()
            ->emulateExecution()
            ->sum('id');
        $this->assertSame(0, $sum);

        $sum = Order::find()
            ->emulateExecution()
            ->average('id');
        $this->assertSame(0, $sum);

        $max = Order::find()
            ->emulateExecution()
            ->max('id');
        $this->assertSame(null, $max);

        $min = Order::find()
            ->emulateExecution()
            ->min('id');
        $this->assertSame(null, $min);

        $scalar = Order::find()
            ->emulateExecution()
            ->scalar('id');
        $this->assertSame(null, $scalar);

        $column = Order::find()
            ->emulateExecution()
            ->column('id');
        $this->assertSame([], $column);
    }

    /**
     * @see https://github.com/yiisoft/yii2-redis/issues/93
     */
    public function testDeleteAllWithCondition(): void
    {
        $deletedCount = Order::deleteAll(['in', 'id', [1, 2, 3]]);
        $this->assertEquals(3, $deletedCount);
    }

    public function testBuildKey(): void
    {
        $pk = ['order_id' => 3, 'item_id' => 'nostr'];
        $key = OrderItem::buildKey($pk);

        $orderItem = OrderItem::findOne($pk);
        $this->assertNotNull($orderItem);

        $pk = ['order_id' => $orderItem->order_id, 'item_id' => $orderItem->item_id];
        $this->assertEquals($key, OrderItem::buildKey($pk));
    }

    public function testNotCondition(): void
    {
        /* @var $orderClass \yii\db\ActiveRecordInterface */
        $orderClass = $this->getOrderClass();

        /* @var $this TestCase|ActiveRecordTestTrait */
        $orders = $orderClass::find()->where(['not', ['customer_id' => 2]])->all();
        $this->assertCount(1, $orders);
        $this->assertEquals(1, $orders[0]['customer_id']);
    }

    public function testBetweenCondition(): void
    {
        /* @var $orderClass \yii\db\ActiveRecordInterface */
        $orderClass = $this->getOrderClass();

        /* @var $this TestCase|ActiveRecordTestTrait */
        $orders = $orderClass::find()->where(['between', 'total', 30, 50])->all();
        $this->assertCount(2, $orders);
        $this->assertEquals(2, $orders[0]['customer_id']);
        $this->assertEquals(2, $orders[1]['customer_id']);

        $orders = $orderClass::find()->where(['not between', 'total', 30, 50])->all();
        $this->assertCount(1, $orders);
        $this->assertEquals(1, $orders[0]['customer_id']);
    }

    public function testInCondition(): void
    {
        /* @var $orderClass \yii\db\ActiveRecordInterface */
        $orderClass = $this->getOrderClass();

        /* @var $this TestCase|ActiveRecordTestTrait */
        $orders = $orderClass::find()->where(['in', 'customer_id', [1, 2]])->all();
        $this->assertCount(3, $orders);

        $orders = $orderClass::find()->where(['not in', 'customer_id', [1, 2]])->all();
        $this->assertCount(0, $orders);

        $orders = $orderClass::find()->where(['in', 'customer_id', [1]])->all();
        $this->assertCount(1, $orders);
        $this->assertEquals(1, $orders[0]['customer_id']);

        $orders = $orderClass::find()->where(['in', 'customer_id', [2]])->all();
        $this->assertCount(2, $orders);
        $this->assertEquals(2, $orders[0]['customer_id']);
        $this->assertEquals(2, $orders[1]['customer_id']);
    }

    public function testCountQuery(): void
    {
        /* @var $itemClass \yii\db\ActiveRecordInterface */
        $itemClass = $this->getItemClass();

        $query = $itemClass::find();
        $this->assertEquals(5, $query->count());

        $query = $itemClass::find()->where(['category_id' => 1]);
        $this->assertEquals(2, $query->count());

        // negative values deactivate limit and offset (in case they were set before)
        $query = $itemClass::find()->where(['category_id' => 1])->limit(-1)->offset(-1);
        $this->assertEquals(2, $query->count());
    }

    /**
     * @dataProvider \yiiunit\extensions\redis\providers\Data::illegalValuesForWhere
     */
    public function testValueEscapingInWhere($filterWithInjection, $expectedStrings, $unexpectedStrings = []): void
    {
        /* @var $itemClass \yii\db\ActiveRecordInterface */
        $itemClass = $this->getItemClass();

        $query = $itemClass::find()->where($filterWithInjection['id']);
        $lua = new LuaScriptBuilder();
        $script = $lua->buildOne($query);

        foreach ($expectedStrings as $string) {
            $this->assertStringContainsString($string, $script);
        }
        foreach ($unexpectedStrings as $string) {
            $this->assertStringNotContainsString($string, $script);
        }
    }

    /**
     * @dataProvider \yiiunit\extensions\redis\providers\Data::illegalValuesForFindByCondition
     */
    public function testValueEscapingInFindByCondition($filterWithInjection, $expectedStrings, $unexpectedStrings = []): void
    {
        /* @var $itemClass \yii\db\ActiveRecordInterface */
        $itemClass = $this->getItemClass();

        $query = $this->invokeMethod(new $itemClass(), 'findByCondition', [$filterWithInjection['id']]);
        $lua = new LuaScriptBuilder();
        $script = $lua->buildOne($query);

        foreach ($expectedStrings as $string) {
            $this->assertStringContainsString($string, $script);
        }
        foreach ($unexpectedStrings as $string) {
            $this->assertStringNotContainsString($string, $script);
        }
        // ensure injected FLUSHALL call did not succeed
        $query->one();
        $this->assertGreaterThan(3, $itemClass::find()->count());
    }

    public function testCompareCondition(): void
    {
        /* @var $orderClass \yii\db\ActiveRecordInterface */
        $orderClass = $this->getOrderClass();

        /* @var $this TestCase|ActiveRecordTestTrait */
        $orders = $orderClass::find()->where(['>', 'total', 30])->all();
        $this->assertCount(3, $orders);
        $this->assertEquals(1, $orders[0]['customer_id']);
        $this->assertEquals(2, $orders[1]['customer_id']);
        $this->assertEquals(2, $orders[2]['customer_id']);

        $orders = $orderClass::find()->where(['>=', 'total', 40])->all();
        $this->assertCount(2, $orders);
        $this->assertEquals(1, $orders[0]['customer_id']);
        $this->assertEquals(2, $orders[1]['customer_id']);

        $orders = $orderClass::find()->where(['<', 'total', 41])->all();
        $this->assertCount(2, $orders);
        $this->assertEquals(2, $orders[0]['customer_id']);
        $this->assertEquals(2, $orders[1]['customer_id']);

        $orders = $orderClass::find()->where(['<=', 'total', 40])->all();
        $this->assertCount(2, $orders);
        $this->assertEquals(2, $orders[0]['customer_id']);
        $this->assertEquals(2, $orders[1]['customer_id']);
    }

    public function testStringCompareCondition(): void
    {
        /* @var $itemClass \yii\db\ActiveRecordInterface */
        $itemClass = $this->getItemClass();

        /* @var $this TestCase|ActiveRecordTestTrait */
        $items = $itemClass::find()->where(['>', 'name', 'A'])->all();
        $this->assertCount(5, $items);
        $this->assertSame('Agile Web Application Development with Yii1.1 and PHP5', $items[0]['name']);

        $items = $itemClass::find()->where(['>=', 'name', 'Ice Age'])->all();
        $this->assertCount(3, $items);
        $this->assertSame('Yii 1.1 Application Development Cookbook', $items[0]['name']);
        $this->assertSame('Toy Story', $items[2]['name']);

        $items = $itemClass::find()->where(['<', 'name', 'Cars'])->all();
        $this->assertCount(1, $items);
        $this->assertSame('Agile Web Application Development with Yii1.1 and PHP5', $items[0]['name']);

        $items = $itemClass::find()->where(['<=', 'name', 'Carts'])->all();
        $this->assertCount(2, $items);
    }

    public function testFind(): void
    {
        /* @var $customerClass \yii\db\ActiveRecordInterface */
        $customerClass = $this->getCustomerClass();

        // find one
        /* @var $this TestCase|ActiveRecordTestTrait */
        $result = $customerClass::find();
        $this->assertInstanceOf('\\yii\\db\\ActiveQueryInterface', $result);
        $customer = $result->one();
        $this->assertInstanceOf($customerClass, $customer);

        // find all
        $customers = $customerClass::find()->all();
        $this->assertCount(3, $customers);
        $this->assertInstanceOf($customerClass, $customers[0]);
        $this->assertInstanceOf($customerClass, $customers[1]);
        $this->assertInstanceOf($customerClass, $customers[2]);

        // find by a single primary key
        $customer = $customerClass::findOne(2);
        $this->assertInstanceOf($customerClass, $customer);
        $this->assertEquals('user2', $customer->name);
        $customer = $customerClass::findOne(5);
        $this->assertNull($customer);
        $customer = $customerClass::findOne(['id' => [5, 6, 1]]);
        $this->assertInstanceOf($customerClass, $customer);
        $customer = $customerClass::find()->where(['id' => [5, 6, 1]])->one();
        $this->assertNotNull($customer);

        // find by column values
        $customer = $customerClass::findOne(['id' => 2, 'name' => 'user2']);
        $this->assertInstanceOf($customerClass, $customer);
        $this->assertEquals('user2', $customer->name);
        $customer = $customerClass::findOne(['id' => 2, 'name' => 'user1']);
        $this->assertNull($customer);
        $customer = $customerClass::findOne(['id' => 5]);
        $this->assertNull($customer);
        $customer = $customerClass::findOne(['name' => 'user5']);
        $this->assertNull($customer);

        // find by attributes
        $customer = $customerClass::find()->where(['name' => 'user2'])->one();
        $this->assertInstanceOf($customerClass, $customer);
        $this->assertEquals(2, $customer->id);

        // scope
        $this->assertCount(2, $customerClass::find()->active()->all());
        $this->assertEquals(2, $customerClass::find()->active()->count());
    }
}
