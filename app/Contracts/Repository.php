<?php

namespace App\Contracts;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface Repository
{
    public function with(...$relationship): self;

    public function all(): Collection;

    public function get(bool $builder = false): mixed;

    public function search(string $query, bool $paginate = false, int $perPage = null): Collection|LengthAwarePaginator;

    public function paginate(int $perPage = null): LengthAwarePaginator;

    public function find(array|string $id, Closure $finder = null): Model|Collection;

    public function create(array $payload, Closure $creator = null): Model;

    public function insert(array $payload, Closure $inserter = null): void;

    public function upsert(array $payload, array $unique = [], array $update = [], array $except = [], Closure $upserter = null): void;

    public function update(Model $model, array $payload, array $except = [], Closure $updater = null): Model;

    public function delete(Model $model, Closure $deleter = null): void;

    public function destroy(array $payload, Closure $destroyer = null): void;

    public function truncate(Closure $truncator = null): void;

    public function query(): mixed;

    public function model(): Model;
}
