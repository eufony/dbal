## Query Builders

```php
$query = Select::from("users")->fields(Op::max("id"));
```

## Clauses

### LIMIT

```php
$query = Select::from("users")->orderBy("id")->limit(25);
$query = Select::from("users")->orderBy("id")->limit(25)->offset(50);
```

### ORDER BY

```php
$query = Select::from("users")->orderBy("id");
$query = Select::from("users")->orderBy(["id" => "desc"]);
$query = Select::from("users")->orderBy(["id", "lucky_number" => "desc"]);
$query = Select::from("users")->orderBy(["id" => "asc", "lucky_number" => "desc"]);
```

### WHERE

```php
$query = Select::from("users")->where(Ex::ge("lucky_number", 2));

$query = Select::from("users")->where(
    Ex::or(
        Ex::and(
            Ex::ge("lucky_number", 2),
            Ex::le("lucky_number", 7),
            Ex::ne("email", null)
        ),
        Ex::and(
            Ex::ne("username", "euphie"),
            Ex::ne("lucky_number", null)
        ),
        Ex::exists(Select::from("users")->where(Ex::gt("id", 9999)))
    )
);
```

#### VALUES

```php
$query = Insert::into("users")->values(["id" => 2, "username" => "euphie"]);
```
