<?php

namespace App\Repositories;

use App\Repositories\Contracts\BaseRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

abstract class BaseRepository implements BaseRepositoryInterface
{
    public function __construct(protected readonly Model $model) {}

    public function findById(int $id): ?Model
    {
        return $this->model->newQuery()->find($id);
    }

    public function findByIdOrFail(int $id): Model
    {
        return $this->model->newQuery()->findOrFail($id);
    }

    public function findAll(array $filters = []): Collection|LengthAwarePaginator
    {
        $query = $this->model->newQuery();

        if (isset($filters['per_page'])) {
            return $query->paginate((int) $filters['per_page']);
        }

        return $query->get();
    }

    public function create(array $data): Model
    {
        return $this->model->newQuery()->create($data);
    }

    public function update(int $id, array $data): Model
    {
        $record = $this->findByIdOrFail($id);
        $record->update($data);

        return $record->fresh();
    }

    public function delete(int $id): bool
    {
        return (bool) $this->findByIdOrFail($id)->delete();
    }
}
