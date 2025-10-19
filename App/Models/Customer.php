<?php

namespace App\Models;
class Customer extends BaseModel
{
    function getTable() : string
    {
        return 'customers';
    }

    /**
     * Create user or return existing user
     *
     * @param string $phone
     * @return int
     * @throws \Exception
     */
    public function createIfNotExists(string $phone) : int
    {
        $existing = $this->findByParam('phone', $phone);

        if ($existing) {
            return (int) $existing['id'];
        } else {
            $query = $this->pdo->prepare("INSERT INTO " . $this->getTable() . " (phone) VALUES (:phone)");
            $query->execute(['phone' => $phone]);

            return (int) $this->pdo->lastInsertId();
        }

    }

    /**
     * Check if customer has subscription
     * @param int $customer_id
     * @return bool
     */
    public function customerHasSubscription(int $customer_id) : bool
    {
        // check if user already purchase some subscription package
        $subCheck = $this->pdo->prepare("SELECT COUNT(*) FROM orders WHERE customer_id = :cid AND subscription_package_id IS NOT NULL");
        $subCheck->execute(['cid' => $customer_id]);

        return (int)$subCheck->fetchColumn() > 0;
    }

    /**
     * This is custom method for this class. Checking if we can delete customer, or just to put to be softly deleted.
     * @SEE delete method of based class
     * @param int $id
     * @return bool
     */
    protected function allowDeleting(int $id) : bool
    {
        // is there any order that this customer did
        $check = $this->pdo->prepare("SELECT COUNT(*) FROM orders WHERE customer_id = :id");
        $check->execute(['article' => $id]);

        $count = (int)$check->fetchColumn();

        return $count > 0;
    }


}

