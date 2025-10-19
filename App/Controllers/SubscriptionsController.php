<?php

namespace App\Controllers;

use App\Models\Subscription;

class SubscriptionsController extends BaseController
{
    protected $model; // subscription model

    public function __construct()
    {
        // load model for subscription
        $this->model = new Subscription();
    }

    /**
     * Load all subscriptions
     * @return void
     * @throws \Exception
     */
    public function index()
    {
        try {
            $packages = $this->model->all();
            $this->render('subscriptions/list', ['packages' => $packages]);
        } catch (\Exception $e) {
            // we can put those things in logs and then redirect
            $this->logError($e->getMessage());
            $this->redirect('/?route=articles.index'); // homepage
        }
    }

    /**
     * @return void
     */
    public function delete()
    {
        try {
            $id = (int)$_POST['id'] ?? null;
            $this->model->delete($id);
            $this->redirect('/?route=subscriptions.index');
        } catch (\Exception $e) {
            $this->logError($e->getMessage());
            $this->redirect('/?route=subscriptions.index');
        }
    }


    /**
     * We can only create subscriptions, not edit
     * @return void
     */
    public function form()
    {
        try {
            $this->render('subscriptions/form', []);
        } catch (\Exception $e) {
            $this->logError($e->getMessage());
            $this->redirect('/?route=subscriptions.index');
        }
    }

    /**
     * Create new Subscription submit
     */
    public function create()
    {
        $args = $this->cleanArguments();
        $errors = $this->checkForm($args);

        try {
            if (empty($errors)) {
                $this->model->create($args);
                $this->redirect('/?route=subscriptions.index');
            } else {
                $this->render('subscriptions/form',
                    ['errors' => $errors, 'old' => $args]);
                return;
            }
        } catch (\Exception $e) {
            $this->logError($e->getMessage());
            $this->redirect('/?route=subscriptions.index');
        }
    }

    /**
     * This checking are just simple for now. We can extend this to have more details
     * New frameworks have more sofisticied validation
     * @return array
     */
    private function cleanArguments()
    {
        $cleanedArgs['name'] = trim($_POST['name'] ?? '');
        $cleanedArgs['price'] = $_POST['price'] ?? null;
        $cleanedArgs['includes_physical_magazine'] = $_POST['includes_physical_magazine'] ?? null;
        $cleanedArgs['description'] = $_POST['description'] ?? null;

        return $cleanedArgs;
    }

    /**
     * Return errors to view or allow to proceed
     * @param $args
     * @return array
     */
    private function checkForm($args)
    {
        // this checking are just simple for now. We can extend this to have more details

        $errors = [];
        if ($args['name'] === '') {
            $errors[] = 'Name required';
        }

        if (!is_numeric($args['price'])) {
            $errors[] = 'Price must be numeric';
        }

        return $errors;
    }
}

