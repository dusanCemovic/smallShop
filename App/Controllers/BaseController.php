<?php

namespace App\Controllers;
abstract class BaseController
{
    abstract function index();

    /**
     * @param $viewPath view part of MVC
     * @param $params sending parameters to view
     * @return void
     * @throws \Exception
     */
    protected function render($viewPath, $params = [])
    {
        extract($params, EXTR_SKIP); // make sure not to overwrite parameters that are already set

        $viewFile = __DIR__ . '/../Views/' . $viewPath . '.php';
        if (!file_exists($viewFile)) {
            throw new \Exception("View " . $viewFile . " not found");
        }

        include __DIR__ . '/../Views/layout.php';
    }

    protected function redirect($url)
    {
        header('Location: ' . $url);
        exit;
    }

    /** This is used for every exception which is made in App.
     *  We can choose if we want to put in DB or just print and exit.
     * @param $message
     * @return void
     */
    protected function logError($message) {
        // this can be added into log file
        // print $message;
        // exit();
    }
}

