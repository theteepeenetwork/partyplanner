<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Blocked terms for chat (whole-word, case-insensitive). Edit this list for your site policy.
 * Keep entries lowercase; add phrases as single tokens only when they are whole words.
 */
class ProfanityWords extends BaseConfig
{
    /**
     * @var list<string>
     */
    public array $blocked = [
        'arse', 'arsehole', 'ass', 'asshole', 'bastard', 'bitch', 'bloody', 'bollocks', 'bugger',
        'bullshit', 'cock', 'crap', 'crappy', 'damn', 'dick', 'dickhead',
        'douche', 'douchebag', 'fuck', 'fucked', 'fucker', 'fucking', 'hell',
        'motherfucker', 'piss', 'pissed', 'prick', 'pussy', 'shit', 'shitty',
        'slut', 'twat', 'wank', 'wanker', 'whore',
        // Add hate speech, slurs, and locale-specific terms here to match your policy.
    ];
}
