<?php 

namespace Core;

use \Twig\Loader\FilesystemLoader;
use \Twig\Environment;

class View {

    private $loader;
    private $twig;

    public function __construct(){
        $this->loader = new FilesystemLoader(__DIR__.'/../app/views');
        
        $this->twig = new Environment($this->loader, [
            'cache' => __DIR__.'/../core/cache',
            'debug' => DEBUG
        ]);
    }

    public function render($template, $data = []){
        $template = str_replace('.', '/', $template);
        echo $this->twig->render("errors/404.html", $data);
    }
}