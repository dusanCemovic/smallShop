<?php

namespace App\Controllers;

use App\Models\Article;
use App\Models\Order;
use App\Models\Customer;
use App\Models\Subscription;
use App\Services\SMS\SMSManager;

class OrdersController extends BaseController
{
    protected Order $model;
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
    public function index() : void
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

    public function checkoutForm() : void
    {
        $this->render('orders/checkout', []);
    }

    public function placeOrder() : void
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
            if ($customer_id === 0) {
                throw new \Exception("Error while creating customer");
            }

            // 1. create order
            $result = $this->initOrder($customer_id, $this->parseArticles($args['articles']), (int) $args['subscription_id']);

            if(!empty($result) && isset($result['id']) && (int) $result['id'] > 0 ) {
                // success
            } else {
                throw new \Exception("Error while creating order");
            }

            // 2. send notification to the customer
            $this->sendMessage($args, $result);

            $this->redirect('/?route=orders.index');


        } catch (\Exception $e) {
            // show customer error
            // I sent all exceptions and error to frontend, but we can do this differently to show only some messages, but other ones to sent to Database for admin check

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
    private function cleanArguments() : array
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
    private function checkForm(array $args) : array
    {
        // this checking are just simple for now. We can extend this to have more details

        $errors = [];

        if ($args['phone'] === '' || !is_numeric($args['phone'])) {
            // we can add better validation for phone
            $errors[] = 'Phone required, and to be valid';
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
     * @param int $customer_id
     * @param array $articles
     * @param int $subscription_id
     * @return array
     * @throws \Exception
     */
    private function initOrder(int $customer_id, array $articles, int $subscription_id): array
    {
        $total = 0.0;

        // 1. adding subscription
        if ($subscription_id !== 0) {

            $SubscriptionPackage = $this->subscriptionModel->findOne($subscription_id);

            if (!$SubscriptionPackage) {
                throw new \Exception("Subscription package not found.");
            }

            if ($this->customerModel->customerHasSubscription($customer_id)) {
                throw new \Exception("You already have a subscription package.");
            };

            $total += (float) $SubscriptionPackage['price'];
        }

        $cartArticles = [];

        // 2.adding articles
        if (!empty($articles)) {

            // We first put all articles in array and then call one time query to avoid n*2 COMPLEXITY with queries.
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
            'subscription_package_id' => $subscription_id === 0 ? null : $subscription_id,
        ];

        // 3. execute
        return $this->model->createOrder($data);


    }

    /**
     * Method which sends notification to customer
     * @param array $args
     * @param array $result
     * @return void
     */
    private function sendMessage(array $args, array $result) : void
    {
        $smsManager = new SMSManager();
        try {
            $smsManager->sendWithFailover($args['phone'], "Your order #" . $result['order_number'] . " was created.");
        } catch (\Exception $e) {
            $this->logError($e->getMessage());
            // if problem happen, we will continue with normal work, but will log this
        }
    }

    /**
     * This is used for parsing articles from cart
     * Here is just simple text area
     * @param mixed $articles
     * @return array
     */
    private function parseArticles(string $articles) : array
    {
        $result = [];

        $array = explode(',', $articles);
        foreach ($array as $item) {
            if(trim($item) === '') {
                continue;
            }
            $result[] = trim($item);
        }

        return $result;
    }
}

