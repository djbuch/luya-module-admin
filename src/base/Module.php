<?php

namespace luya\admin\base;

use yii\helpers\ArrayHelper;
use luya\base\AdminModuleInterface;

/**
 * The base Admin Module for all administration modules.
 *
 * Each administration module of LUYA must implemented this class. This class provides the ability to
 * store menu data, register translations.
 *
 * @author Basil Suter <basil@nadar.io>
 */
class Module extends \luya\base\Module implements AdminModuleInterface
{
    public $requiredComponents = ['db'];

    /**
     * @var array The config linker property can specific the configuration class for ngRest model where the key
     * is the `api` and the value is the class to the config. An array could look like this:
     *
     * ```php
     * [
     *     'api-admin-user' => \path\to\Config::className(),
     *     'api-admin-group' => '\\path\\to\\config\\GroupConfig',
     * ]
     * ```
     *
     * The ngrestConfigLinker property is build to add the ability to override the base ngrest config inside
     * a project via the module configuration inside your prep/prod config. Example for override a default ngrest
     * config inside a project config:
     *
     * ```php
     * return [
     *     // ...
     *     'modules' => [
     *         'admin' => [
     *             'class' => 'admin\Module',
     *             'ngrestConfigLinkter' => [
     *                 'api-admin-user' => \app\configs\ngrest\User::className(),
     *             ],
     *         ]
     *     ]
     * ];
     * ```
     *
     * The above example will override the api-admin-user ngrest config with your project specific config.
     */
    public $ngrestConfigLinker = [];

    /**
     * @var array Each module can have assets, all module controllers will register those assets in the view.. Valid class name to the asset e.g.
     *
     * ```php
     * public $assets = ['\app\assets\TestAsset'];
     * ```
     */
    public $assets = [];
    
    /**
     * @var array Register translations from admin modules, to make them available in javascript files trough the `i18n['yourTranslation']` object.
     */
    public $registerJsTranslation = [];
    
    /**
     * Checks if a config exist in the linked property based on the provided `$apiEndpoint`.
     *
     * @param string $apiEndpoint The identifier of an apiEndpoint. ApiEndpoints are listed in the module class.
     * @return bool|string If apiEndpoint exists in the linker property returns className, otherwhise false.
     */
    public function getLinkedNgRestConfig($apiEndpoint)
    {
        return array_key_exists($apiEndpoint, $this->ngrestConfigLinker) ? $this->ngrestConfigLinker[$apiEndpoint] : false;
    }

    /**
     * {@inheritDoc}
     * @see \luya\base\AdminModuleInterface::getMenu()
     * @return array|\luya\admin\components\AdminMenuBuilderInterface Get the menu builder object or an empty array.
     */
    public function getMenu()
    {
        return [];
    }
    
    /**
     * Extend the permission apis with none menu based items.
     *
     * Example return:
     *
     * ```php
     * return [
     *     ['api' => 'api-cms-moveblock', 'alias' => 'Move blocks'],
     * ];
     * ```
     *
     * @return array An array with an array with keys `api` and `alias`.
     */
    public function extendPermissionApis()
    {
        return [];
    }

    /**
     * Extend the permission route with a none menu route the set permissions.
     *
     * Example return:
     *
     * ```php
     * public function extendPermissionRoutes()
     * {
     *     return [
     *         ['route' => 'cmsadmin/page/create', 'alias' => 'Page Create'],
     *         ['route' => 'cmsadmin/page/update', 'alias' => 'Page Edit'],
     *     ];
     * }
     * ```
     */
    public function extendPermissionRoutes()
    {
        return [];
    }

    /**
     * Get an array with all api routes based on the menu builder.
     *
     * @return array
     */
    public function getAuthApis()
    {
        $menu = $this->getMenu();
        
        if (is_object($menu)) {
            $perm = $menu->permissionApis;
        } else {
            $perm = $this->_permissionApis;
        }
        
        return ArrayHelper::merge($this->extendPermissionApis(), $perm);
    }

    /**
     * Get an array with all routes based on the menu builder.
     *
     * @return array
     */
    public function getAuthRoutes()
    {
        $menu = $this->getMenu();
        
        if (is_object($menu)) {
            $perm = $menu->permissionRoutes;
        } else {
            $perm = $this->_permissionRoutes;
        }

        return ArrayHelper::merge($this->extendPermissionRoutes(), $perm);
    }
    
    // THE CODE BELOW WILL BE REMOVED IN 1.0.0 AND IS MAKRED AS DEPRECATED

    private $_menu = [];
    
    private $_pointers = [];
    
    private $_permissionApis = [];
    
    private $_permissionRoutes = [];
    
    protected function node($name, $icon, $template = false)
    {
        trigger_error('Deprecated method '.__METHOD__.' in '.get_called_class().', use \luya\admin\components\AdminMenuBuilder() class instead in the `getMenu()` method of your Module.', E_USER_DEPRECATED);
        
        $this->_pointers['node'] = $name;
        $this->_menu[$name] = [
            'moduleId' => $this->id,
            'template' => $template,
            'routing' => $template ? 'custom' : 'default',
            'alias' => $name,
            'icon' => $icon,
            'permissionRoute' => false,
            'permissionIsRoute' => false,
            'searchModelClass' => false,
        ];
    
        return $this;
    }
    
    protected function nodeRoute($name, $icon, $template, $route, $searchModelClass = null)
    {
        trigger_error('Deprecated method '.__METHOD__.' in '.get_called_class().', use \luya\admin\components\AdminMenuBuilder() class instead in the `getMenu()` method of your Module.', E_USER_DEPRECATED);
        
        $this->_pointers['node'] = $name;
        $this->_menu[$name] = [
            'moduleId' => $this->id,
            'template' => $template,
            'routing' => $template ? 'custom' : 'default',
            'alias' => $name,
            'icon' => $icon,
            'permissionRoute' => $route,
            'permissionIsRoute' => true,
            'searchModelClass' => $searchModelClass,
        ];
    
        $this->_permissionRoutes[] = ['route' => $route, 'alias' => $name];
    
        return $this;
    }
    
    protected function group($name)
    {
        trigger_error('Deprecated method '.__METHOD__.' in '.get_called_class().', use \luya\admin\components\AdminMenuBuilder() class instead in the `getMenu()` method of your Module.', E_USER_DEPRECATED);
        
        $this->_pointers['group'] = $name;
        $this->_menu[$this->_pointers['node']]['groups'][$name] = ['name' => $name, 'items' => []];
    
        return $this;
    }
    
    protected function itemApi($name, $route, $icon, $apiEndpoint)
    {
        trigger_error('Deprecated method '.__METHOD__.' in '.get_called_class().', use \luya\admin\components\AdminMenuBuilder() class instead in the `getMenu()` method of your Module.', E_USER_DEPRECATED);
        
        $this->_menu[$this->_pointers['node']]['groups'][$this->_pointers['group']]['items'][] = [
            'alias' => $name,
            'route' => $route,
            'icon' => $icon,
            'permssionApiEndpoint' => $apiEndpoint,
            'permissionIsRoute' => false,
            'permissionIsApi' => true,
            'searchModelClass' => false,
        ];
    
        $this->_permissionApis[] = ['api' => $apiEndpoint, 'alias' => $name];
    
        return $this;
    }
    
    protected function itemRoute($name, $route, $icon, $searchModelClass = null)
    {
        trigger_error('Deprecated method '.__METHOD__.' in '.get_called_class().', use \luya\admin\components\AdminMenuBuilder() class instead in the `getMenu()` method of your Module.', E_USER_DEPRECATED);
        
        $this->_menu[$this->_pointers['node']]['groups'][$this->_pointers['group']]['items'][] = [
            'alias' => $name,
            'route' => $route,
            'icon' => $icon,
            'permssionApiEndpoint' => null,
            'permissionIsRoute' => true,
            'permissionIsApi' => false,
            'searchModelClass' => $searchModelClass,
        ];
    
        $this->_permissionRoutes[] = ['route' => $route, 'alias' => $name];
    
        return $this;
    }
    
    public function menu()
    {
        trigger_error('Deprecated method '.__METHOD__.' in '.get_called_class().', use \luya\admin\components\AdminMenuBuilder() class instead in the `getMenu()` method of your Module.', E_USER_DEPRECATED);
        
        return $this->_menu;
    }
}
