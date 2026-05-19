<?php

namespace App\Commands;

use App\Libraries\QuoteNotifier;
use App\Models\BookingItemModel;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class QuoteRemindPending extends BaseCommand
{
    protected $group = 'Quote';
    protected $name = 'quote:remind-pending';
    protected $description = 'Email vendors about pending booking requests older than 24 hours';

    public function run(array $params)
    {
        $model = new BookingItemModel();
        $cutoff = date('Y-m-d H:i:s', strtotime('-24 hours'));
        $items = $model
            ->select('booking_items.*, services.vendor_id, services.title as service_title, events.title as event_title, events.date as event_date, bookings.user_id as customer_id', false)
            ->join('bookings', 'bookings.id = booking_items.booking_id')
            ->join('events', 'events.id = bookings.event_id')
            ->join('services', 'services.id = booking_items.service_id')
            ->where('booking_items.status', 'pending')
            ->where('bookings.created_at <', $cutoff)
            ->findAll();

        $notifier = new QuoteNotifier();
        $count = 0;
        foreach ($items as $item) {
            $qd = json_decode($item['quote_breakdown'] ?? '', true);
            $notifier->sendVendorNewQuoteNotification(
                (int) $item['vendor_id'],
                (int) $item['customer_id'],
                (int) $item['service_id'],
                $item,
                is_array($qd) ? $qd : []
            );
            $count++;
        }

        CLI::write("Sent {$count} pending quote reminder(s).");
    }
}
