## Paginator

```php
$users = User::where("username", "=", "euphie");
$users = new Paginator(Select::from("users")->where("username", "=", "euphie"), "user_data");

// Method call should be proxy passed to internal Select object
$users->andWhere("lucky_number", "=", 2);

// Paginator is iterable
foreach ($users as $user) {
    print_r($user);
}

// Paginator should be immutable after iteration has begun
$users->orWhere("lucky_number", "=", null);
```
