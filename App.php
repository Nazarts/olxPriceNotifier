<?php

require_once 'Controllers/AppController.php';

use Controllers\AppController;

error_reporting(E_ERROR | E_PARSE);

class App
{
    static public function initEnvVariables(string|null $env_file=null): bool
    {
        $env_file = $env_file??'.env';
        $env_vars = parse_ini_file($env_file);
        if ($env_vars) {
            foreach ($env_vars as $env_var=>$value) {
                $_ENV[$env_var] = $value;
            }
            return true;
        }
        return false;
    }

    static public function run(): void
    {
        self::initEnvVariables();
        echo self::handleRequest();
    }

    static private function handleRequest(): bool|string
    {
        header('Content-Type: application/json');
        $uri = $_SERVER['REQUEST_URI'];
        $request_method = $_SERVER['REQUEST_METHOD'];
        if ($request_method === 'POST') {
            if ($uri === '/subscribe') {
                return AppController::subscribe();
            } elseif ($uri === '/verify') {
                return AppController::verifyCode();
            }
        } elseif ($request_method === 'GET') {
            if ($uri === '/run_parser') {
                AppController::runParser();
                return true;
            }
        }

        else {
            http_response_code(404);
            return json_encode(['status' => false, 'message' => 'page not found'.$uri]);
        }
    }

}