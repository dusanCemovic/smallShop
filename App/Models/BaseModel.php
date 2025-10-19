<?php

namespace App\Models;
abstract class BaseModel
{
    protected \PDO $pdo;

    public function __construct()
    {
        $this->cfg = DB::getConfig();
        $this->pdo = DB::getConnection();
    }

    // every new class has to define name of table
    abstract function getTable() : string;

    /**
     * Font one based on id.
     * @param $id
     * @return mixed
     */
    public function findOne($id, $table = null)
    {
        if(is_null($table)) {
            $table = $this->getTable();
        }

        try {
            $query = $this->pdo->prepare("SELECT * FROM " . $table . " WHERE id = " . $id . " LIMIT 1");
            $query->execute();
            return $query->fetch();
        } catch (\PDOException $e) {
            throw new \Exception("FindOne made problem, value: >>'" . $id . "'<< // " . $e->getMessage());
        }
    }

    /**
     * Find specific row(s) based on param and its value
     * @param $paramName
     * @param $paramValue
     * @param $limit
     * @param bool $deletedInclude
     * @return mixed
     * @throws \Exception
     */
    public function findByParam($paramName, $paramValue, $limit = 1, $deletedInclude = false)
    {
        if (!$paramName || !$paramValue) {
            throw new \Exception("Wrong parameter name or parameter value ");
        }

        try {
            $limitLabel = $limit !== -1 ? ' LIMIT ' . $limit : '';
            $deletedIncludeLabel = $deletedInclude === false ? ' and deleted_at IS NULL' : ' and deleted_at IS NOT NULL ';

            $queryLabel = "SELECT * FROM " . $this->getTable() . " WHERE " . $paramName . " = '" . $paramValue . "'" . $deletedIncludeLabel . " ORDER BY created_at DESC " . $limitLabel;
            $query = $this->pdo->prepare($queryLabel);

            $query->execute();
        } catch (\PDOException $e) {
            throw new \Exception("FindByParam made problem: " . $queryLabel . " // " . $e->getMessage());
        }

        if($limit === 1) {
            return $query->fetch();
        } else {
            return $query->fetchAll();
        }

    }

    /**
     * Find all rows by date of creating
     * @return array
     */
    public function all()
    {
        $query = $this->pdo->query("SELECT * FROM " . $this->getTable() . " WHERE deleted_at IS NULL ORDER BY created_at DESC");
        return $query->fetchAll();
    }

    /**
     * This is "soft" delete. So we set time of deleting.
     * If it is allowed to be deleted then we can delete it without problem
     * @param int $id
     * @param bool $force in case to force delete without checking
     * @return bool
     * @throws \Exception
     */
    public function delete(int $id, bool $force = false) : bool
    {
        if($id === 0) {
            throw new \Exception("Error id === " . $id . " in table:  " . $this->getTable());
        }

        if ($force || $this->allowDeleting($id)) {
            // total delete
            $query = $this->pdo->prepare("DELETE FROM " . $this->getTable() . " WHERE id = :id");
        } else {
            // soft delete
            $query = $this->pdo->prepare("UPDATE " . $this->getTable() . " SET deleted_at = NOW() WHERE id = :id");
        }

        if (!$query->execute(['id' => $id])) {
            throw new \Exception("Error deleting row id: " . $id . " in table:  " . $this->getTable());
        };

        // each model can create additionally things for DB
        if(!$this->customThingsToBeDone()) {
            throw new \Exception("Error with custom thing which need to be deleted, row id: " . $id . " in table:  " . $this->getTable());
        }

        return true;
    }

    /**
     * This is different for each model. In case that some data is used in other tables, we will probably just soft delete.
     * @param int $id
     * @return bool
     */
    protected function allowDeleting(int $id) : bool
    {
        return true;
    }

    /**
     * This function is used to do additionally queries if it is important like deleting some customer details like phone or something other
     * In practice, probably due the private and terms we have to delete everything about customer except id
     * @return bool
     */
    protected function customThingsToBeDone() : bool {
        return true; // empty by default
    }

}

