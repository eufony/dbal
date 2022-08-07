# Query Builders

## Examples

### Select

Select all fields from all entries in the `users` table:

```php
$query = Select::from("users");
```

Select only the `id` and `name` fields from all entries in the `users table:

```php
$query = Select::from("users")->fields("id", "name");

// Or, alternatively:

$fields = [ "id", "name" ];
$query = Select::from("users")->fields(...$fields);
```

Select all fields from all entries in the `users` table, grouped by the `name` and `lucky_number` fields:

```php
$query = Select::from("users")->groupBy("name", "lucky_number");

// Or, alternatively:

$fields = [ "name", "lucky_number" ];
$query = Select::from("users")->groupBy(...$fields);
```

Select all fields from all entries in the `users` table, grouped by the `name` field, but only if there are at least 2
entries in the group:

```php
$query = Select::from("users")->groupBy("name")->having(Expr::ge("count(*)", 2));
```

> **Note:** Currently, the Eufony DBAL only has *partial* support for aggregate functions.

See the [HAVING](#where-and-having) clause for more example Expressions.

Select all fields from all entires in the joined `users` and `emails` tables:

```php
$query = Select::from("users", as: "user")
            ->leftJoin("emails", as: "email", on: Expr::same("user.id", "email.user_id"));
```

See the [JOIN](#join) clause for more examples.

Select all fields from the first 5 entries in the `users` table:

```php
$query = Select::from("users")->limit(5);
```

> **Note:** You would probably only use this in conjunction with the [ORDER BY](#order-by) clause.

Select all fields from the 2nd until the 7th entry in the `users` table:

```php
$query = Select::from("users")->limit(2, 7);
```

> **Note:** You would probably only use this in conjunction with the [ORDER BY](#order-by) clause.

Select all fields from all entries in the `users` table, sorted alphabetically by the `name` field in ascending order:

```php
$query = Select::from("users")->orderBy("name");
```

Select all fields from all entries in the `users` table with the `name` 'Euphie':

```php
$query = Select::from("users")->where(Expr::eq("name", "Euphie"));
```

See the [WHERE](#where-and-having) clause for more examples.

### Insert

Insert the values `id = 5` and `name = 'Euphie'` into the `users` table:

```php
$query = Insert::into("users")->values([ "id" => 5, "name" => Euphie ]);
```

### Update

Update the `users` table to set the `lucky_number` of all users to `2`:

```php
$query = Update::table("users")->values([ "lucky_number" => 2 ]);
```

Update the `users` table to set `name = 'Euphie` of the user with the `id` of 5:

```php
$query = Update::table("users")->values([ "name" => "Euphie" ])->where(Expr::eq("id", 5));
```

See the [WHERE](#where-and-having) clause for more examples.

### Delete

Delete all entries in the `users` table (truncate table):

```php
$query = Delete::from("users");
```

Delete the user with the `id` of 5 from the `users` table:

```php
$query = Delete::from("users")->where(Expr::eq("id", 5));
```

See the [WHERE](#where-and-having) clause for more examples.

### Create

*The `Create` query builder is currently not yet implemented.*

### Alter

*The `Alter` query builder is currently not yet implemented.*

### Drop

*The `Drop` query builder is currently not yet implemented.*

# Clauses

## Examples

### JOIN

> **Note:** For all following examples, the `leftJoin()` and `innerJoin()` functions are (syntactically) equivalent.

Join tables `a` and `b` on `a.id = b.a_id` (one-to-many):

```php
$query = Select::from("a")
            ->leftJoin("b", on: Expr::same("a.id", "b.a_id"));
```

Join `a` and `b` on `a.b_id` = `b.id` (many-to-one):

```php
$query = Select::from("a")
            ->leftJoin("b", on: Expr::same("a.b_id", "b.id"));
```

Join tables `a`, `b`, and `c` on `a.b_id = b.id` and `b.c_id = c.id` (many-to-one-to-many):

```php
$query = Select::from("a")
            ->leftJoin("b", on: Expr::same("a.b_id", "b.id"))
            ->leftJoin("c", on: Expr::same("b.c_id", "c.id"));
```

Join table `b` twice as `b1` and `b2` with table `a` on `a.b1_id = b1.id` and `a.b2_id = b2.id`:

```php
$query = Select::from("a")
            ->leftJoin("b", as: "b1", on: Expr::same("a.b1_id", "b1.id"))
            ->leftJoin("b", as: "b2", on: Expr::same("a.b2_id", "b2.id"));
```

Join table `a` as `a2` onto itself (as `a1`) on `a1.a2_id = a2.id`:

```php
$query = Select::from("a", as: "a1")
            ->leftJoin("a", as: "a2", on: Expr::same("a1.a2_id", "a2.id"));
```

### ORDER BY

Order by `id` in ascending order:

```php
$query = Select::from("users")->orderBy("id");

// Or, alternatively:

$query = Select::from("users")->orderBy([ "id" ]);

// Or, alternatively:

$query = Select::from("users")->orderBy([ "id" => "asc" ]);
```

Order by `id` in descending order:

```php
$query = Select::from("users")->orderBy([ "id" => "desc" ]);
```

Order by `id` and `name`, both in ascending order:

```php
$query = Select::from("users")->orderBy([ "id", "name" ]);

// Or, alternatively:

$query = Select::from("users")->orderBy([ "id" => "asc", "name" => "asc" ]);
```

Order by `id` in ascending order, then `name` in descending order:

```php
$query = Select::from("users")->orderBy([ "id", "name" => "desc" ]);

// Or, alternatively:

$query = Select::from("users")->orderBy([ "id" => "asc", "name" => "desc" ]);
```

Order by `id` and `name, both in descending order:

```php
$query = Select::from("users")->orderBy([ "id" => "desc", "name" => "desc" ]);
```

### WHERE and HAVING

Both the WHERE and HAVING clauses accept any valid expression (Expr) as a parameter. These expressions can be:

Always evaluate to be `true`:

```php
$expr = Expr::true();
```

Always evaluate to be `false` (using NOT for negation):

```php
$expr = Expr::not(Expr::true());
```

Evaluate to be `true` if all the secondary expressions evaluate to be `true` (using AND):

```php
$expr = Expr::and(Expr::true(), Expr::true(), Expr::true());

// Or, alternatively:

$sub = [ Expr::true(), Expr::true(), Expr::true() ];
$expr = Expr::and(...$sub);
```

Evaluate to be `true` if any of the secondary expressions evaluate to be `true` (using OR):

```php
$expr = Expr::or(Expr::true(), Expr::not(Expr::true()), Expr::not(Expr::true()));

// Or, alternatively:

$sub = [ Expr::true(), Expr::not(Expr::true()), Expr::not(Expr::true()) ];
$expr = Expr::or(...$sub);
```

Similarly, shorthands for NAND, NOR, XOR, and XNOR can also be used:

```php
$expr = Expr::nand(/* ... */);
$expr = Expr::nor(/* ... */);
$expr = Expr::xor(/* ... */);
$expr = Expr::xnor(/* ... */);
```

Check for equality between two fields:

```php
$expr = Expr::same("primary", "foreign");
```

Check for equality or inequality between a field and a literal:

```php
$expr = Expr::eq("id", 5)
$expr = Expr::ne("name", "Euphie")
```

> **Note:** A literal can be of any type (including `null`) except for `array`.

Compare a numerical (`int` or `float`) field and a literal:

```php
$expr = Expr::lt("id", 5);
$expr = Expr::le("id", 5);
$expr = Expr::ge("id", 5);
$expr = Expr::gt("id", 5);
```

Compare two numerical (`int` or `float`) fields:

```php
$expr = Expr::lt("primary", "foreign");
$expr = Expr::le("primary", "foreign");
$expr = Expr::ge("primary", "foreign");
$expr = Expr::gt("primary", "foreign");
```

Compare similarity between a field and a string literal:

```php
$expr = Expr::like("exact", "Euphie");
$expr = Expr::like("right", "Euphie%");
$expr = Expr::like("left", "%Euphie");
$expr = Expr::like("both", "%Euphie%");
```

Check if a field's value is in a list of literals:

```php
$expr = Expr::in("id", [ 1, 3, 5, 7, 9 ]);
```

Check if a subquery returns at least one result:

```php
$expr = Expr::exists(Select::from(/* ... */));
```
