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

