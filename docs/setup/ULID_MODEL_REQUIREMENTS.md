# ULID Model Requirements

Any Eloquent model whose table uses `ulid('id')` as the primary key must use Laravel's `HasUlids` trait.

Example:

```php
use Illuminate\Database\Eloquent\Concerns\HasUlids;

final class User extends Authenticatable
{
    use HasUlids;
}
```

Without the trait, PostgreSQL receives a null primary key during inserts.

The `User` model is covered by `tests/Feature/UserUlidTest.php`.
