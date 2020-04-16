<?php 


$loader = new \Twig\Loader\FilesystemLoader(__DIR__.'/../views');

$twig = new \Twig\Environment($loader, [
  'cache' => __DIR__.'/../bootstrap/cache',
  'debug' => DEBUG
]);


$view = $_REQUEST['REQUEST_URI'];
if(file_exists("{$_SERVER['DOCUMENT_ROOT']}/views/{$view}.html")){
  $template = $twig->load("{$view}.html");
} else {
  $template = $twig->load("errors/404.html");
}



echo $template->render(['the' => 'variables', 'go' => 'here']);