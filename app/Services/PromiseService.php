<?php

namespace App\Services;

use Closure;
use Throwable;
use App\Helpers\Resp;
use Illuminate\Support\Facades\DB;

class PromiseService
{
    protected array $callbacks = [];
    protected $catchCallback;
    protected $result;

    protected $i = 0;
    public function then(Closure $callback, int $times = 0, bool $commit = false, int $sleep = 5): self
    {
        $this->callbacks[] = [
            'callback' => $callback,
            'times' => $times,
            'commit' => $commit,
            'sleep' => $sleep
        ];

        return $this;
    }

    public function catch(Closure $catchCallback): self
    {
        $this->catchCallback = $catchCallback;
        return $this;
    }

    public function result()
    {
        try {
            $this->result = null;

            foreach ($this->callbacks as $step) {
                if ($step['commit']) {
                    DB::beginTransaction();
                }

                $this->result = retry(
                    $step['times'],
                    function ($i) use ($step) {
                        $this->i = $i;
                        return call_user_func($step['callback'], $this->result);
                    },
                    $step['sleep'] * 1000,
                    $this->catchCallback
                );

                if ($step['commit']) {
                    DB::commit();
                }
            }

            return $this->result;
        } catch (Throwable $e) {

            if ($step['commit'] ?? false) {
                DB::rollBack();
            }
            throw $e;
        }
    }

    public function json() {}

    public function jsonResponse($message = "Opération effectuée avec succès")
    {
        return Resp::success(
            $this->result(),
            $message
        );
    }
}
