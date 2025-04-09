<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table = 'users';               // Table name
    protected $primaryKey = 'id';             // Primary key

    protected $useAutoIncrement = true;
    protected $returnType = 'array';          // Can be 'object' too
    protected $useSoftDeletes = false;

    protected $allowedFields = [              // Fields allowed for insert/update
        'username',
        'email',
        'password',
        'created_at',
        'updated_at',
    ];

    protected $useTimestamps = true;          // Enable created_at and updated_at
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
}
