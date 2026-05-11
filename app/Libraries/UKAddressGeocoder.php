<?php

namespace App\Libraries;

/**
 * Best-effort geocoding for UK postcodes / towns via Nominatim (OpenStreetMap).
 * Fails closed (returns null) on any error; callers should tolerate missing coordinates.
 */
class UKAddressGeocoder
{
    private const ENDPOINT = 'https://nominatim.openstreetmap.org/search';

    public function geocode(?string $postcode, ?string $townCity): ?array
    {
        $postcode = $postcode !== null ? trim($postcode) : '';
        $townCity = $townCity !== null ? trim($townCity) : '';

        if ($postcode === '' && $townCity === '') {
            return null;
        }

        $query = $postcode !== '' ? $postcode : $townCity;
        if ($townCity !== '' && $postcode !== '' && stripos($query, $townCity) === false) {
            $query = $townCity . ', ' . $postcode;
        }
        $query .= ', United Kingdom';

        $url = self::ENDPOINT . '?' . http_build_query([
            'format'         => 'json',
            'limit'          => 1,
            'countrycodes'   => 'gb',
            'q'              => $query,
        ]);

        $ctx = stream_context_create([
            'http' => [
                'timeout' => 6,
                'header'  => "User-Agent: EventMarketplace/1.0 (booking quotes)\r\n",
            ],
        ]);

        $raw = @file_get_contents($url, false, $ctx);
        if ($raw === false) {
            return null;
        }

        $data = json_decode($raw, true);
        if (!is_array($data) || $data === [] || !isset($data[0]['lat'], $data[0]['lon'])) {
            return null;
        }

        $lat = (float) $data[0]['lat'];
        $lon = (float) $data[0]['lon'];

        if ($lat < -90 || $lat > 90 || $lon < -180 || $lon > 180) {
            return null;
        }

        return ['latitude' => $lat, 'longitude' => $lon];
    }
}
