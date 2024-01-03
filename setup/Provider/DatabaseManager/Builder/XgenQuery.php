<?php

namespace Setup\Provider\DatabaseManager\Builder;

use PDO;
use Setup\Provider\DatabaseManager\DatabaseManager;

class XgenQuery
{

    private DatabaseManager $connection;
    public function __construct($con)
    {
     $this->connection = $con;
    }

    // select all
    public  function selectAll(string $table): false|array|string
    {

            $conn = $this->connection->getConnection();
            try {
                $conn = $this->connection->getConnection();
                $conn->setAttribute(PDO::ATTR_PERSISTENT, PDO::ERRMODE_EXCEPTION);
                $stmt = $conn->prepare("SELECT * FROM ".$table);
                $stmt->execute();

                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (\PDOException $exception) {
                return "query error :" . $exception->getMessage();
            } finally {
                $this->connection->releaseConnection($conn);
            }
    }
    // select by id
    protected function selectById(string $table, int $id): false|array|string
    {
        try {
            $conn = $this->connection;
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $stmt = $conn->prepare("SELECT * FROM ".$table." where id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $exception) {
            return "query error :" . $exception->getMessage();
        }
    }

    // insert
    protected function insertdata(string $table, array $data): void
    {
        try {
            $conn = $this->connection;
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Set PDO attribute to use unbuffered queries
            $conn->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, false);

            $fields = array_keys($data);
            $placeholders = ":" . implode(", :", $fields);

            $sql = "INSERT INTO $table (" . implode(", ", $fields) . ") VALUES (" . $placeholders . ")";
            $stmt = $conn->prepare($sql);

            // Bind each value from the $data array to the corresponding placeholder in the SQL statement
            foreach ($data as $field => $value) {
                $stmt->bindValue(":$field", $value);
            }
            $stmt->execute();
            echo "query success";
        } catch (\PDOException $exception) {
            echo "query error: " . $exception->getMessage();
        }
    }

    // update
    protected function update(string $table, array $data , int $recordId): void
    {
        try {
            $conn = $this->connection;
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Build the SET part of the UPDATE statement with column=value pairs
            $setValues = [];
            foreach ($data as $field => $value) {
                $setValues[] = "$field = :$field";
            }
            $setClause = implode(", ", $setValues);

            $sql = "UPDATE $table SET $setClause WHERE id = :record_id";
            $stmt = $conn->prepare($sql);

            // Bind each value from the $data array to the corresponding placeholder in the SQL statement
            foreach ($data as $field => $value) {
                $stmt->bindValue(":$field", $value);
            }

            // Bind the recordId to the placeholder :record_id
            $stmt->bindValue(":record_id", $recordId, PDO::PARAM_INT);

            $stmt->execute();
            echo "Query successful";
        } catch (\PDOException $exception) {
            echo "Query error: " . $exception->getMessage();
        }
    }

    // delete
    protected function delete(string $table, int $id) {
        try {
            $conn = $this->connection;
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $stmt = $conn->prepare("DELETE FROM ".$table." where id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
        } catch (\PDOException $exception) {
            echo "Query error: " . $exception->getMessage();
        }
    }
}