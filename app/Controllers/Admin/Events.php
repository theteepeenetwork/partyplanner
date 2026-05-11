<?php

namespace App\Controllers\Admin;

use App\Libraries\AdminAccountPurge;
use App\Models\EventModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class Events extends BaseAdminController
{
    public function index()
    {
        $db = db_connect();

        $customerId = (int) $this->request->getGet('customer_id');
        $vendorId   = (int) $this->request->getGet('vendor_id');
        $status     = trim((string) $this->request->getGet('status'));
        $eventType  = trim((string) $this->request->getGet('event_type'));
        $location   = trim((string) $this->request->getGet('location'));
        $dateFrom   = trim((string) $this->request->getGet('date_from'));
        $dateTo     = trim((string) $this->request->getGet('date_to'));

        $builder = $db->table('events')
            ->select('events.*, c.name as customer_name, v.name as vendor_name')
            ->join('users c', 'c.id = events.user_id', 'left')
            ->join('users v', 'v.id = events.vendor_id', 'left');

        if ($customerId > 0) {
            $builder->where('events.user_id', $customerId);
        }
        if ($vendorId > 0) {
            $builder->where('events.vendor_id', $vendorId);
        }
        if ($status !== '') {
            $builder->where('events.status', $status);
        }
        if ($eventType !== '') {
            $builder->like('events.event_type', $eventType);
        }
        if ($location !== '') {
            $builder->groupStart()
                ->like('events.location', $location)
                ->orLike('events.town_city', $location)
                ->orLike('events.postcode', $location)
                ->groupEnd();
        }
        if ($dateFrom !== '') {
            $builder->where('events.date >=', $dateFrom);
        }
        if ($dateTo !== '') {
            $builder->where('events.date <=', $dateTo);
        }

        $events = $builder->orderBy('events.id', 'DESC')->get()->getResultArray();

        return $this->layout('admin/events/index', [
            'title'       => 'Events',
            'activeNav'   => 'events',
            'events'      => $events,
            'customer_id' => $customerId,
            'vendor_id'   => $vendorId,
            'status'      => $status,
            'event_type'  => $eventType,
            'location'    => $location,
            'date_from'   => $dateFrom,
            'date_to'     => $dateTo,
        ]);
    }

    public function show(int $id)
    {
        $eventModel = new EventModel();
        $event      = $eventModel->find($id);
        if (! $event) {
            throw PageNotFoundException::forPageNotFound();
        }

        $db = db_connect();

        $customer = $event['user_id'] ? $db->table('users')->where('id', $event['user_id'])->get()->getRowArray() : null;
        $vendor   = ! empty($event['vendor_id']) ? $db->table('users')->where('id', $event['vendor_id'])->get()->getRowArray() : null;

        $basket = $db->table('event_basket_items')
            ->select('event_basket_items.*, services.title as service_title')
            ->join('services', 'services.id = event_basket_items.service_id', 'left')
            ->where('event_basket_items.event_id', $id)
            ->get()
            ->getResultArray();

        $bookings = $db->table('bookings')
            ->where('event_id', $id)
            ->orderBy('id', 'DESC')
            ->get()
            ->getResultArray();

        return $this->layout('admin/events/show', [
            'title'     => 'Event #' . $id,
            'activeNav' => 'events',
            'event'     => $event,
            'customer'  => $customer,
            'vendor'    => $vendor,
            'basket'    => $basket,
            'bookings'  => $bookings,
        ]);
    }

    public function edit(int $id)
    {
        $eventModel = new EventModel();
        $event      = $eventModel->find($id);
        if (! $event) {
            throw PageNotFoundException::forPageNotFound();
        }

        if ($this->request->getMethod() === 'post') {
            $rules = [
                'title' => 'required|min_length[2]|max_length[255]',
            ];
            if (! $this->validate($rules)) {
                return redirect()->back()->withInput()->with('error', implode(' ', $this->validator->getErrors()));
            }

            $eventModel->update($id, [
                'title'         => $this->request->getPost('title'),
                'description'   => $this->request->getPost('description'),
                'date'          => $this->request->getPost('date') ?: null,
                'location'      => $this->request->getPost('location'),
                'venue_name'    => $this->request->getPost('venue_name'),
                'postcode'      => $this->request->getPost('postcode'),
                'town_city'     => $this->request->getPost('town_city'),
                'event_type'    => $this->request->getPost('event_type'),
                'guest_count'   => $this->request->getPost('guest_count') ?: null,
                'status'        => $this->request->getPost('status'),
                'budget_min'    => $this->request->getPost('budget_min') ?: null,
                'budget_max'    => $this->request->getPost('budget_max') ?: null,
                'style_theme'   => $this->request->getPost('style_theme'),
                'notes'         => $this->request->getPost('notes'),
            ]);

            return redirect()->to('/admin/events/' . $id)->with('success', 'Event updated.');
        }

        return $this->layout('admin/events/edit', [
            'title'     => 'Edit event',
            'activeNav' => 'events',
            'event'     => $event,
        ]);
    }

    public function deleteConfirm(int $id)
    {
        $eventModel = new EventModel();
        $event      = $eventModel->find($id);
        if (! $event) {
            throw PageNotFoundException::forPageNotFound();
        }

        return $this->layout('admin/events/delete_confirm', [
            'title'     => 'Delete event',
            'activeNav' => 'events',
            'event'     => $event,
        ]);
    }

    public function delete(int $id)
    {
        $eventModel = new EventModel();
        $event      = $eventModel->find($id);
        if (! $event) {
            throw PageNotFoundException::forPageNotFound();
        }

        $db = db_connect();
        $db->transStart();
        try {
            (new AdminAccountPurge($db))->purgeEvent($id);
            $db->transComplete();
        } catch (\Throwable $e) {
            $db->transRollback();
            log_message('error', 'Admin event delete: ' . $e->getMessage());

            return redirect()->back()->with('error', 'Deletion failed.');
        }

        if (! $db->transStatus()) {
            return redirect()->back()->with('error', 'Deletion failed.');
        }

        return redirect()->to('/admin/events')->with('success', 'Event removed.');
    }
}
