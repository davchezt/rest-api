<?php
/**
 * @author      Raiza Rhamdan (Leonardo DaVchezt) <davchezt@gmail.com>
 * @copyright   Copyright (c), 2021 Raiza Rhamdan
 * @license		MIT public license
 */

namespace app;

defined("__DAVCHEZT") or die("{ \"response\" : \"error 403\"}");

use flight\Engine;

class Plugin
{
    protected static $app;
    protected static $pluginList = [];
    protected static $plugins = [];
    
    public static function configure(Engine $app)
    {
        self::$app = $app;
    }

    public static function register($className)
    {
        // array_push(self::$pluginList, $className);
        self::$pluginList[] = $className;
    }

    public static function loadPlugins()
    {
        foreach (self::$pluginList as $name) {
            $className = 'app\Plugin\\' . $name;
            if (!class_exists($className)) {
                continue;
            }
            
            self::$plugins[$name] = new $className(self::$app);
            self::$plugins[$name]->listen();
        }
    }

    public static function trigger($event, $parameters = array())
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
        if (!isset($trace[1])) {
            return;
        }
        
        $class = new \ReflectionClass($trace[1]['class']);
        // build event format "NamespaceName_class_method_event" e.g: App_beforeStart_before, Router_Main_init_before
        if ($class->inNamespace()) {
            $namespaceName = str_replace('app\\', '', $class->getName());
            $namespaceName = str_replace('\\', '_', $namespaceName);

            $event = $namespaceName . '_' . $trace[1]['function'] . '_' . $event;
        }

        $returns = [];
        foreach (self::$plugins as $plugin) {
            // serch for method (event format) e.g handler_App_beforeStart_before, handler_Router_Main_init_before
            if (method_exists($plugin, 'handler_' . $event)) {
                $return = call_user_func_array([$plugin, 'handler_' . $event], $parameters);
                if ($return !== null) {
                    $returns[] = $return;
                }
            }
        }
        
        return $returns;
    }
    
    public static function first($event, $parameters = array())
    {
        foreach (self::$plugins as $plugin) {
            if (method_exists($plugin, 'handler_' . $event)) {
                $return = call_user_func_array([$plugin, 'handler_' . $event], $parameters);
                if ($return !== null) {
                    return $return;
                }
            }
        }
    }

    private static function sanitizeFileName($value)
    {
        return preg_replace('#(?:[\/:\\\]|\.{2,}|\\x00)#', '', $value);
    }
}
