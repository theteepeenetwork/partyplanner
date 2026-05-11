<?php

namespace App\Libraries;

/**
 * Censors configured whole words in chat text and records metadata for moderation.
 */
class ChatModeration
{
    public const STATUS_CLEAN          = 'clean';
    public const STATUS_PENDING        = 'pending_review';
    public const STATUS_APPROVED       = 'approved';
    public const STATUS_REJECTED       = 'rejected';

    public const REJECTED_PLACEHOLDER = '[This message was not sent after review.]';

    /**
     * @return array{text: string, hits: list<string>}
     */
    public function censor(string $raw): array
    {
        $raw  = (string) $raw;
        $cfg  = config('ProfanityWords');
        $list = ($cfg && isset($cfg->blocked)) ? $cfg->blocked : [];
        $hits = [];

        $out = $raw;
        foreach ($list as $word) {
            $word = strtolower(trim((string) $word));
            if ($word === '') {
                continue;
            }
            $pattern = '/\b' . preg_quote($word, '/') . '\b/iu';
            if (preg_match($pattern, $out)) {
                $hits[] = $word;
                $out = (string) preg_replace_callback(
                    $pattern,
                    static fn (array $m) => str_repeat('*', self::len($m[0])),
                    $out
                );
            }
        }

        return [
            'text' => $out,
            'hits' => array_values(array_unique($hits)),
        ];
    }

    /**
     * Build extra DB fields for chat_messages insert/update.
     *
     * @return array<string, mixed>
     */
    public function moderationFieldsForInsert(string $rawMessage): array
    {
        $censored = $this->censor($rawMessage);
        $had      = $censored['hits'] !== [];

        return [
            'message'             => $censored['text'],
            'original_message'    => $had ? $rawMessage : null,
            'moderation_status'   => $had ? self::STATUS_PENDING : self::STATUS_CLEAN,
            'profanity_matches'   => $had ? substr(implode(',', $censored['hits']), 0, 500) : null,
            'admin_note'          => null,
            'reviewed_by'         => null,
            'reviewed_at'         => null,
        ];
    }

    public static function refreshRoomModerationFlag(int $roomId): void
    {
        $db = db_connect();
        $pending = $db->table('chat_messages')
            ->where('chat_room_id', $roomId)
            ->where('moderation_status', self::STATUS_PENDING)
            ->countAllResults();

        $db->table('chat_rooms')->where('id', $roomId)->update([
            'flagged_for_review' => $pending > 0 ? 1 : 0,
        ]);
    }

    private static function len(string $s): int
    {
        if (function_exists('mb_strlen')) {
            return mb_strlen($s, 'UTF-8');
        }

        return strlen($s);
    }
}
