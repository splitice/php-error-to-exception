<?php
namespace Splitice;

/**
 * Class ErrorToException for the translation of legacy errors to Exceptions
 * @package Splitice
 */
class ErrorToException {
    /**
     * E_USER_WARNING | E_USER_ERROR | E_ERROR | E_COMPILE_ERROR | E_CORE_ERROR | E_RECOVERABLE_ERROR
     */
    const DEFAULT_ERRORS = 4945;

    /**
     * Call $functor, if the output matches $validation_function or if $validation_function is NULL
     * then throw a $exception if an error matching $errors is found
     *
     * @param callable|$functor
     * @param callable|null $validation_function
     * @param int $errors
     * @param string $exception
     */
    public static function handle($functor, $validation_function = null, $errors = -1, $exception = '\\ErrorException'){
        if($errors == -1){
            $errors = self::DEFAULT_ERRORS;
        }

        $ex = $previous_handler = NULL;
        $error_handler = function ($errno, $errstr, $errfile, $errline )
                            use($exception, $errors, &$previous_handler, &$ex, $validation_function) {
            if($errno & $errors) {
                $ex = new $exception($errstr, 0, $errno, $errfile, $errline);

                //Is error, we need to do something now
                if($errno & (E_USER_ERROR | E_ERROR | E_COMPILE_ERROR | E_CORE_ERROR | E_RECOVERABLE_ERROR)){
                    if($validation_function == NULL || $validation_function($result)){
                        throw $ex;
                    }
                }

                return true;
            }


            if($previous_handler == NULL){
                return false;
            }
            return $previous_handler($errno, $errstr, $errfile, $errline);
        };

        $previous_handler = set_error_handler($error_handler);

        $result = $functor();

        if($ex != null){
            if($validation_function == NULL || $validation_function($result)){
                throw $ex;
            }
        }

        restore_error_handler();
    }
}