<?php

namespace App\Traits;

use Illuminate\Validation\ValidationException;
use Throwable;
use App\Exceptions\GlobalException;
use Spatie\Permission\Exceptions\RoleDoesNotExist;

trait CanTrowException
{
    public function throwif(bool | callable $conditions, $message, $code = 500,  $notify = false)
    {
        if (is_callable($conditions)) {
            $conditions = $conditions($this);
        }

        if ($conditions) {
            $this->throwException($message, $code,  $notify);
        }
    }

    /**
     * Throw exception
     * @param mixed $error
     * @param mixed $code
     * @param bool $log
     * @param bool $notify
     * @throws \Throwable
     * @return never
     */
    public function throwException($error, $code = 500, bool $log = true, bool $notify = false)
    {
        if ($code > 499) {
            logger()->error($error);
        }
        if ($error instanceof Throwable) {
            throw $error;
        }

        throw new GlobalException($error);
    }

    public function throwOriginal(Throwable $exception,)
    {
        $this->throwif(
            $exception instanceof RoleDoesNotExist,
            $exception->getMessage(),
            $exception->getCode()
        );

        $this->throwException($exception->getMessage(), $exception->getCode(), true);
    }

    public function throwValidationErros(array $messages)
    {
        $this->throwException(ValidationException::withMessages($messages));
    }
}
