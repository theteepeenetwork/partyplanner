<?php

namespace App\Libraries;

/**
 * Records daily quote metrics for vendor analytics.
 */
class QuoteAnalyticsRecorder
{
    public function recordQuoteGenerated(int $vendorId, ?int $serviceId, float $total): void
    {
        $this->increment($vendorId, $serviceId, [
            'quotes_generated' => 1,
            'avg_total' => $total,
        ]);
    }

    /**
     * Increment the accepted-quote counter for the given vendor and optionally mark it as auto-accepted.
     *
     * @param int      $vendorId     The vendor user primary key.
     * @param int|null $serviceId    The service primary key, or null for vendor-level recording.
     * @param bool     $autoAccepted Whether the quote was accepted automatically by the automation rules.
     * @return void
     */
    public function recordAccepted(int $vendorId, ?int $serviceId, bool $autoAccepted = false): void
    {
        $data = ['quotes_accepted' => 1];
        if ($autoAccepted) {
            $data['auto_accepted'] = 1;
        }
        $this->increment($vendorId, $serviceId, $data);
    }

    /**
     * @param array<string, int|float> $increments
     */
    private function increment(int $vendorId, ?int $serviceId, array $increments): void
    {
        $db = \Config\Database::connect();
        if (!$db->tableExists('quote_analytics_daily')) {
            return;
        }

        $today = date('Y-m-d');
        $existing = $db->table('quote_analytics_daily')
            ->where('vendor_id', $vendorId)
            ->where('service_id', $serviceId)
            ->where('metric_date', $today)
            ->get()
            ->getRowArray();

        if ($existing) {
            $update = [];
            foreach ($increments as $key => $val) {
                if ($key === 'avg_total') {
                    $prev = (int) ($existing['quotes_generated'] ?? 0);
                    $prevAvg = (float) ($existing['avg_total'] ?? 0);
                    $newCount = $prev + 1;
                    $update['avg_total'] = $newCount > 0
                        ? round((($prevAvg * $prev) + (float) $val) / $newCount, 2)
                        : (float) $val;
                    $update['quotes_generated'] = $newCount;
                } else {
                    $update[$key] = (int) ($existing[$key] ?? 0) + (int) $val;
                }
            }
            $db->table('quote_analytics_daily')->where('id', $existing['id'])->update($update);
        } else {
            $row = array_merge([
                'vendor_id' => $vendorId,
                'service_id' => $serviceId,
                'metric_date' => $today,
                'quotes_generated' => 0,
                'quotes_accepted' => 0,
                'auto_accepted' => 0,
                'avg_total' => null,
            ], $increments);
            if (isset($increments['avg_total']) && !isset($increments['quotes_generated'])) {
                $row['quotes_generated'] = 1;
            }
            $db->table('quote_analytics_daily')->insert($row);
        }
    }
}
