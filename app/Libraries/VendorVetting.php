<?php

namespace App\Libraries;

use App\Models\UserModel;

/**
 * Vendor vetting state transitions (pending/approved/rejected) plus the
 * audit-trail writes (reviewed_by/reviewed_at), following the
 * Admin\Messages moderation write pattern.
 */
class VendorVetting
{
    public const MAX_REASON_LENGTH = 2000;

    private UserModel $userModel;

    public function __construct(?UserModel $userModel = null)
    {
        $this->userModel = $userModel ?? new UserModel();
    }

    /**
     * Approve a vendor. Any state may transition to approved (pending→approved,
     * rejected→approved re-instate, approved→approved no-op refresh).
     * An optional reason may be recorded; passing null clears any stale
     * rejection reason left over from a previous decision.
     */
    public function approve(int $vendorId, int $adminId, ?string $reason = null): bool
    {
        $vendor = $this->findVendor($vendorId);
        if ($vendor === null) {
            return false;
        }

        $normalisedReason = $this->normaliseReason($reason);

        return $this->userModel->update($vendorId, [
            'vendor_status'             => 'approved',
            'vendor_status_reason'      => $normalisedReason,
            'vendor_status_reviewed_by' => $adminId,
            'vendor_status_reviewed_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Reject a vendor. Requires a non-empty (trimmed) reason. Any state may
     * transition to rejected (pending→rejected, approved→rejected revoke).
     */
    public function reject(int $vendorId, int $adminId, string $reason): bool
    {
        $vendor = $this->findVendor($vendorId);
        if ($vendor === null) {
            return false;
        }

        $normalisedReason = $this->normaliseReason($reason);
        if ($normalisedReason === null) {
            return false;
        }

        return $this->userModel->update($vendorId, [
            'vendor_status'             => 'rejected',
            'vendor_status_reason'      => $normalisedReason,
            'vendor_status_reviewed_by' => $adminId,
            'vendor_status_reviewed_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * @return array<string, mixed>|null
     */
    private function findVendor(int $vendorId): ?array
    {
        if ($vendorId <= 0) {
            return null;
        }

        $vendor = $this->userModel->find($vendorId);
        if (! $vendor || ($vendor['role'] ?? '') !== 'vendor') {
            return null;
        }

        return $vendor;
    }

    private function normaliseReason(?string $reason): ?string
    {
        if ($reason === null) {
            return null;
        }

        $trimmed = trim($reason);
        if ($trimmed === '') {
            return null;
        }

        if (strlen($trimmed) > self::MAX_REASON_LENGTH) {
            $trimmed = substr($trimmed, 0, self::MAX_REASON_LENGTH);
        }

        return $trimmed;
    }
}
