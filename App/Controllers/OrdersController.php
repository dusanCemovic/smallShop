<?php

namespace App\Controllers;

use App\Models\Order;
use App\Models\Customer;
use App\Services\SMS\SMSManager;

class OrdersController extends BaseController
{
    protected $model;
    protected $customerModel;

    public function __construct()
    {
        $this->model = new Order();
        $this->customerModel = new Customer();
    }

    /**
     * List all orders
     * @return void
     * @throws \Exception
     */
    public function index()
    {
        try {
            $orders = $this->model->listAll();
            $this->render('orders/list', ['orders' => $orders]);
        } catch (\Exception $e) {
            // we can put those things in logs and then redirect
            $this->logError($e->getMessage());
            $this->redirect('/?route=articles.index'); // homepage
        }
    }

    public function checkoutForm()
    {
        $this->render('orders/checkout', []);
    }

    public function placeOrder()
    {
        $args = $this->cleanArguments();
        $errors = $this->checkForm($args);

        try {
            if (!empty($errors)) { // return to view
                $this->render('orders/checkout',
                    ['errors' => $errors, 'old' => $args]);
                return;
            }

            $customer_id = $this->customerModel->createIfNotExists($args['phone']);
            $result = $this->model->createOrder($customer_id, explode(',', $_POST ['articles']), $args['subscription_id']);

            // todo Attempt to send SMS


            $this->redirect('/?route=orders.index');


        } catch (\Exception $e) {

        }
    }


    /**
     * This checking are just simple for now. We can extend this to have more details
     * New frameworks have more sofisticied validation
     * @return array
     */
    private function cleanArguments()
    {

        $cleanedArgs['phone'] = trim($_POST['phone'] ?? '');
        $cleanedArgs['articles'] = trim($_POST['articles'] ?? '');
        $cleanedArgs['subscription_id'] = (int) ($_POST['subscription_id']) !== 0 ? (int) $_POST['subscription_id'] : null;

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

        if ($args['phone'] === '') {
            $errors[] = 'Phone required';
        }
        if (empty($args['articles']) && !$args['subscription_id']) {
            $errors[] = 'Select at least an article or a subscription';
        }

        return $errors;
    }
}

