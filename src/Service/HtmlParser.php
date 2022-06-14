<?php

namespace App\Service;

use App\Entity\IngressEvent;
use App\Type\FsAttendeesInfo;
use Goutte\Client;
use stdClass;

class HtmlParser
{
    public function getFsAssistants(IngressEvent $event): FsAttendeesInfo
    {
        $client = new Client();
        $info = new FsAttendeesInfo();
        $info->poc = [];
        $info->attendees = [];
        $crawler = $client->request('GET', $event->getLink());
        $crawler->filterXPath('//table/tbody/tr/td/a')->each(
            static function ($node) use ($info) {
                $info->poc[(string)$node->attr('class')] = (string)$node->html();
            }
        );
        $crawler->filterXPath('//table/tbody/tr/td/div')->each(
            static function ($node) use ($info) {
                static $i = 0;
                $string = $node->html();
                $string = preg_replace(
                    '#<h4>[\s()\w]+</h4>#m',
                    '',
                    $string
                );

                /**
                 * @var array<int, string> $attendees
                 */
                $attendees = explode('<br>', trim($string));
                /**
                 * @var array<int, string> $factions
                 */
                $factions = array_keys($info->poc);

                $info->attendees[(string)$factions[$i]] = $attendees;
                $i++;
            }
        );

        return $info;
    }
}
