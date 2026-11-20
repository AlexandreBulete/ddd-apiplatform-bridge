# DDD API Platform Bridge

API Platform bridge for DDD Foundation. Provides State providers, processors, and pagination utilities.

**Pure PHP** - No framework dependency. Can be used with Symfony or Laravel (future).

Requires PHP 8.4+ and `api-platform/state` 4.2 or 5.x.

## Installation

```bash
composer require alexandrebulete/ddd-apiplatform-bridge
```

For Symfony integration, also install:
```bash
composer require alexandrebulete/ddd-apiplatform-bundle
```

## Structure

```
src/
└── State/
    └── Paginator.php
```

## Usage

### Paginator

Implements API Platform's `PaginatorInterface`. The page is materialised once,
so `count()` and iteration never exhaust a generator.

`Paginator::fromDomain()` adapts a page returned by a Domain repository
(`DddFoundation\Domain\Repository\PaginatorInterface`) and maps each model to
its API resource — the API never exposes Domain objects:

```php
use AlexandreBulete\DddApiPlatformBridge\State\Paginator;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;

/** @implements ProviderInterface<PostResource> */
final readonly class PostCollectionProvider implements ProviderInterface
{
    public function __construct(private QueryBusInterface $queryBus) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): Paginator
    {
        $page = $this->queryBus->ask(new ListPublishedPosts(page: (int) ($context['filters']['page'] ?? 1)));

        return Paginator::fromDomain($page, PostResource::fromModel(...));
    }
}
```

## Usage with API Platform Resources

```php
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;

#[ApiResource(
    operations: [
        new GetCollection(
            provider: GetPostsProvider::class,
        ),
    ],
)]
class PostResource
{
    public function __construct(
        public string $id,
        public string $title,
        public string $content,
    ) {}
}
```

