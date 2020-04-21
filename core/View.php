<?php

namespace Core;

use \Twig\Loader\FilesystemLoader;
use \Twig\Environment;

class View
{

    private $loader;
    private $twig;

    public function __construct() {
        $this->loader = new FilesystemLoader(__DIR__ . '/../app/views');
        $this->twig = new Environment($this->loader, [
            'cache' => __DIR__ . '/../core/cache',
            'debug' => boolval(getenv("DEBUG", false))
        ]);
    }

    public function render($template, $data = []) {
        $template = str_replace('.', '/', $template);
        echo $this->twig->render("{$template}.html", $data);
        exit;
    }

    public function load($template) {
        $template = str_replace('.', '/', $template);
        return $this->twig->load("{$template}.html");
    }
}
