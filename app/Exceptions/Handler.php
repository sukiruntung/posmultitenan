<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    public function render($request, Throwable $e)
    {
        // Tangani error 403
        if ($e instanceof HttpException && $e->getStatusCode() === 403) {
            dd('Anda tidak memiliki akses untuk melakukan tindakan ini.');
            return redirect('/admin');
        }

        return parent::render($request, $e);
    }
}
