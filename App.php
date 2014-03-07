<?php

namespace frame;

class App
{
    public $appConfig;
    protected $file;

    protected $layout;
    protected $links;
    protected $scripts;
    protected $metatags;
    protected $modules;
    protected $title;

    public function __construct($basePath)
    {
        $this->file = "$basePath/website/etc/appConfig.xml";
    }

    protected function loadAppConfig($request)
    {
        $this->appConfig = simplexml_load_file($this->file);
        $global = $this->appConfig->global;
        $page = $this->appConfig->xpath("/app/pages/page[@name='{$request->page}']");

        if (!$page) {
            echo '404';
            exit;
        }
        $page = $page[0];

        # global parts
        $attributes = $global->attributes();

        if (isset($attributes['layout'])) {
            $this->layout = $attributes['layout'];
        }

        foreach ($global->metatags->meta as $meta) {
            $this->metatags[] = $meta;
        }

        foreach ($global->links->link as $link) {
            $this->links[] = $link;
        }

        foreach ($global->scripts->script as $script) {
            $this->scripts[] = $script;
        }

        foreach ($global->modules->module as $module) {
            $this->modules[] = $module;
        }

        # page specific
        $attributes = $page->attributes();
        if (isset($attributes['title'])) {
            $this->title = $attributes['title'];
        }

        if (isset($attributes['layout'])) {
            $this->layout = $attributes['layout'];
        }

        if ($page->metatags) {
            foreach ($page->metatags->meta as $meta) {
                $this->metatags[] = $meta;
            }
        }

        foreach ($page->links->link as $link) {
            $this->links[] = $link;
        }

        foreach ($page->scripts->script as $script) {
            $this->scripts[] = $script;
        }

        foreach ($page->modules->module as $module) {
            $this->modules[] = $module;
        }

    }

    public function run($request)
    {
        $this->loadAppConfig($request);
        $layout = new Layout($this->layout);

        foreach ($this->metatags as $meta) {
            $layout->setContainerOutput('headLinks', $meta->asXML() . "\n");
        }

        foreach ($this->links as $link) {
            $attributes = $link->attributes();
            $href = $attributes['href'];
            $layout->setContainerOutput('headLinks', $link->asXML() . "\n");
        }

        foreach ($this->scripts as $script) {
            $attributes = $script->attributes();
            $src = $attributes['src'];
            $layout->setContainerOutput('headLinks', $script->asXML(). "\n");
        }

        $layout->setContainerOutput('headTitle', "<title>{$this->title}</title>\n");

        foreach ($this->modules as $module) {
            $attributes = $module->attributes();
            $name = $attributes['name'];
            $action = $attributes['action'];
            $actionMethod = "{$action}Action";
            $controllerName = ucfirst($name) . 'Controller';
            if (isset($attributes['view'])) {
                $view = $attributes['view'];
            } else {
                $view = $action;
            }
            $container = (string)$attributes['container'];

            require_once '../modules/' . $attributes['name'] . '/index.php';

            $controller = new $controllerName();
            $controller->$actionMethod();
            $output = $controller->render("../modules/$name/views/$view.phtml", $name, $action);
            $layout->setContainerOutput($container, $output);
        }

        $layout->render();
    }
}
