<?php
namespace Schedule\Modules\Authority;

use Phalcon\DiInterface;
use Phalcon\Loader;
use Phalcon\Mvc\View;
use Phalcon\Mvc\View\Engine\Php as PhpEngine;
use Phalcon\Mvc\ModuleDefinitionInterface;
use Phalcon\Config\Adapter\Ini;
use Phalcon\Config;

class Module implements ModuleDefinitionInterface
{
    /**
     * Registers an autoloader related to the module
     *
     * @param DiInterface $di
     */
    public function registerAutoloaders(DiInterface $di = null)
    {
        /**
         * Try to load local configuration
         */
        if (file_exists(__DIR__ . '/config/config.ini')) {

            $config = $di['config'];

            $override = new Ini(__DIR__ . '/config/config.ini');

            if ($config instanceof Config) {
                $config->merge($override);
            } else {
                $config = $override;
            }
        }
        $loader = new Loader();
        $config = $di->getConfig();
        $module_dir=$config->get('application')->modulesDir;

        $loader->registerNamespaces([
            'Schedule\Modules\Authority\Controllers' => BASE_PATH.$module_dir .  $config->get('authority')->controllersDir,
            'Schedule\Modules\Authority\Models'      => BASE_PATH.$module_dir .  $config->get('authority')->modelsDir,
            'Schedule\Modules\Authority\Forms'      =>  BASE_PATH.$module_dir .  $config->get('authority')->formDir
        ]);

        $loader->register();
    }

    /**
     * Registers services related to the module
     *
     * @param DiInterface $di
     */
    public function registerServices(DiInterface $di)
    {


        /**
         * Setting up the view component
         */
        $di['view'] = function () {
            $config = $this->getConfig();

            $view = new View();
            $view->setViewsDir(BASE_PATH.$config->get('application')->modulesDir.$config->get('authority')->viewsDir);
            $view->setLayoutsDir(BASE_PATH.$config->get('application')->modulesDir.$config->get('authority')->viewsDir."layouts/");
          //  echo $view->getLayoutsDir();die;
            $view->registerEngines([
                '.volt'  => 'voltShared',
                '.phtml' => PhpEngine::class
            ]);

            return $view;
        };

        /**
         * Database connection is created based in the parameters defined in the configuration file
         */
        $di['db'] = function () {
            $config = $this->getConfig();

            $dbConfig = $config->database->toArray();

            $dbAdapter = '\Phalcon\Db\Adapter\Pdo\\' . $dbConfig['adapter'];
            unset($config['adapter']);

            return new $dbAdapter($dbConfig);
        };
    }
}
