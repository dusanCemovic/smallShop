<?php

namespace App\Models;
class Order extends BaseModel
{
    function getTable()
    {
        return "orders";
    }

    /**
     * Create order but check everything before
     * @param $customer_id
     * @param $articles
     * @param $subscription_package_id
     * @return array
     * @throws \Exception
     */
    public function createOrder($customer_id, $articles, $subscription_package_id = null)
    {

        // in case that we are using some table like order_articles and we have more db execution, we can use somethine like:
        // $this->pdo->beginTransaction();
        // and then rollback $this->pdo->rollBack();
        // but here we are doing precheck

        try {
            $total = 0.0;
            $orderNumber = 'ORD-' . time() . '-' . rand(100, 999);

            // 1. adding subscription
            if ($subscription_package_id) {
                // this can be changed to load model of subscription packages
                $subscription_package = $this->pdo->prepare("SELECT price FROM subscription_packages WHERE id = :id AND deleted_at IS NULL");
                $subscription_package->execute(
                    ['id' => $subscription_package_id]
                );
                $subscriptionPackage = $subscription_package->fetch();

                if (!$subscriptionPackage) {
                    throw new \Exception("Subscription package not found.");
                }

                // check if user already purchase some subscription package
                $subCheck = $this->pdo->prepare("SELECT COUNT(*) FROM " . $this->getTable() . " WHERE customer_id = :cid AND subscription_package_id IS NOT NULL");
                $subCheck->execute(['cid' => $customer_id]);

                $subCount = (int)$subCheck->fetchColumn();
                if ($subCount > 0) {
                    throw new \Exception("Customer already has a subscription package.");
                }

                $total += (float)$subscriptionPackage['price'];
            }

            // 2.adding articles
            $tempArticles = [];
            if(!empty($articles)) {
                // We first put all articles in array and then call one time query to avoid n*2 complexity with query.
                // This second solution is faster even that it is also n*2 complexity.
                foreach ($articles as $article) {
                    $Article = $this->findOne($article, 'articles');
                    if(!$Article) {
                        throw new \Exception("Article " . $article . " not found.");
                    }
                    $tempArticles[] = $Article['id'];
                    $total += (float) $Article['price'];
                }

                // get all orders from one customer to check if articles are already added in some of the past order
                $tempOrders = $this->findByParam('customer_id', $customer_id, -1);
                foreach ($tempOrders as $tempOrder) {
                    foreach (json_decode($tempOrder['articles']) as $tempOrderArticle) {
                        if (in_array($tempOrderArticle, $tempArticles)) {
                            throw new \Exception("Customer already bought this article in previous order #" . $tempOrder['order_number']);
                        }
                    }
                }
            }

            if($total === 0.0) {
                throw new \Exception("There is no subscription package or articles included in order.");
            }

            $jsonArticles = json_encode($tempArticles);

            $query = $this->pdo->prepare("INSERT INTO " . $this->getTable() . " (order_number, customer_id, status, total_price, articles, subscription_package_id) 
            VALUES (:order_number, :customer_id, :status, :total_price, :articles, :subscription_package_id)");

            $dataQuery = [
                'order_number' => $orderNumber,
                'customer_id' => $customer_id,
                'status' => "pending",
                'total_price'  => $total,
                'articles' => $jsonArticles,
                'subscription_package_id' => $subscription_package_id
            ];

            // 3. execution
            $query->execute($dataQuery);

            $orderId = $this->pdo->lastInsertId();

            return ['id' => $orderId, 'order_number' => $orderNumber];

        } catch (\Exception $e) {
            echo $e->getMessage();
            exit();
        }
    }

    /**
     * Custom listing all orders
     * @return array
     */
    public function listAll()
    {
        $query = $this->pdo->prepare(
            "SELECT orders.*, customers.phone as phone FROM " . $this->getTable() . " 
            JOIN customers 
            ON customers.id = ".$this->getTable() .".customer_id 
            ORDER BY orders.created_at DESC");

        $query->execute();

        return $query->fetchAll();
    }
}

