<?php

namespace App\Models;
class Order extends BaseModel
{
    function getTable() : string
    {
        return "orders";
    }

    /**
     * Creating order after everything is checked
     * @param array $params
     * @return array
     */
    public function createOrder(array $params) : array {

        $orderNumber = 'ORD-' . time() . '-' . rand(100, 999);

        $query = $this->pdo->prepare("INSERT INTO " . $this->getTable() . " (order_number, customer_id, status, total_price, articles, subscription_package_id) 
        VALUES (:order_number, :customer_id, :status, :total_price, :articles, :subscription_package_id)");

        $dataQuery = [
            'order_number' => $orderNumber,
            'customer_id' => $params['customer_id'],
            'status' => "completed",
            'total_price'  => $params['total'],
            'articles' => $params['articles'],
            'subscription_package_id' => $params['subscription_package_id']
        ];

        $query->execute($dataQuery);

        $orderId = $this->pdo->lastInsertId();

        return ['id' => $orderId, 'order_number' => $orderNumber];
    }

    /**
     * Custom listing all orders
     * @return array
     */
    public function listAll() : array
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

