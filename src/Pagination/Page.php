<?php

declare(strict_types=1);

namespace Tsara\Pagination;

use Countable;
use IteratorAggregate;
use Traversable;

final class Page implements Countable, IteratorAggregate
{
    public function __construct(
        public readonly array $items,
        public readonly array $meta,
        public readonly array $response,
    ) {}

    public static function fromResponse(array $response): self
    {
        $payload = is_array($response['data'] ?? null) ? $response['data'] : [];
        $nestedItems = is_array($payload['data'] ?? null) ? $payload['data'] : null;
        $items = $nestedItems ?? (array_is_list($payload) ? $payload : (is_array($payload['items'] ?? null) ? $payload['items'] : []));
        $meta = is_array($response['meta'] ?? null) ? $response['meta'] : (is_array($payload['meta'] ?? null) ? $payload['meta'] : []);
        return new self($items, $meta, $response);
    }

    public function currentPage(): int
    {
        return max(1, (int) ($this->meta['current_page'] ?? $this->meta['page'] ?? 1));
    }

    public function lastPage(): int
    {
        return max($this->currentPage(), (int) ($this->meta['last_page'] ?? $this->meta['total_pages'] ?? $this->meta['pages'] ?? $this->currentPage()));
    }

    public function hasNextPage(): bool
    {
        if (array_key_exists('next_page_url', $this->meta)) return !empty($this->meta['next_page_url']);
        if (array_key_exists('has_more', $this->meta)) return (bool) $this->meta['has_more'];
        return $this->currentPage() < $this->lastPage();
    }

    public function count(): int { return count($this->items); }
    public function getIterator(): Traversable { yield from $this->items; }
}
