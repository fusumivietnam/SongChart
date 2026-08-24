# Feature Test Assertion Rules

Status: mandatory regression guidance.

## Purpose

Prevent false-negative Laravel Feature tests caused by assertions that are broader or more brittle than the behavior under test.

## Rules

1. Scope negative assertions to canonical URLs, semantic containers or structured attributes. Do not reject a label across the whole page when it may appear in suggestions, navigation or supporting copy.
2. Do not assert a flattened sentence across nested HTML elements. `assertSee()` searches rendered HTML and does not automatically join text around tags.
3. Prefer stable behavior markers: route URLs, `aria-*`, `data-*`, `rel`, result counts, ranges and response status.
4. Use `assertSee(..., false)` only for intentional HTML/attribute assertions.
5. Keep assertions independent from utility CSS classes and decorative markup.
6. When accessible text matters, test the accessible label or separate visible fragments unless a DOM-aware text assertion is deliberately used and verified.

## Pagination example

Avoid:

```php
$response->assertSee('Trang 1 / 3');
```

when the template renders:

```html
Trang <strong>1</strong> / 3
```

Prefer:

```php
$response
    ->assertSee('data-current-page="1"', false)
    ->assertSee('data-last-page="3"', false)
    ->assertSee('page=2', false);
```

## Documentation duty

When a false-negative pattern repeats or can affect multiple stages, update this document and the affected phase documents before considering the fix complete.

## Provider destination assertions

Provider tests should assert semantic `data-provider-*` state plus the presence or absence of exact `href` attributes. Do not infer actionability from button text or color alone.

## Source-completeness and autoload assertions

Feature tests must exercise route/controller loading for newly added endpoints. Syntax lint alone cannot detect a missing parent class that is referenced only when the route is dispatched.

For shared framework boundaries such as `App\Http\Controllers\Controller`, keep a focused regression test that asserts the class exists and new controllers inherit from it. Packaging validation should also verify required foundation files are present.


## Framework-owned route assertions

When Fortify or another framework package owns an endpoint, assert redirects with its named route rather than a guessed literal path.

Avoid:

```php
$response->assertRedirect('/verify-email');
```

Prefer:

```php
$response->assertRedirect(route('verification.notice'));
```

Do not add duplicate application routes merely to make an incorrect literal-path assertion pass.

## Partial model hydration assertions

Security and account tests must include at least one partially hydrated model when a helper reads optional columns. This catches direct-property access that can throw `MissingAttributeException` on models created without refreshed nullable attributes or loaded through a limited `select()`.


## Security attribute boundary tests

For encrypted or optional authentication columns, add a partial-hydration test and a source-boundary assertion preventing direct Blade access. Runtime tests alone may miss a second direct access path after one helper has been fixed.

## Navigation and role-visibility assertions

Role-visibility tests must assert stable navigation selectors, not page-wide human text. The same wording may legitimately appear in dashboard work items, summaries, help copy or technical details.

Admin navigation items must expose a stable selector such as `data-admin-nav="imports"`.

Avoid:

```php
$response->assertDontSee('Tác vụ dữ liệu');
```

Prefer:

```php
$response->assertDontSee('data-admin-nav="imports"', false);
```

Use exact text assertions only when the wording itself is the acceptance contract.

## Pest Laravel test context

Every Pest closure that uses Laravel TestCase helpers through `$this` (`get`, `post`, `actingAs`, `artisan`, database assertions, and similar helpers) must declare the TestCase type inside the closure:

```php
it('does something', function (): void {
    /** @var \Tests\TestCase $this */
    $this->get('/')->assertOk();
});
```

This is mandatory for Larastan/PHPStan precision and must not be replaced by static-analysis ignores.
