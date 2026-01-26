# DDD API Platform Bridge

API Platform bridge for DDD Foundation. Provides State providers, processors, and pagination utilities.

**Pure PHP** - No framework dependency. Can be used with Symfony or Laravel (future).

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

A generic paginator implementing API Platform's `PaginatorInterface`:

```php
use AlexandreBulete\DddApiPlatformBridge\State\Paginator;
use AlexandreBulete\DddFoundation\Application\Query\QueryBusInterface;

class GetPostsProvider implements ProviderInterface
{
    public function __construct(
        private QueryBusInterface $queryBus,
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $repository = $this->queryBus->ask(new GetPostsQuery(
            page: $context['filters']['page'] ?? 1,
            itemsPerPage: $context['filters']['itemsPerPage'] ?? 30,
        ));

        $paginator = $repository->paginator();

        if (null === $paginator) {
            return iterator_to_array($repository);
        }

        return new Paginator(
            items: $paginator,
            currentPage: $paginator->getCurrentPage(),
            itemsPerPage: $paginator->getItemsPerPage(),
            lastPage: $paginator->getLastPage(),
            totalItems: $paginator->getTotalItems(),
        );
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

