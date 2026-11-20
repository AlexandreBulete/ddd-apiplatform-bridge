<?php

declare(strict_types=1);

namespace AlexandreBulete\DddApiPlatformBridge\Tests\State;

use AlexandreBulete\DddApiPlatformBridge\State\Paginator;
use AlexandreBulete\DddFoundation\Infrastructure\InMemory\InMemoryPaginator;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class PaginatorTest extends TestCase
{
    #[Test]
    public function aGeneratorCanBeCountedThenIterated(): void
    {
        $items = (static function (): \Generator {
            yield new \stdClass();
            yield new \stdClass();
        })();

        $paginator = new Paginator($items, 1, 2, 3, 5);

        self::assertCount(2, $paginator);
        self::assertCount(2, iterator_to_array($paginator));
    }

    #[Test]
    public function itAdaptsADomainPage(): void
    {
        $domain = new InMemoryPaginator(
            new \ArrayIterator([(object) ['id' => 1], (object) ['id' => 2], (object) ['id' => 3]]),
            totalItems: 3,
            currentPage: 2,
            itemsPerPage: 2,
        );

        $paginator = Paginator::fromDomain($domain, static fn (\stdClass $model): Resource => new Resource($model->id));

        self::assertSame(2.0, $paginator->getCurrentPage());
        self::assertSame(2.0, $paginator->getItemsPerPage());
        self::assertSame(2.0, $paginator->getLastPage());
        self::assertSame(3.0, $paginator->getTotalItems());
        self::assertCount(1, $paginator);
        self::assertSame([3], array_map(
            static fn (Resource $resource): mixed => $resource->id,
            iterator_to_array($paginator, false),
        ));
    }
}

/**
 * @internal
 */
final readonly class Resource
{
    public function __construct(public mixed $id)
    {
    }
}
