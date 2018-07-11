<?php

require 'vendor/autoload.php';

use Tackk\Cartographer\Sitemap;
use Tackk\Cartographer\ChangeFrequency;
use Phlib\String;

$files = [];
$sitemap = new Tackk\Cartographer\Sitemap();
if ($dh = opendir('docs/source')) {
  while (($file = readdir($dh)) !== false) {
    if (preg_match('/^(\d{4})-(\d{2})-(\d{2})-(.*)\.html.markdown$/', $file, $matches)) {
      $files["http://localhost:8080/{$matches[1]}/{$matches[2]}/{$matches[3]}/{$matches[4]}.html"] = filemtime("docs/source/{$file}");
    }
  }
  closedir($dh);
}
asort($files);
date_default_timezone_set('GMT');
foreach ($files as $url => $lastMod) {
  $sitemap->add($url, date(DATE_ATOM, $lastMod));
}

file_put_contents('docs/sitemap.xml', $sitemap->toString());
