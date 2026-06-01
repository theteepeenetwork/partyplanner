<?php

namespace App\Controllers\Admin;

use App\Libraries\ChatModeration;
use App\Models\ReviewModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class Reviews extends BaseAdminController
{
    /**
     * @return ReviewModel
     */
    private function withJoins(ReviewModel $model): ReviewModel
    {
        $model->select('reviews.*, customer.name AS customer_name, vendor.name AS vendor_name, services.title AS service_title')
            ->join('users AS customer', 'customer.id = reviews.customer_id', 'left')
            ->join('users AS vendor', 'vendor.id = reviews.vendor_id', 'left')
            ->join('services', 'services.id = reviews.service_id', 'left');

        return $model;
    }

    public function index()
    {
        $vendorId  = (int) $this->request->getGet('vendor_id');
        $serviceId = (int) $this->request->getGet('service_id');
        $flagged   = (int) $this->request->getGet('flagged') === 1;

        $model = $this->withJoins(new ReviewModel());

        if ($vendorId > 0) {
            $model->where('reviews.vendor_id', $vendorId);
        }
        if ($serviceId > 0) {
            $model->where('reviews.service_id', $serviceId);
        }
        if ($flagged) {
            $model->where('reviews.flagged', 1);
        }

        $reviews = $model->orderBy('reviews.id', 'DESC')->paginate(25);
        $pager   = $model->pager;

        return $this->layout('admin/reviews/index', [
            'title'      => 'Reviews',
            'activeNav'  => 'reviews',
            'reviews'    => $reviews,
            'pager'      => $pager,
            'vendor_id'  => $vendorId,
            'service_id' => $serviceId,
            'flagged'    => $flagged,
        ]);
    }

    public function show(int $id)
    {
        $review = $this->withJoins(new ReviewModel())->where('reviews.id', $id)->first();
        if (! $review) {
            throw PageNotFoundException::forPageNotFound();
        }

        return $this->layout('admin/reviews/show', [
            'title'     => 'Review #' . $id,
            'activeNav' => 'reviews',
            'review'    => $review,
        ]);
    }

    public function edit(int $id)
    {
        $reviewModel = new ReviewModel();
        $review      = $reviewModel->find($id);
        if (! $review) {
            throw PageNotFoundException::forPageNotFound();
        }

        if ($this->request->is('post')) {
            $rules = [
                'rating'  => 'required|in_list[1,2,3,4,5]',
                'title'   => 'required|min_length[3]|max_length[150]',
                'comment' => 'required|min_length[10]|max_length[2000]',
            ];
            if (! $this->validate($rules)) {
                return redirect()->back()->withInput()->with('error', implode(' ', $this->validator->getErrors()));
            }

            // Re-censor admin-edited text so profanity can't be reintroduced.
            $moderation    = new ChatModeration();
            $titleCensored = $moderation->censor((string) $this->request->getPost('title'));
            $bodyCensored  = $moderation->censor((string) $this->request->getPost('comment'));

            $reviewModel->update($id, [
                'rating'  => (int) $this->request->getPost('rating'),
                'title'   => $titleCensored['text'],
                'comment' => $bodyCensored['text'],
                'flagged' => ($titleCensored['hits'] !== [] || $bodyCensored['hits'] !== []) ? 1 : 0,
            ]);

            return redirect()->to('/admin/reviews/' . $id)->with('success', 'Review updated.');
        }

        return $this->layout('admin/reviews/edit', [
            'title'     => 'Edit review',
            'activeNav' => 'reviews',
            'review'    => $review,
        ]);
    }

    public function deleteConfirm(int $id)
    {
        $review = $this->withJoins(new ReviewModel())->where('reviews.id', $id)->first();
        if (! $review) {
            throw PageNotFoundException::forPageNotFound();
        }

        return $this->layout('admin/reviews/delete_confirm', [
            'title'     => 'Remove review',
            'activeNav' => 'reviews',
            'review'    => $review,
        ]);
    }

    public function delete(int $id)
    {
        $reviewModel = new ReviewModel();
        if (! $reviewModel->find($id)) {
            throw PageNotFoundException::forPageNotFound();
        }

        $reviewModel->delete($id);

        return redirect()->to('/admin/reviews')->with('success', 'Review permanently removed.');
    }
}
