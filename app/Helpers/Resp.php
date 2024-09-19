<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class Resp
{
    /**
     * Send success response
     *
     * @param object|array|integer|string|bool $data
     * @param string $message
     * @param integer|null $responsecode
     * @param int $statuscode
     * @return \Illuminate\Http\JsonResponse | \Illuminate\Http\Response
     */
    public static function success($data, $message = "Success", int $statuscode = Response::HTTP_OK, $responsecode = null)
    {
        return response()->json(
            [
                "message" => $message,
                "data" => $data,
                "responsecode" => $responsecode
            ]
        )->setStatusCode($statuscode);
    }

    /**
     * Send server error response
     *
     * @param  $errors
     * @param mixed $data
     * @param string $message
     * @param mixed $responsecode
     * @param int $statuscode
     * @return \Illuminate\Http\JsonResponse
     */
    public static function error($message = "An error occurred while processing the request.", $errors = [],  $data = null,  $statuscode = Response::HTTP_INTERNAL_SERVER_ERROR, $responsecode = null, $log = null)
    {
        $responsesData = [
            "message" => $message,
            "errors" => $errors,
            "responsecode" => $responsecode
        ];

        if ($log instanceof \Throwable) {
            logger()->error($log);
            $responsesData['message'] = $message;
        }

        if ($data) {
            $responsesData['data'] = $data;
        }

        return response()->json($responsesData)->setStatusCode($statuscode);
    }

    public static function created($data, $message = "Enregistrement effectué avec succès.",  $responsecode = null)
    {
        return static::success($data, $message, Response::HTTP_CREATED, $responsecode);
    }

    /**
     * Update a resource
     *
     * @param $data
     * @return \Illuminate\Http\JsonResponse
     */
    public static function updated($data = null, $message = "Mise à jour effectuée avec succès")
    {
        return static::success($data, $message, Response::HTTP_CREATED);
    }

    /**
     * Delete a resource
     *
     * @param  $data
     * @return \Illuminate\Http\JsonResponse
     */
    public static function deleted($data)
    {
        return static::success($data, "Suppression effectué avec succès.", Response::HTTP_OK);
    }

    /**
     * Mark a resource as read
     *
     * @param array $id
     * @return \Illuminate\Http\JsonResponse
     */
    public static function readed($data)
    {
        return static::success($data, "Réponse récupérée avec succès", Response::HTTP_OK);
    }

    /**
     * Choose response based on request method
     *
     * @param $data
     * @return \Illuminate\Http\JsonResponse
     */
    public static function response($data = null)
    {
        $method = request()->getMethod();
        switch ($method) {
            case 'POST':
                return static::created($data,);
            case 'PUT':
            case 'PATCH':
                return static::updated($data,);
            case 'DELETE':
                return static::deleted($data);
            case 'GET':
            default:
                return static::success($data,);
        }
    }

    public static function notFound($data = null, $message = "Aucun résultat trouvé.")
    {
        return static::error($message, [], $data, Response::HTTP_NOT_FOUND);
    }
}
