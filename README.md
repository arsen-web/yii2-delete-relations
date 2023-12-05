# yii2-delete-relations
Yii2 delete relations behavior

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist matthew-p/yii2-delete-relations "@dev"
```

or add

```
"matthew-p/yii2-delete-relations": "@dev"
```

to the require section of your `composer.json` file.

Usage
-----

Once the extension is installed, simply use it in your code by:

Add behavior to Active Record class:

```php
use MP\Yii2DeleteRelations\DeleteRelationsBehavior;
use yii\db\ActiveRecord;

class Sample extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public function behaviors(): array
    {
        return [
            [
                'class'     => DeleteRelationsBehavior::class,
                'relations' => ['categories', 'phone', 'orders'],  // Delete all relations bulk
                'method'    => ['orders' => 0, 'phone' => DeleteRelationsBehavior::DELETE_BY_ONE], // orders - update relation column to 0, phone - use delete method
            ],
        ];
    }
    
    /**
     * Get categories
     *
     * @return ActiveQuery|CategoriesQuery
     */
    public function getCategories(): CategoriesQuery
    {
        return $this->hasMany(Category::class, ['id' => 'category_id']);
    }
    
    /**
     * Get orders
     *
     * @return ActiveQuery|OrdersQuery
     */
    public function getOrders(): OrdersQuery
    {
        return $this->hasMany(Order::class, ['id' => 'order_id']);
    }
    
    /**
     * Get phone
     *
     * @return ActiveQuery|PhoneQuery
     */
    public function getPhone(): PhoneQuery
    {
        return $this->hasOne(Phone::class, ['id' => 'phone_id']);
    }
}
```

That's all. Check it.