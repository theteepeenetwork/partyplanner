<?php

namespace App\Controllers\Admin;

use App\Libraries\AdminAccountPurge;
use App\Models\BookingItemModel;
use App\Models\BookingModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class Bookings extends BaseAdminController
{
    public function index()
    {
        $customerId = (int) $this->request->getGet('customer_id');
        $vendorId   = (int) $this->request->getGet('vendor_id');
        $status     = trim((string) $this->request->getGet('status'));
        $serviceId  = (int) $this->request->getGet('service_id');
        $dateFrom   = trim((string) $this->request->getGet('date_from'));
        $dateTo     = trim((string) $this->request->getGet('date_to'));

        $db = db_connect();

        $ids = null;
        if ($vendorId > 0 || $serviceId > 0) {
            $b = $db->table('bookings')->select('bookings.id');
            $b->join('booking_items', 'booking_items.booking_id = bookings.id');
            $b->join('services', 'services.id = booking_items.service_id');
            if ($vendorId > 0) {
                $b->where('services.vendor_id', $vendorId);
            }
            if ($serviceId > 0) {
                $b->where('booking_items.service_id', $serviceId);
            }
            $ids = array_values(array_unique(array_column($b->get()->getResultArray(), 'id')));
        }

        $bookingModel = new BookingModel();
        $bookingModel->select('bookings.*, users.name as customer_name, events.title as event_title, events.date as event_date');
        $bookingModel->join('users', 'users.id = bookings.user_id', 'left');
        $bookingModel->join('events', 'events.id = bookings.event_id', 'left');

        if ($customerId > 0) {
            $bookingModel->where('bookings.user_id', $customerId);
        }
        if ($status !== '') {
            $bookingModel->where('bookings.status', $status);
        }
        if ($dateFrom !== '') {
            $bookingModel->where('events.`date` >=', $dateFrom, false);
        }
        if ($dateTo !== '') {
            $bookingModel->where('events.`date` <=', $dateTo, false);
        }
        if ($vendorId > 0 || $serviceId > 0) {
            if ($ids === []) {
                $bookingModel->where('1=0', null, false);
            } elseif (is_array($ids)) {
                $bookingModel->whereIn('bookings.id', $ids);
            }
        }

        $bookings = $bookingModel->orderBy('bookings.created_at', 'DESC')->paginate(30);
        $pager    = $bookingModel->pager;

        return $this->layout('admin/bookings/index', [
            'title'       => 'Bookings',
            'activeNav'   => 'bookings',
            'bookings'    => $bookings,
            'pager'       => $pager,
            'customer_id' => $customerId,
            'vendor_id'   => $vendorId,
            'status'      => $status,
            'service_id'  => $serviceId,
            'date_from'   => $dateFrom,
            'date_to'     => $dateTo,
        ]);
    }

    public function show(int $id)
    {
        $bookingModel = new BookingModel();
        $booking      = $bookingModel->find($id);
        if (! $booking) {
            throw PageNotFoundException::forPageNotFound();
        }

        $db = db_connect();

        $customer = $db->table('users')->where('id', $booking['user_id'])->get()->getRowArray();
        $event    = $booking['event_id'] ? $db->table('events')->where('id', $booking['event_id'])->get()->getRowArray() : null;

        $items = $db->table('booking_items')
            ->select('booking_items.*, services.title as service_title, services.vendor_id, vendors.name as vendor_name')
            ->join('services', 'services.id = booking_items.service_id')
            ->join('users as vendors', 'vendors.id = services.vendor_id', 'left')
            ->where('booking_items.booking_id', $id)
            ->get()
            ->getResultArray();

        $payments = $db->table('payments')->where('booking_id', $id)->orderBy('id', 'DESC')->get()->getResultArray();

        $roomIds = [];
        foreach ($items as $it) {
            if (! empty($it['vendor_id']) && ! empty($booking['user_id'])) {
                $r = $db->table('chat_rooms')
                    ->where('vendor_id', $it['vendor_id'])
                    ->where('customer_id', $booking['user_id'])
                    ->get()
                    ->getRowArray();
                if ($r) {
                    $roomIds[$r['id']] = $r;
                }
            }
        }

        return $this->layout('admin/bookings/show', [
            'title'     => 'Booking #' . $id,
            'activeNav' => 'bookings',
            'booking'   => $booking,
            'customer'  => $customer,
            'event'     => $event,
            'items'     => $items,
            'payments'  => $payments,
            'chatRooms' => array_values($roomIds),
        ]);
    }

    public function updateStatus(int $id)
    {
        $bookingModel = new BookingModel();
        $booking      = $bookingModel->find($id);
        if (! $booking) {
            throw PageNotFoundException::forPageNotFound();
        }

        $status  = (string) $this->request->getPost('status');
        $allowed = ['pending', 'accepted', 'confirmed', 'declined', 'cancelled', 'completed'];
        if (! in_array($status, $allowed, true)) {
            return redirect()->back()->with('error', 'Invalid status.');
        }

        $bookingModel->update($id, ['status' => $status]);

        $itemModel = new BookingItemModel();
        $itemModel->where('booking_id', $id)->set(['status' => $status])->update();

        return redirect()->back()->with('success', 'Booking status updated.');
    }

    public function deleteConfirm(int $id)
    {
        $bookingModel = new BookingModel();
        $booking      = $bookingModel->find($id);
        if (! $booking) {
            throw PageNotFoundException::forPageNotFound();
        }

        return $this->layout('admin/bookings/delete_confirm', [
            'title'     => 'Delete booking',
            'activeNav' => 'bookings',
            'booking'   => $booking,
        ]);
    }

    public function delete(int $id)
    {
        $bookingModel = new BookingModel();
        $booking      = $bookingModel->find($id);
        if (! $booking) {
            throw PageNotFoundException::forPageNotFound();
        }

        $db = db_connect();
        $db->transStart();
        try {
            (new AdminAccountPurge($db))->purgeBookingsByIds([$id]);
            $db->transComplete();
        } catch (\Throwable $e) {
            $db->transRollback();
            log_message('error', 'Admin booking delete: ' . $e->getMessage());

            return redirect()->back()->with('error', 'Deletion failed.');
        }

        if (! $db->transStatus()) {
            return redirect()->back()->with('error', 'Deletion failed.');
        }

        return redirect()->to('/admin/bookings')->with('success', 'Booking removed.');
    }
}
