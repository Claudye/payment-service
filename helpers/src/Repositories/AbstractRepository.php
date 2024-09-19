<?php

namespace Helpers\Repositories;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

abstract class AbstractRepository
{

    protected $modelName;
    protected Model $model;
    public function __construct()
    {
        $this->model = app($this->modelName);
    }
    public function transform($result)
    {
        if (is_callable($result)) {
            $result = $result();
        }
        if ($result instanceof Model) {
            return $this->data($result);
        }

        if ($result instanceof \Illuminate\Database\Eloquent\Collection) {
            return $result->map(function ($item) {
                return $this->data($item);
            });
        }

        if ($result instanceof LengthAwarePaginator) {
            return $result->map(function ($item) {
                return $this->data($item);
            });
        }

        return $result;
    }

    public function paginate($perPage = 10)
    {
        return $this->transform(
            $this->model->paginate($perPage)
        );
    }

    public function take($take = 10)
    {
        return $this->transform(
            $this->model->take($take)->get()
        );
    }

    abstract protected function data($model, bool $private = false): array;

    public function with(array $options)
    {
        foreach ($options as $method => $args) {
            $this->model->$method(...$args);
        }
    }

    public function popular($perPage = 10)
    {
        return $this->model
            ->paginate($perPage)
            ->map(function ($item) {
                return $this->data($item);
            });
    }

    public function getNewInstance(array $select = ["*"])
    {
        return app($this->modelName)->select($select);
    }
}
