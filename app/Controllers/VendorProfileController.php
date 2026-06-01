<?php

namespace App\Controllers;

use App\Models\ReviewModel;
use App\Models\ServiceModel;
use App\Models\UserModel;

class VendorProfileController extends BaseController
{
    /**
     * Public vendor profile: host details, vendor-wide rating, and ALL reviews across services.
     */
    public function show(int $vendorId)
    {
        $vendorUser = (new UserModel())->find($vendorId);
        if (! $vendorUser || ($vendorUser['role'] ?? '') !== 'vendor') {
            return redirect()->to('/browse-services')->with('error', 'Vendor not found.');
        }

        $playsArr = [];
        if (! empty($vendorUser['host_plays'])) {
            $decoded  = json_decode($vendorUser['host_plays'], true);
            $playsArr = is_array($decoded) ? $decoded : [];
        }
        $memberSince = ! empty($vendorUser['created_at'])
            ? (int) date('Y', strtotime($vendorUser['created_at']))
            : null;

        $vendorProfile = [
            'name'       => $vendorUser['name'],
            'tagline'    => $vendorUser['host_tagline'] ?? '',
            'bio'        => $vendorUser['host_bio'] ?? '',
            'quote'      => $vendorUser['host_quote'] ?? '',
            'plays'      => $playsArr,
            'photo_path' => $vendorUser['host_photo_path'] ?? '',
            'since'      => $memberSince,
        ];

        $reviewModel = new ReviewModel();
        $services    = (new ServiceModel())
            ->where('vendor_id', $vendorId)
            ->where('status', 'active')
            ->findAll();

        return view('vendor_profile', [
            'vendor_id'      => $vendorId,
            'vendor_profile' => $vendorProfile,
            'vendor_rating'  => $reviewModel->vendorRatingSummary($vendorId),
            'reviews'        => $reviewModel->vendorReviews($vendorId),
            'services'       => $services,
        ]);
    }
}
