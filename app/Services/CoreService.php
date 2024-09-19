<?php

namespace App\Services;

use Throwable;
use App\Traits\CanTrowException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

abstract class CoreService
{
    use CanTrowException;

    /**
     * Model | Builder
     * @var Model | Builder
     */
    private mixed $model;
    /**
     * Summary of collection
     * @var  Collection
     */
    protected $collection;

    protected $cached = false;

    protected $with = [];
    protected NotificationService $notificationService;

    public function __construct()
    {
        $this->model = app($this->getModelClass());
        $this->notificationService = app(NotificationService::class);
        $this->collection = collect([]);
    }

    abstract protected function getModelClass();

    /**
     * Summary of model
     * @return Model | Builder
     */
    protected function model()
    {
        return clone $this->model->with($this->with);
    }

    /**
     * Set default Currency
     */


    /**
     * @param array|null $exclude
     * @return void
     */
    public function dropAll(?array $exclude = []): void
    {
        /** @var Model $models */

        $models = $this->model();

        $models = $models->when(
            data_get($exclude, 'column') && data_get($exclude, 'value'),
            function (Builder $query) use ($exclude) {
                $query->where(data_get($exclude, 'column'), '!=', data_get($exclude, 'value'));
            }
        )->get();

        foreach ($models as $model) {

            try {
                $model->delete();
            } catch (Throwable $e) {
                $this->throwException($e);
            }
        }
    }

    public function all(array $filters, bool $reload = false)
    {
        if ($reload) {
            $this->reload();
        }

        if (!$this->cached) {
            $this->collection = $this->model()
                ->filter($filters)->get();
            $this->cached = true;
        }
        return $this->collection;
    }

    public function paginate(array $filters)
    {
        return $this->model()
            ->filter($filters)
            ->paginate();
    }
    public function reload()
    {
        $this->cached = false;
        return $this;
    }
    public function sum($filters, callable $callback)
    {
        return $this->all($filters)->sum($callback);
    }
    /**
     * @return void
     */
    public function restoreAll(): void
    {
        /** @var Model $models */
        $models = $this->model();

        foreach ($models->withTrashed()->whereNotNull('deleted_at')->get() as $model) {

            try {
                $model->update([
                    'deleted_at' => null
                ]);
            } catch (Throwable $e) {

                $this->throwException($e);
            }
        }
    }
    public function total(array $filters): int
    {
        return $this->all($filters)->count();
    }
    /**
     * @param string $name
     * @return void
     */
    public function truncate(string $name = ''): void
    {
        DB::statement("SET foreign_key_checks = 0");
        DB::table($name ?: $this->model()->getTable())->truncate();
        DB::statement("SET foreign_key_checks = 1");
    }

    /**
     * @param array $ids
     * @return array|int[]|void
     */
    public function destroy(array $ids)
    {
        foreach ($this->model()->whereIn('id', $ids)->get() as $model) {
            try {
                $model->delete();
            } catch (Throwable $e) {
                $this->throwException($e);
            }
        }
    }

    /**
     * @param array $ids
     * @return array|int|int[]|void
     */
    public function delete(array $ids)
    {
        $this->destroy($ids);
    }

    public function relations(array $relations)
    {
        $this->with = $relations;
        return $this;
    }
    /**
     * @param array $ids
     * @param string $column
     * @param array<string>|null $when
     */
    public function remove(array $ids, string $column = 'id', ?array $when = ['column' => null, 'value' => null])
    {
        $models = $this->model()
            ->whereIn($column, $ids)
            ->when(
                data_get($when, 'column'),
                fn($q, $column) => $q->where($column, data_get($when, 'value'))
            )
            ->get();

        foreach ($models as $model) {
            try {
                $model->delete();
            } catch (Throwable $e) {
                $this->throwException($e);
            }
        }
    }

    public function latest(array $filters)
    {
        return $this->model()->filter($filters)->latest();
    }

    public function firstWhereAny(array $columns, $value)
    {
        return $this->model()->whereAny($columns, $value)->first();
    }
    /**
     * @param array $attributes
     * @return mixed
     */
    public function create(array $attributes)
    {
        try {
            return $this->model()->create($attributes);
        } catch (Throwable $e) {
            $this->throwException($e);
        }
    }

    /**
     * @param int|string $id
     * @param array $attributes
     * @return Model|null
     */
    public function update($id, array $attributes): ?Model
    {
        if (empty($attributes)) {
            $this->throwValidationErros([
                "message" => "No attributes provided",
            ]);
        }
        try {

            $model = is_a($id, $this->getModelClass()) ? $id : $this->model()->find($id);
            if ($model) {
                $model->update($attributes);
                return $model;
            }
        } catch (Throwable $e) {
            $this->throwException($e);
        }

        return null;
    }

    /**
     * @param int|string $id
     * @return Model|null
     */
    public function find($id): ?Model
    {
        try {
            return $this->model()->find($id);
        } catch (Throwable $e) {
            $this->throwException($e);
        }

        return null;
    }

    /**
     * @param array $conditions
     * @return Model|null
     */
    public function findBy(array $conditions): ?Model
    {
        try {
            return $this->model()->where($conditions)->first();
        } catch (Throwable $e) {
            $this->throwException($e);
        }

        return null;
    }
    public function findOne(string $column, $value)
    {
        try {
            return $this->model()->where($column, $value)->first();
        } catch (Throwable $e) {
            $this->throwException($e);
        }

        return null;
    }
    /**
     * @param array $conditions
     * @return Collection
     */
    public function getAll(array $conditions = []): Collection
    {
        try {
            return $this->model()->where($conditions)->get();
        } catch (Throwable $e) {
            $this->throwException($e);
        }

        return collect();
    }

    /**
     * @param int|string $id
     * @return bool
     */
    public function exists($id): bool
    {
        try {
            return $this->model()->where('id', $id)->exists();
        } catch (Throwable $e) {
            $this->throwException($e);
        }

        return false;
    }

    /**
     * @param int|string $id
     * @return bool
     */
    public function forceDelete($id): bool
    {
        try {
            $model = $this->model()->withTrashed()->find($id);
            if ($model) {
                return $model->forceDelete();
            }
        } catch (Throwable $e) {
            $this->throwException($e);
        }

        return false;
    }

    /**
     * @param array $attributes
     * @param array $values
     */
    public function updateOrCreate(array $attributes, array $values = []): Model
    {
        try {
            return $this->model()->updateOrCreate($attributes, $values);
        } catch (Throwable $e) {
            $this->throwException($e);
        }
    }

    public function generateUniqueCode($column, $length = 6, $type = 'alphanum', $maxAttempts = 10): string
    {
        $code = '';
        $attempts = 0;

        switch ($type) {
            case 'alphanum':
                $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
                break;
            case 'alpha':
                $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
                break;
            case 'number':
                $characters = '0123456789';
                break;
            default:
                $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
                break;
        }

        do {
            // Génération du code unique
            $code = substr(str_shuffle(str_repeat($characters, $length)), 0, $length);

            // Vérifier l'existence du code et limiter le nombre d'essais
            if (++$attempts >= $maxAttempts) {
                $this->throwException("Impossible de générer un code unique après $maxAttempts tentatives.");
            }
        } while ($this->model()->where($column, $code)->exists());

        return $code;
    }
}
