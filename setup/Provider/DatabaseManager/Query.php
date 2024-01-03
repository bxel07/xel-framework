<?php

namespace Setup\Provider\DatabaseManager;
use PDOException;
use Swoole\Coroutine as go;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Setup\Provider\DatabaseManager\Builder\XgenQuery_1;

class Query
{
     public function __construct(public Request $request, public Response $response, public mixed $path, public mixed $method, public XgenQuery_1 $db)
     {
 
     }
 
     public function handleRequest(): void
     {
             if ($this->method === 'GET') {
                 if ($this->path === '/') {
                     $this->index();
                 } elseif (preg_match('/\/users\/(\d+)/', $this->path, $matches)) {
                     $this->show($matches[1]);
                 }
             } elseif ($this->method === 'POST') {
                 if ($this->path === '/users') {
                     $this->store();
                 }
             } elseif ($this->method === 'PUT') {
                 if (preg_match('/\/users\/(\d+)/', $this->path, $matches)) {
                     $this->update($matches[1]);
                 }
             } elseif ($this->method === 'DELETE') {
                 if (preg_match('/\/users\/(\d+)/', $this->path, $matches)) {
                     $this->destroy($matches[1]);
                 }
             }
     }
 
     private function index(): void
     {
         go\go(function () {
             try {
                 $result = $this->db->selectAll('users');
                 $this->response->header("Content-Type", "application/json");
                 $this->response->end(json_encode($result));
             } catch (PDOException $e) {
                 $this->response->status(500);
                 $this->response->end(json_encode(["error" => $e->getMessage()]));
             }
         });
     }
 
     private function show($id): void
     {
         // Handle GET request for fetching a specific us
 
         // Implement your logic here
         $this->response->header("Content-Type", "application/json");
         $this->response->end(json_encode(["message" => "GET Request for user with ID " . $id]));
     }
 
     private function store(): void
     {
         // Handle POST request for creating a user
         // Implement your logic here
         $this->response->header("Content-Type", "application/json");
         $this->response->end(json_encode(["message" => "POST Request for creating a user"]));
     }
 
     private function update($id): void
     {
         // Handle PUT request for updating a user
         // Implement your logic here
         $this->response->header("Content-Type", "application/json");
         $this->response->end(json_encode(["message" => "PUT Request for updating user with ID " . $id]));
     }
 
     private function destroy($id): void
     {
         // Handle DELETE request for deleting a user
         // Implement your logic here
         $this->response->header("Content-Type", "application/json");
         $this->response->end(json_encode(["message" => "DELETE Request for deleting user with ID " . $id]));
     }

}
