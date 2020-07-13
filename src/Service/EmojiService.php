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
            'tadaa'       => [
                'description' => 'party popper',
                'unicode'     => 'U+1F389',
                'bytecode'    => "\xF0\x9F\x8E\x89",
            ],
            'redlight'    => [
                'description' => 'police cars revolving light',
                'unicode'     => 'U+1F6A8',
                'bytecode'    => "\xF0\x9F\x9A\xA8",
            ],
            // ❌
            'cross-mark'  => [
                'description' => 'cross mark',
                'unicode'     => 'U+274C',
                'bytecode'    => "\xE2\x9D\x8C",
                'native'      => '❌',
            ],
            // ✅
            'check-mark'  => [
                'description' => 'white heavy check mark',
                'unicode'     => 'U+2705',
                'bytecode'    => "\xE2\x9C\x85",
                'native'      => '✅',
            ],
            // ✨
            'sparkles'    => [
                'description' => 'sparkles',
                'unicode'     => 'U+2728',
                'bytecode'    => "\xE2\x9C\xA8",
                'native'      => '✨',
            ],
            'loudspeaker' => [
                'description' => 'public address loudspeaker',
                'unicode'     => 'U+1F4E2',
                'bytecode'    => "\xF0\x9F\x93\xA2",
            ],
            // 📊
            'bar-chart'   => [
                'description' => 'bar chart',
                'unicode'     => 'U+1F4CA',
                'bytecode'    => "\xF0\x9F\x93\x8A",
                'native'      => '📊',
            ],
            // 💡
            'light-bulb' => [
                'description' => 'electric light bulb',
                'unicode'     => 'U+1F4A1',
                'bytecode'    => "\xF0\x9F\x92\xA1",
                'native'      => '💡',
            ],
            'silhouette' => [
                'description' => 'bust in silhouette',
                'unicode'     => 'U+1F464',
                'bytecode'    => "\xF0\x9F\x91\xA4",
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
                $this->emojies[$name]['bytecode'],
                $this->emojies[$name]['native'] ?? ''
            );
        }

        throw new EmojiNotFoundException('No such emoji ;(');
    }

    public function getKeys(): array
    {
        return array_keys($this->emojies);
    }

    /**
     * @return Emoji[]
     * @throws EmojiNotFoundException
     */
    public function getAll(): array
    {
        $emojis = [];

        foreach ($this->getKeys() as $key) {
            $emojis[] = $this->getEmoji($key);
        }

        return $emojis;
    }
}
