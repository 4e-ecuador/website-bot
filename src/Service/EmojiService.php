<?php
/**
 * @see https://apps.timwhitlock.info/emoji/tables/unicode
 */

namespace App\Service;

use App\Exception\EmojiNotFoundException;
use App\Type\Emoji;

/**
 *
 *                                     ☺
 *
 */
final class EmojiService
{
    private array $emojies
        = [
            'tadaa'      => [
                'description' => 'party popper',
                'unicode'     => 'U+1F389',
                'bytecode'    => "\xF0\x9F\x8E\x89",
            ],
            'redlight'   => [
                'description' => 'police cars revolving light',
                'unicode'     => 'U+1F6A8',
                'bytecode'    => "\xF0\x9F\x9A\xA8",
            ],
            // ❌
            'cross-mark' => [
                'description' => 'cross mark',
                'unicode'     => 'U+274C',
                'bytecode'    => "\xE2\x9D\x8C",
            ],
            // ✅
            'check-mark' => [
                'description' => 'white heavy check mark',
                'unicode'     => 'U+2705',
                'bytecode'    => "\xE2\x9C\x85",
            ],
            // ✨
            'sparkles'   => [
                'description' => 'sparkles',
                'raw'         => '✨',
                'unicode'     => 'U+2728',
                'bytecode'    => "\xE2\x9C\xA8",
            ],
            // '' => [
            //     'description' => '',
            //     'unicode'     => '',
            //     'bytecode'    => "",
            // ],
        ];

    /**
     * @throws EmojiNotFoundException
     */
    public function getEmoji(string $name): Emoji
    {
        if (array_key_exists($name, $this->emojies)) {
            return new Emoji(
                $name,
                $this->emojies[$name]['description'],
                $this->emojies[$name]['unicode'],
                $this->emojies[$name]['bytecode']
            );
        }

        throw new EmojiNotFoundException('No such emoji ;(');
    }
}
