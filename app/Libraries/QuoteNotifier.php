<?php

namespace App\Libraries;

use App\Models\ChatMessageModel;
use App\Models\ChatRoomModel;
use App\Models\UserModel;
use Config\Email as EmailConfig;

/**
 * Sends quote-related chat messages and emails.
 */
class QuoteNotifier
{
    /**
     * @param array<string,mixed> $bookingItem
     * @param array{lines?: list<array>, warnings?: list<string>, total?: float} $breakdown
     */
    public function sendVendorNewQuoteNotification(
        int $vendorId,
        int $customerId,
        int $serviceId,
        array $bookingItem,
        array $breakdown
    ): void {
        $roomModel = new ChatRoomModel();
        $roomId = $roomModel->ensureRoom($vendorId, $customerId, $serviceId);

        $body = $this->formatBreakdownMessage($bookingItem, $breakdown, 'New automated quote request');
        $this->postSystemChatMessage($roomId, $vendorId, $customerId, $body);

        $this->sendEmail(
            $this->userEmail($vendorId),
            'New booking request with quote',
            $body
        );
    }

    /**
     * @param array{lines?: list<array>, warnings?: list<string>} $breakdown
     */
    public function sendCustomerQuoteConfirmed(int $customerId, int $vendorId, int $serviceId, array $breakdown): void
    {
        $roomModel = new ChatRoomModel();
        $roomId = $roomModel->ensureRoom($vendorId, $customerId, $serviceId);

        $body = $this->formatBreakdownMessage([], $breakdown, 'Your quote request was submitted');
        $this->postSystemChatMessage($roomId, $vendorId, $customerId, $body);

        $this->sendEmail(
            $this->userEmail($customerId),
            'Booking request submitted',
            $body
        );
    }

    /**
     * @param array<string,mixed> $context
     * @param array{lines?: list<array>, warnings?: list<string>} $breakdown
     */
    private function formatBreakdownMessage(array $context, array $breakdown, string $heading): string
    {
        $lines = ['[Quote] ' . $heading];
        if (!empty($context['event_title'])) {
            $lines[] = 'Event: ' . $context['event_title'];
        }
        if (!empty($context['event_date'])) {
            $lines[] = 'Date: ' . $context['event_date'];
        }
        foreach ($breakdown['lines'] ?? [] as $line) {
            $lines[] = sprintf(
                '- %s: £%s',
                $line['label'] ?? 'Line',
                number_format((float) ($line['amount'] ?? 0), 2)
            );
        }
        if (!empty($breakdown['warnings'])) {
            $lines[] = 'Notes: ' . implode(' ', $breakdown['warnings']);
        }

        return implode("\n", $lines);
    }

    private function postSystemChatMessage(int $roomId, int $senderId, int $receiverId, string $message): void
    {
        $msgModel = new ChatMessageModel();
        $msgModel->insert([
            'chat_room_id' => $roomId,
            'sender_id' => $senderId,
            'receiver_id' => $receiverId,
            'message' => $message,
            'is_read' => 0,
            'moderation_status' => 'clean',
        ]);
    }

    private function userEmail(int $userId): ?string
    {
        $user = (new UserModel())->find($userId);

        return $user['email'] ?? null;
    }

    private function sendEmail(?string $to, string $subject, string $body): void
    {
        if ($to === null || $to === '') {
            log_message('info', 'Quote email skipped (no recipient): {subject}', ['subject' => $subject]);

            return;
        }

        $email = \Config\Services::email();
        $config = config(EmailConfig::class);
        $from = $config->fromEmail ?: 'noreply@partyplanner.test';
        $fromName = $config->fromName ?: 'Party Planner';

        $email->setFrom($from, $fromName);
        $email->setTo($to);
        $email->setSubject($subject);
        $email->setMessage(nl2br(esc($body)));

        if (!$email->send()) {
            log_message('info', 'Quote email to {to}: {subject}' . "\n{body}", [
                'to' => $to,
                'subject' => $subject,
                'body' => $body,
            ]);
        }
    }
}
