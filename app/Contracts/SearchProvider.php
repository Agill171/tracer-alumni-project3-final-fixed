<?php

namespace App\Contracts;

interface SearchProvider
{
    public function name(): string;

    /**
     * @return array<int, array{
     *     rank:int,
     *     title:string|null,
     *     url:string,
     *     snippet:string|null
     * }>
     */
    public function search(
        string $query,
        int $count = 5
    ): array;
}