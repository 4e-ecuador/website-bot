<?php
$the_site = "https://dedo1911.xyz/Badges/";
$the_tag = "div"; #
$the_class = "badge";

$html = file_get_contents($the_site);
libxml_use_internal_errors(true);
$dom = new DOMDocument();
$dom->loadHTML($html);
$xpath = new DOMXPath($dom);

$urlBase = 'https://dedo1911.xyz/Badges/';
$imgBase = '';

foreach ($xpath->query('//'.$the_tag.'[contains(@class,"'.$the_class.'")]/img') as $item) {

    $original =  $item->getAttribute('data-original');
    print $original.'... ';

    $imgPath = $imgBase.$original;
    if (file_exists($imgPath)) {
        print "exists\n";
    } else {
        $urlPath = $the_site.$original;
        file_put_contents($imgPath, file_get_contents($urlPath));
        print "ok\n";
    }
}
print "\n\nFinished!\n";
