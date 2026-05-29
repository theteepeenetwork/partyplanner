<?php

namespace App\Commands;

use App\Models\BookingItemModel;
use App\Models\VendorQuoteModel;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * CLI command that auto-declines stale pending booking items and expires overdue vendor quotes.
 */
class QuoteExpireStale extends BaseCommand
{
    protected $group = 'Quote';
    protected $name = 'quote:expire-stale';
    protected $description = 'Auto-decline pending bookings older than 14 days and expire sent vendor quotes';

    /**
     * Decline booking items pending longer than the given number of days and mark expired vendor quotes.
     *
     * @param array<int, string> $params Optional: first element overrides the default 14-day threshold.
     * @return void
     */
    public function run(array $params)
    {
        $days = (int) ($params[0] ?? 14);
        $cutoff = date('Y-m-d H:i:s', strtotime('-' . $days . ' days'));

        $bookingItemModel = new BookingItemModel();
        $stale = $bookingItemModel
            ->select('booking_items.id')
            ->join('bookings', 'bookings.id = booking_items.booking_id')
            ->where('booking_items.status', 'pending')
            ->where('bookings.created_at <', $cutoff)
            ->findAll();

        $declined = 0;
        foreach ($stale as $row) {
            $bookingItemModel->update($row['id'], ['status' => 'rejected']);
            $declined++;
        }

        $vqModel = new VendorQuoteModel();
        if (\Config\Database::connect()->tableExists('vendor_quotes')) {
            $vqModel->where('status', 'sent')
                ->where('expires_at <', date('Y-m-d H:i:s'))
                ->set(['status' => 'expired'])
                ->update();
        }

        CLI::write("Declined {$declined} stale pending booking(s).");
    }
}
