<?php
namespace App\Models;

use CodeIgniter\Database\ConnectionInterface;
use CodeIgniter\Model;
use CodeIgniter\Validation\ValidationInterface;
use DateTimeInterface;

class UserModel extends Model
{
    protected $table          = 'users';
    protected $primaryKey     = 'id';
    protected $returnType     = 'array';

    protected $allowedFields = ['name', 'username', 'email', 'password', 'role', 'password_reset_token', 'password_reset_expires_at'];

    public function __construct(?ConnectionInterface $db = null, ?ValidationInterface $validation = null)
    {
        parent::__construct($db, $validation);

        // Prevent "You must set the database table" if $table was lost in a bad merge/deploy.
        if ($this->table === null || $this->table === '') {
            $this->table = 'users';
        }
    }

    public function clearPasswordReset(int $userId): bool
    {
        return $this->update($userId, [
            'password_reset_token'      => null,
            'password_reset_expires_at'   => null,
        ]);
    }

    public function setPasswordReset(int $userId, string $token, DateTimeInterface $expires): bool
    {
        return $this->update($userId, [
            'password_reset_token'    => $token,
            'password_reset_expires_at' => $expires->format('Y-m-d H:i:s'),
        ]);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findByPasswordResetToken(string $token): ?array
    {
        if ($token === '' || strlen($token) !== 128 || !ctype_xdigit($token)) {
            return null;
        }

        return $this->where('password_reset_token', $token)->first();
    }
}
