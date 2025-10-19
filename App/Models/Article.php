<?php

namespace App\Models;
class Article extends BaseModel
{

    function getTable()
    {
        return 'articles';
    }

    /**
     * Create new article
     * @param $data
     * @return false|string
     * @throws \Exception
     */
    public function create($data)
    {
        $parseData = $this->parseData($data);

        $query = $this->pdo->prepare("INSERT INTO " . $this->getTable() . " (name, description, price, supplier_email) VALUES (:name, :description, :price, :supplier_email)");
        if (!$query->execute($parseData)) {
            throw new \Exception("Error creating new row in db:  " . json_encode($data));
        };

        return $this->pdo->lastInsertId();
    }

    /**
     * Update article
     * @param $id
     * @param $data
     * @return bool
     * @throws \Exception
     */
    public function update($id, $data)
    {
        $parseData = $this->parseData($data);
        $parseData['id'] = $id;

        $query = $this->pdo->prepare("UPDATE " . $this->getTable() . " SET name=:name, description=:description, price=:price, supplier_email=:supplier_email WHERE id=:id");
        if (!$query->execute($parseData)) {
            throw new \Exception("Error updating article: " . $id . " in db:  " . json_encode($data));
        };

        return true;
    }

    /**
     * This is custom method for this class. Checking if we can delete article, or just to put to be softly deleted.
     * @SEE delete method of based class
     * @param $id
     * @return bool
     */
    protected function allowDeleting($id)
    {
        // if we introduce new table order-article then we may just read that table, not reading json param in table

        $check = $this->pdo->prepare("SELECT COUNT(*) FROM orders WHERE JSON_CONTAINS(articles, :article)");
        $check->execute(['article' => json_encode((int)$id)]);

        $count = (int)$check->fetchColumn();

        return $count === 0;
    }

    /**
     * Parse data for creating or updating
     * @param $data
     * @return array
     */
    private function parseData($data)
    {
        return [
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'price' => $data['price'],
            'supplier_email' => $data['supplier_email'] ?? null
        ];
    }
}

