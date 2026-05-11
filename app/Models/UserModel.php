<?php
namespace App\Models;

use CodeIgniter\Database\ConnectionInterface;
use CodeIgniter\Model;
use CodeIgniter\Validation\ValidationInterface;

class UserModel extends Model
{
    protected $table          = 'users';
    protected $primaryKey     = 'id';
    protected $returnType     = 'array';

    protected $allowedFields = ['name', 'username', 'email', 'password', 'role'];

    public function __construct(?ConnectionInterface $db = null, ?ValidationInterface $validation = null)
    {
        parent::__construct($db, $validation);

        // Prevent "You must set the database table" if $table was lost in a bad merge/deploy.
        if ($this->table === null || $this->table === '') {
            $this->table = 'users';
        }
    }
}
