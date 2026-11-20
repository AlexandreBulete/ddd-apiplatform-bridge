<?php

declare(strict_types=1);

namespace AlexandreBulete\DddApiPlatformBridge\State;

use AlexandreBulete\DddFoundation\Domain\Repository\PaginatorInterface as DomainPaginator;
use ApiPlatform\State\Pagination\PaginatorInterface;

/**
 * API Platform view of one page of results.
 *
 * The page is materialised once: API Platform calls count() and iterates
 * the paginator, and a generator would be exhausted by the first call.
 *
 * @template T of object
 *
 * @implements PaginatorInterface<T>
 * @implements \IteratorAggregate<mixed, T>
 */
final readonly class Paginator implements PaginatorInterface, \IteratorAggregate
{
    /** @var list<T> */
    private array $items;

    /**
     * @param iterable<T> $items
     */
    public function __construct(
        iterable $items,
        private float $currentPage,
        private float $itemsPerPage,
        private float $lastPage,
        private float $totalItems,
    ) {
        $this->items = \is_array($items) ? array_values($items) : iterator_to_array($items, false);
    }

    /**
     * Adapts a page returned by a Domain repository, mapping each model to
     * the API resource that represents it: the API never exposes the Domain.
     *
     * @template M of object
     * @template R of object
     *
     * @param DomainPaginator<M> $page
     * @param \Closure(M): R     $toResource
     *
     * @return self<R>
     */
    public static function fromDomain(DomainPaginator $page, \Closure $toResource): self
    {
        $resources = [];
        foreach ($page as $model) {
            $resources[] = $toResource($model);
        }

        return new self(
            $resources,
            $page->getCurrentPage(),
            $page->getItemsPerPage(),
            $page->getLastPage(),
            $page->getTotalItems(),
        );
    }

    public function getCurrentPage(): float
    {
        return $this->currentPage;
    }

    public function getItemsPerPage(): float
    {
        return $this->itemsPerPage;
    }

    public function getLastPage(): float
    {
        return $this->lastPage;
    }

    public function getTotalItems(): float
    {
        return $this->totalItems;
    }

    public function count(): int
    {
        return \count($this->items);
    }

    /**
     * @return \ArrayIterator<int, T>
     */
    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->items);
    }
}
