<?php

require 'vendor/autoload.php';

use Tackk\Cartographer\Sitemap;
use Tackk\Cartographer\ChangeFrequency;
use ChrisKonnertz\OpenGraph\OpenGraph;
use Phlib\String;

$app = new Slim\App();

$app->get('/', function ($request, $response, $args) {
  $og = new OpenGraph();
  $og->title('Tech note')
    ->type('website')
    ->image('http://localhost:8080/imates/polymer-logo.png')
    ->description('polyester-prototype description')
    ->siteName('Tech Note Website')
    ->url();
  $qp = html5qp('docs/index.html');
  $qp->find('title')->after($og->renderTags());

  return $response->getBody()->write($qp->html5());
});

$app->get('/index.html', function ($request, $response, $args) {
  $template = file_get_contents('docs/index.html');
  return $response->getBody()->write($template);
});

$app->get('/{year}/{month}/{day}/{title}.html', function ($request, $response, $args) {
  $page = file_get_contents("docs/source/{$args['year']}-{$args['month']}-{$args[day]}-{$args['title']}.html.markdown");
  $frontMatter = new Webuni\FrontMatter\FrontMatter();
  $document = $frontMatter->parse($page);
  $data = $document->getData();
  $content = $document->getContent();

  $og = new OpenGraph();
  $og->title($data['title'])
    ->type('article')
    ->image('http://localhost:8080/imates/polymer-logo.png')
    ->description(String::ellipsis($conent, 140))
    ->siteName('Tech Note Website')
    ->url();
  $qp = html5qp('docs/index.html');
  $qp->find('title')->after($og->renderTags());

  return $response->getBody()->write($qp->html5());
});

$app->get('/sitemap.xml', function ($request, $response, $args) {
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
  foreach ($files as $url => $lastMod) {
    $sitemap->add($url, date(DATE_ATOM, $lastMod));
  }

  $response->getBody()->write($sitemap->toString());
  return $response->withHeader('Content-Type', 'application/xml');
});

$app->run();
