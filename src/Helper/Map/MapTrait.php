<?php

namespace App\Helper\Map;

use App\Entity\Agent;
use Symfony\UX\Map\Bridge\Leaflet\LeafletOptions;
use Symfony\UX\Map\Bridge\Leaflet\Option\TileLayer;
use Symfony\UX\Map\Map;
use Symfony\UX\Map\Marker;
use Symfony\UX\Map\Point;

trait MapTrait
{
    public function getAgentLocationMap(Agent $agent, bool $addMarker = false): Map
    {
        $lat = (float) ($agent->getLat() ?: -1.262326);
        $lon = (float) ($agent->getLon() ?: -79.09357);
        $zoom = $agent->getLat() ? 12 : 5;

        $map = new Map('default')
            ->center(new Point($lat, $lon))
            ->zoom($zoom)
            ->options($this->getLeafletOptions());
        if ($addMarker) {
            $map->addMarker(
                new Marker(
                    position: new Point($lat, $lon),
                    title: $agent->getNickname()
                )
            );
        }

        return $map;
    }

    private function getLeafletOptions(): LeafletOptions
    {
        return new LeafletOptions()
            ->tileLayer(
                new TileLayer(
                    url: 'https://tile.openstreetmap.org/{z}/{x}/{y}.png',
                    attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
                    options: ['maxZoom' => 19]
                )
            );
    }
}
