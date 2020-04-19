<?php

namespace Core;

use Core\View;

class MailboxException
{

    private static function getNotFound() {
        return [
            'code' => 404,
            'title' => 'Página no encontrada',
            'message' => 'No se ha encontrado la página, por favor verifique la URL'
        ];
    }

    private static function getInternalServerError() {
        return [
            'code' => 500,
            'title' => 'Error de servidor interno',
            'message' => 'Hubo un error en el servidor, intente nuevamente!'
        ];
    }

    private static function getMethodNotAllowed() {
        return [
            'code' => 403,
            'title' => 'Metodo no permitido',
            'message' => 'El metodo no está permitido para la URL solicitada'
        ];
    }

    private static function getBadRequest() {
        return [
            'code' => 400,
            'title' => 'Petición mala',
            'message' => 'El servidor no puede procesar la petición'
        ];
    }

    public static function showMessage($e, $code, $additional = []) {
        $view = new View();
        $errorInfo = [];
        switch ($code) {
            case 400:
                $errorInfo = self::getBadRequest();
                break;
            case 403:
                $errorInfo = self::getMethodNotAllowed();
                break;
            case 404:
                $errorInfo = self::getNotFound();
                break;
            case 500:
                $errorInfo = self::getInternalServerError();
                break;
            default:
                null;
                break;
        }

        $errorInfo['DEBUG'] = boolval(getenv('DEBUG', false));
        $errorInfo['ERRORS'] = $additional;
        $errorInfo['ERROR_MESSAGE'] = $e->getMessage();
        $errorInfo['ERROR_TRACE'] = $e->getTraceAsString();
        $view->render('errors', $errorInfo);
    }
}
