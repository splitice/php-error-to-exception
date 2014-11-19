<?php
namespace Splitice;

class ErrorToException {
    /**
     * E_ERROR | E_USER_ERROR | E_USER_WARNING | E_COMPILE_ERROR | E_CORE_ERROR | E_RECOVERABLE_ERROR
     */
    const DEFAULT_ERRORS = 4945;

    public static function handle($functor, $errors = -1, $exception = '\\ErrorException'){
        if($errors == -1){
            $errors = self::DEFAULT_ERRORS;
        }

        $previous_handler = NULL;
        $error_handler = function ($errno, $errstr, $errfile, $errline ) use($exception, $errors, &$previous_handler) {
            if($errno & $errors) {
                throw new $exception($errstr, 0, $errno, $errfile, $errline);
            }

            if($previous_handler == NULL){
                return false;
            }
            return $previous_handler($errno, $errstr, $errfile, $errline);
        };

        $previous_handler = set_error_handler($error_handler);

        $functor();

        restore_error_handler();
    }
}