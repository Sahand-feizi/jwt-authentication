<?php

namespace App\Exceptions;

use App\Traits\ApiResponses;
use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\UnauthorizedException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends Exception
{
    use ApiResponses;

    protected array $handlers = [
        ValidationException::class => 'handleValidationException',
        NotFoundHttpException::class => 'handleNotFoundHttpException',
        AccessDeniedHttpException::class => 'handleAccessDeniedHttpException',
        AuthenticationException::class => 'handleAuthenticationException'
    ];

    public function handleValidationException(ValidationException $e)
    {
        $errors = [];
        foreach ($e->errors() as $key => $value) {
            foreach ($value as $message) {
                $errors[] = [
                    'status' => 422,
                    'message' => $message,
                    'source' => $key
                ];
            }
        }

        return $this->error($errors, 422);
    }

    public function handleNotFoundHttpException(NotFoundHttpException $e)
    {
        return $this->error([
            'type' => 'NotFoundHttpException',
            'status' => 404,
            'message' => 'The resource cannot be found',
        ], 404);
    }

    public function handleAccessDeniedHttpException(AccessDeniedHttpException $e)
    {
        return $this->error([
            'type' => 'AccessDeniedHttpException',
            'status' => 401,
            'message' => 'You are not authorized'
        ], 401);
    }

    public function handleAuthenticationException(AuthenticationException $e)
    {
        return $this->error([
            'type' => 'AuthenticationException',
            'status' => 401,
            'message' => 'Your are not authenticated'
        ], 401);
    }

    public function render(Throwable $e)
    {
        $className = get_class($e);

        $index = strrpos($className, '\\');

        if (array_key_exists($className, $this->handlers)) {
            $method = $this->handlers[$className];

            return $this->$method($e);
        }

        return $this->error([
            [
                'type' => substr($className, $index + 1),
                'status' => 0,
                'message' => $e->getMessage()
            ]
        ], 200);
    }
}
