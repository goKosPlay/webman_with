<?php

namespace app\example;

use app\attribute\dependency\{Repository, Autowired, Value};

#[Repository(singleton: true)]
class ExampleUserRepository
{
    #[Value(key: 'database.default', default: 'mysql')]
    private string $connection;
    
    #[Autowired]
    private ExampleDatabaseConnection $db;
    
    public function create($data)
    {
        return [];
    }
    
    public function findById($id)
    {
        return null;
    }
    
    public function update($id, $data)
    {
        return [];
    }
    
    public function delete($id)
    {
        return true;
    }
    
    public function paginate($page = 1, $perPage = 20)
    {
        return [];
    }
    
    public function deleteInactive($days)
    {
        return 0;
    }
    
    public function updateLastLogin($userId)
    {
        return true;
    }
    
    public function updateStatistics()
    {
        return true;
    }
}
