<?php
namespace frame;

class App
{
    public $appConfig;
    protected $file;

    protected $debug;
    protected $version;

    protected $layout;
    protected $defaultContainer;
    protected $links;
    protected $scripts;
    protected $metatags;
    protected $modules;
    protected $title;

    public function __construct($basePath)
    {
        $this->file = __DIR__ . "/../etc/appConfig.xml";
        $this->links = [];
        $this->metatags = [];
        $this->debug = false;
        $this->modules = [];
    }

    protected function toBool($xml)
    {
        return strtolower((string)$xml) == "true";
    }

    protected function loadAppConfig($request)
    {
        $this->appConfig = simplexml_load_file($this->file);
        if ($this->debug) {
            $this->version = (string)time();
        } else {
            $this->version = (string)$this->appConfig->version;
        }

        $global = $this->appConfig->global;

        $page = $this->appConfig->xpath("/app/pages/page[@name='{$request->page}']");

        if (!$page) {
            header("HTTP/1.0 404 Not Found");
            exit;
        }
        $page = $page[0];

        # global parts
        $attributes = $global->attributes();

        if (isset($attributes->defaultLayout)) {
            $this->layout = (string)$attributes->defaultLayout;
        }
        if (isset($attributes->defaultContainer)) {
            $this->defaultContainer = (string)$attributes->defaultContainer;
        }

        // ability to disable globals
        if (is_null($page->attributes()->global) || ($page->attributes()->global != 'false')) {
            if ($global->metatags && $global->metatags->meta) {
                foreach ($global->metatags->meta as $meta) {
                    $this->metatags[] = $meta;
                }
            }

            if ($global->links) {
                foreach ($global->links->link as $link) {
                    $this->links[] = $link;
                }
            }

            if ($global->scripts) {
                foreach ($global->scripts->script as $script) {
                    $this->scripts[] = $script;
                }
            }

            if ($global->modules) {
                foreach ($global->modules->module as $module) {
                    $this->modules[] = $module;
                }
            }
        }

        # page specific
        $attributes = $page->attributes();

        $useGlobalCss     = isset($attributes->globalcss)  ? $this->toBool($attributes->globalcss)  : true;
        $useGlobalScripts = isset($attributes->globaljs)   ? $this->toBool($attributes->globaljs)   : true;
        $useGlobalMeta    = isset($attributes->globalmeta) ? $this->toBool($attributes->globalmeta) : true;
        $useGlobalModules = isset($attributes->globalmod)  ? $this->toBool($attributes->globalmod)  : true;

        !$useGlobalCss     and $this->links    = [];
        !$useGlobalScripts and $this->scripts  = [];
        !$useGlobalMeta    and $this->metatags = [];
        !$useGlobalModules and $this->modules  = [];

        if (isset($attributes->title)) {
            $this->title = (string)$attributes->title;
        }

        if (isset($attributes->layout)) {
            $this->layout = (string)$attributes->layout;
        }

        if ($page->metatags) {
            foreach ($page->metatags->meta as $meta) {
                $this->metatags[] = $meta;
            }
        }

        if ($page->links) {
            foreach ($page->links->link as $link) {
                $this->links[] = $link;
            }
        }

        if ($page->scripts) {
            foreach ($page->scripts->script as $script) {
                $this->scripts[] = $script;
            }
        }

        if ($page->modules) {
            foreach ($page->modules->module as $module) {
                $this->modules[] = $module;
            }
        }
    }

    public function run($request)
    {
        if ($request->controllerType === 'Index') {
            $this->processPage($request);
        } else {
            require_once __DIR__ . "/../modules/{$request->module}/{$request->controllerType}.php";

            $controllerName = $request->module . "{$request->controllerType}Controller";

            $actionMethod = "{$request->action}Action";
            $controller = new $controllerName();
            $controller->$actionMethod();
        }
    }

    private function processPage($request)
    {
        $this->loadAppConfig($request);
        $layout = new Layout($this->layout);

        foreach ($this->metatags as $meta) {
            $layout->setContainerOutput('headLinks', $meta->asXML() . "\n");
        }

        foreach ($this->links as $link) {
            $attrList = $link->attributes();

            if (isset($attrList->href)) {
                if (!substr($attrList->href, 0, 4) === 'http' && !substr($attrList->href, 0, 2) === '//') {
                    $attrList->href = $attrList->href . "?v={$this->version}";
                }
            }

            $layout->setContainerOutput('headLinks', $link->asXML() . "\n");
        }

        foreach ($this->scripts as $script) {
            # browsers don't like <script />
            $attrList = $script->attributes();
            $attrs = '';
            foreach ($attrList as $k => $v) {
                if ($k === 'src') {
                    $attrs = "$k=\"$v?v={$this->version}\"";
                } else {
                    $attrs .= "$k=\"$v\"";
                }
            }

            $output = "<script $attrs></script>";
            $layout->setContainerOutput('headScripts', "$output\n");
        }

        $layout->setContainerOutput('headTitle', "<title>{$this->title}</title>\n");

        foreach ($this->modules as $module) {
            $attributes = $module->attributes();
            $name = (string)$attributes->name;
            $action = (string)$attributes->action;
            $actionMethod = "{$action}Action";
            $controllerName = ucfirst($name) . 'Controller';
            if (isset($attributes->view)) {
                $view = (string)$attributes->view;
            } else {
                $view = $action;
            }

            if (isset($attributes->container)) {
                $container = (string)$attributes->container;
            } elseif (isset($this->defaultContainer)) {
                $container = $this->defaultContainer;
            }

            require_once __DIR__ . "/../modules/$name/index.php";
            $controller = new $controllerName();

            // Add options from appConfig.xml
            if (isset($attributes['options'])) {
                $parts = explode('&', $attributes->options);
                foreach ($parts as $part) {
                    $tuple = explode('=', $part);
                    if (count($tuple) === 2) {
                        $controller->options[ $tuple[0] ] = urldecode($tuple[1]);
                    }
                }
            }
            $controller->$actionMethod();

            $viewPath = __DIR__ . "/../modules/$name/views/$view.phtml";
            $output = $controller->render($viewPath, $name, $action);
            $layout->setContainerOutput($container, $output);
        }

        $layout->render();
    }
}

function esc($text)
{
    return htmlentities($text, ENT_COMPAT | ENT_HTML5, 'UTF-8');
}
