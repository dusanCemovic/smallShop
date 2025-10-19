<?php

namespace App\Models;
class Subscription extends BaseModel
{

    function getTable() : string
    {
        return 'subscription_packages';
    }

    /**
     * Creating new Subscription
     * @param array $data
     * @return int
     */
    public function create(array $data) : int
    {
        $query = $this->pdo->prepare("INSERT INTO " . $this->getTable() . " (name, description, price, includes_physical_magazine) VALUES (:name, :description, :price, :includes_physical_magazine)");
        $query->execute([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'price' => $data['price'],
            'includes_physical_magazine' => !empty($data['includes_physical_magazine']) ? 1 : 0
        ]);
        return $this->pdo->lastInsertId();
    }

    /**
     * This is custom method for this class. Checking if we can delete subscription, or just to put to be softly deleted.
     * @SEE delete method of based class
     * @param int $id
     * @return bool
     */
    protected function allowDeleting(int $id) : bool
    {
        // if we introduce new table order-article then we may just read that table, not reading json param in table

        $check = $this->pdo->prepare("SELECT COUNT(*) FROM orders WHERE subscription_package_id = :id");
        $check->execute(['id' => $id]);

        $count = (int)$check->fetchColumn();

        return $count === 0;
    }
}

