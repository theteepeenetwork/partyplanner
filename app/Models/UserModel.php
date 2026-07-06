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

    protected $allowedFields = [
        'name', 'username', 'email', 'password', 'role',
        'password_reset_token', 'password_reset_expires_at',
        'host_bio', 'host_tagline', 'host_quote', 'host_plays', 'host_photo_path',
        'vendor_status', 'vendor_status_reason', 'vendor_status_reviewed_by', 'vendor_status_reviewed_at',
    ];

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

    /**
     * Fail-safe vendor-approval check for public storefront surfaces (vendor
     * profile page, service detail page). Missing/null `vendor_status` is
     * treated as not-approved, matching VendorAuth and Profile's fail-safe
     * default of 'pending' for a null column.
     */
    public function isVendorApproved(int $vendorId): bool
    {
        $vendor = $this->find($vendorId);

        return $vendor !== null
            && ($vendor['role'] ?? '') === 'vendor'
            && ($vendor['vendor_status'] ?? 'pending') === 'approved';
    }
}
