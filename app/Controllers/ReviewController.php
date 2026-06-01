<?php

namespace App\Controllers;

use App\Libraries\ChatModeration;
use App\Models\BookingItemModel;
use App\Models\ReviewModel;
use App\Models\ServiceModel;

class ReviewController extends BaseController
{
    private function requireCustomer()
    {
        if (! session()->has('user_id')) {
            return redirect()->to('/login');
        }
        if (session()->get('role') !== 'customer') {
            return redirect()->to('/profile')->with('error', 'Only customers can leave reviews.');
        }

        return null;
    }

    /**
     * Show the "leave a review" form for an eligible booking line.
     */
    public function create(int $bookingItemId)
    {
        if ($r = $this->requireCustomer()) {
            return $r;
        }

        $customerId        = (int) session()->get('user_id');
        $bookingItemModel  = new BookingItemModel();
        $reviewModel       = new ReviewModel();

        if (! $bookingItemModel->isReviewableByCustomer($bookingItemId, $customerId)) {
            return redirect()->to('/profile/my-bookings')
                ->with('error', 'This booking is not available to review yet.');
        }
        if ($reviewModel->hasReviewedBookingItem($bookingItemId)) {
            return redirect()->to('/profile/my-bookings')
                ->with('error', 'You have already reviewed this booking.');
        }

        $item = $bookingItemModel
            ->select('booking_items.id, services.title AS service_title, events.title AS event_title, events.event_type, events.date AS event_date')
            ->join('bookings', 'bookings.id = booking_items.booking_id')
            ->join('events', 'events.id = bookings.event_id')
            ->join('services', 'services.id = booking_items.service_id')
            ->where('booking_items.id', $bookingItemId)
            ->first();

        return view('reviews/create', [
            'bookingItemId' => $bookingItemId,
            'item'          => $item,
        ]);
    }

    /**
     * Persist a submitted review (text run through the profanity filter; publishes immediately).
     */
    public function store()
    {
        if ($r = $this->requireCustomer()) {
            return $r;
        }

        $customerId       = (int) session()->get('user_id');
        $bookingItemId    = (int) $this->request->getPost('booking_item_id');
        $bookingItemModel = new BookingItemModel();
        $reviewModel      = new ReviewModel();

        // Re-run every server-side guard — never trust the GET gate.
        if ($bookingItemId <= 0 || ! $bookingItemModel->isReviewableByCustomer($bookingItemId, $customerId)) {
            return redirect()->to('/profile/my-bookings')
                ->with('error', 'This booking is not available to review.');
        }
        if ($reviewModel->hasReviewedBookingItem($bookingItemId)) {
            return redirect()->to('/profile/my-bookings')
                ->with('error', 'You have already reviewed this booking.');
        }

        $rules = [
            'rating'  => 'required|in_list[1,2,3,4,5]',
            'title'   => 'required|min_length[3]|max_length[150]',
            'comment' => 'required|min_length[10]|max_length[2000]',
        ];
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        // Derive service + vendor server-side from the booking line.
        $item = $bookingItemModel
            ->select('booking_items.service_id, services.vendor_id')
            ->join('services', 'services.id = booking_items.service_id')
            ->where('booking_items.id', $bookingItemId)
            ->first();
        if (! $item) {
            return redirect()->to('/profile/my-bookings')->with('error', 'Service not found for this booking.');
        }

        $moderation    = new ChatModeration();
        $titleCensored = $moderation->censor((string) $this->request->getPost('title'));
        $bodyCensored  = $moderation->censor((string) $this->request->getPost('comment'));
        $flagged       = ($titleCensored['hits'] !== [] || $bodyCensored['hits'] !== []) ? 1 : 0;

        try {
            $reviewModel->insert([
                'booking_item_id' => $bookingItemId,
                'customer_id'     => $customerId,
                'vendor_id'       => (int) $item['vendor_id'],
                'service_id'      => (int) $item['service_id'],
                'rating'          => (int) $this->request->getPost('rating'),
                'title'           => $titleCensored['text'],
                'comment'         => $bodyCensored['text'],
                'flagged'         => $flagged,
            ]);
        } catch (\Throwable $e) {
            // Unique key race (already reviewed between the guard and insert).
            return redirect()->to('/profile/my-bookings')
                ->with('error', 'You have already reviewed this booking.');
        }

        return redirect()->to('/service/view/' . (int) $item['service_id'])
            ->with('success', 'Thanks for your review!');
    }
}
