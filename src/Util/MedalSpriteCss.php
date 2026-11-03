<?php

declare(strict_types=1);

namespace App\Util;

use SplFileInfo;

/**
 * Lays medal images out on a sprite grid and writes the matching CSS.
 */
class MedalSpriteCss
{
    /**
     * @param iterable<SplFileInfo> $files the images in sprite order
     *
     * @return array{css: string, paths: list<string>} the stylesheet and the image paths in grid order
     */
    public function generate(iterable $files, int $size, int $imagesPerRow, string $imageName): array
    {
        $paths = [];
        $cssLines = [
            '.medal'.$size.' {',
            '	width: '.$size.'px;',
            '	height: '.$size.'px;',
            '	display: inline-block;',
            '	background:url(../images/sprites/'.$imageName.') no-repeat',
            '}',
        ];

        foreach (array_values([...$files]) as $index => $file) {
            $paths[] = (string) $file->getRealPath();

            $cssLines[] = sprintf(
                '.medal'.$size.'.medal-%s {background-position: %s %s}',
                str_replace('.png', '', $file->getBasename()),
                $this->offset($index % $imagesPerRow, $size),
                $this->offset(intdiv($index, $imagesPerRow), $size),
            );
        }

        return ['css' => implode("\n", $cssLines), 'paths' => $paths];
    }

    private function offset(int $cell, int $size): string
    {
        return 0 !== $cell ? '-'.$cell * $size.'px' : '0';
    }
}
