<?php

namespace App\Controllers;

use App\Models\Article;
use App\Models\Order;
use App\Models\Customer;
use App\Models\Subscription;
use App\Services\SMS\SMSManager;

class OrdersController extends BaseController
{
    protected $model;
    private Customer $customerModel;
    private Subscription $subscriptionModel;
    private Article $articleModel;

    public function __construct()
    {
        $this->model = new Order();
        $this->customerModel = new Customer();
        $this->subscriptionModel = new Subscription();
        $this->articleModel = new Article();
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
            if ($customer_id === null) {
                throw new \Exception("Error while creating customer");
            }

            $result = $this->initOrder($customer_id, explode(',', $_POST['articles']), $args['subscription_id']);

            // todo Attempt to send SMS

            $this->redirect('/?route=orders.index');


        } catch (\Exception $e) {
            // show customer error
            $errors['error'] = $e->getMessage();
            $this->render('orders/checkout',
                ['errors' => $errors, 'old' => $args]);
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
        $cleanedArgs['subscription_id'] = (int)($_POST['subscription_id']) !== 0 ? (int)$_POST['subscription_id'] : null;

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

    /*
     * This method start and control order
     */
    /**
     * @param mixed $customer_id
     * @param array $articles
     * @param mixed $subscription_id
     * @return array
     * @throws \Exception
     */
    private function initOrder(mixed $customer_id, array $articles, mixed $subscription_id)
    {
        $total = 0.0;

        // 1. adding subscription
        if ($subscription_id) {

            $SubscriptionPackage = $this->subscriptionModel->findOne($subscription_id);

            if (!$SubscriptionPackage) {
                throw new \Exception("Subscription package not found.");
            }

            if ($this->customerModel->customerHasSubscription($customer_id)) {
                throw new \Exception("You already have a subscription package.");
            };

            $total += $SubscriptionPackage['price'];
        }

        $cartArticles = [];

        // 2.adding articles
        if (!empty($articles)) {

            // We first put all articles in array and then call one time query to avoid n*2 complexity with queries.
            // This solution is faster even that it is also n*2 complexity, but it is using only side not calling mysql.
            foreach ($articles as $article) {
                $Article = $this->articleModel->findOne($article);
                if (!$Article) {
                    throw new \Exception("Article " . $article . " not found.");
                }
                $cartArticles[] = $Article['id'];
                $total += (float)$Article['price'];
            }

            // get all orders from one customer to check if articles are already added in some of the past order
            $tempOrders = $this->model->findByParam('customer_id', $customer_id, -1);
            foreach ($tempOrders as $tempOrder) {
                foreach (json_decode($tempOrder['articles']) as $tempOrderArticle) {
                    if (in_array($tempOrderArticle, $cartArticles)) {
                        // if we found one, then it is error
                        throw new \Exception("Your already bought this article #" . $tempOrderArticle . " in previous order #" . $tempOrder['order_number']);
                    }
                }
            }
        }

        if ($total === 0.0) {
            throw new \Exception("There is no subscription package or articles included in order.");
        }

        $data =  [
            'customer_id' => $customer_id,
            'total' => $total,
            'articles' => json_encode($cartArticles),
            'subscription_package_id' => $subscription_id,
        ];

        // 3. execute
        return $this->model->createOrder($data);


    }
}

