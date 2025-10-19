<?php

namespace App\Models;
class Customer extends BaseModel
{
    function getTable()
    {
        return 'customers';
    }

    /**
     * Create user or return existing user
     *
     * @param $phone
     * @return false|mixed|string
     * @throws \Exception
     */
    public function createIfNotExists($phone)
    {
        $existing = $this->findByParam('phone', $phone);

        if ($existing) {
            return $existing['id'];
        } else {
            $query = $this->pdo->prepare("INSERT INTO " . $this->getTable() . " (phone) VALUES (:phone)");
            $query->execute(['phone' => $phone]);

            return $this->pdo->lastInsertId();
        }

    }

    /**
     * Check if customer has subscription
     * @param $customer_id
     * @return bool
     */
    public function customerHasSubscription($customer_id)
    {
        // check if user already purchase some subscription package
        $subCheck = $this->pdo->prepare("SELECT COUNT(*) FROM orders WHERE customer_id = :cid AND subscription_package_id IS NOT NULL");
        $subCheck->execute(['cid' => $customer_id]);

        return (int)$subCheck->fetchColumn() > 0;
    }

    /**
     * This is custom method for this class. Checking if we can delete customer, or just to put to be softly deleted.
     * @SEE delete method of based class
     * @param $id
     * @return bool
     */
    protected function allowDeleting($id)
    {
        // is there any order that this customer did
        $check = $this->pdo->prepare("SELECT COUNT(*) FROM orders WHERE customer_id = :id");
        $check->execute(['article' => $id]);

        $count = (int)$check->fetchColumn();

        return $count > 0;
    }


}

