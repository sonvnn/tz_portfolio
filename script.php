<?php
/*------------------------------------------------------------------------

# TZ Portfolio Extension

# ------------------------------------------------------------------------

# author    DuongTVTemPlaza

# copyright Copyright (C) 2012 templaza.com. All Rights Reserved.

# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL

# Websites: http://www.templaza.com

# Technical Support:  Forum - http://templaza.com/Forum

-------------------------------------------------------------------------*/

// No direct access
defined('_JEXEC') or die;

jimport('joomla.installer.installer');
//jimport('joomla.base.adapterinstance');

class com_tz_portfolioInstallerScript{


    function postflight($type, $parent){


        $manifest   = $parent -> get('manifest');
        $params     = new JRegistry();

        $query  = 'SELECT params FROM #__extensions'
                  .' WHERE `type`="component" AND `name`="'.strtolower($manifest -> name).'"';
        $db     = &JFactory::getDbo();
        $db -> setQuery($query);
        $db -> query();

        $paramNames = array();

        if($db -> loadResult()){
            $params -> loadString($db ->loadResult());
            if(count($params -> toArray())>0){
                foreach($params -> toArray() as $key => $val){
                    $paramNames[]   = $key;
                }
            }
        }

        $fields     = $manifest -> xPath('config/fields/field');

        foreach($fields as $field){
            $attribute  = $field -> attributes();
            if(!in_array((string)$attribute -> name,$paramNames)){
                if($attribute -> multiple == 'true'){
                    $arr   = array();
                    $options    = $manifest -> xPath('config/fields/field/option');
                    foreach($options as $option){
                        $opAttr = $option -> attributes();
                        $arr[]  = (string)$opAttr -> value;
                    }

                    $params -> set((string) $attribute -> name,$arr);
                }
                else
                    $params -> set((string)$attribute -> name,(string)$attribute ->default);
            }
        }


        $params = $params -> toString();

        $query  = 'UPDATE #__extensions SET `params`=\''.$params.'\''
                  .' WHERE `name`="'.strtolower($manifest -> name).'"'
                  .' AND `type`="component"';

        $db -> setQuery($query);
        $db -> query();
        $this -> UpdateSql();

        JFactory::getLanguage() -> load('com_tz_portfolio');


        //Create folder
        $mediaFolder    = 'tz_portfolio';
        $mediaFolderPath    = JPATH_SITE.'/media/'.$mediaFolder;
        $article    = 'article';
        $cache      = 'cache';
        $src        = 'src';

        if(!JFolder::exists($mediaFolderPath)){
            JFolder::create($mediaFolderPath);
        }
        if(!JFile::exists($mediaFolderPath.'/index.html')){
            JFile::write($mediaFolderPath.'/index.html',htmlspecialchars_decode('<!DOCTYPE html><title></title>'));
        }
        if(!JFolder::exists($mediaFolderPath.'/'.$article)){
            JFolder::create($mediaFolderPath.'/'.$article);
        }
        if(!JFile::exists($mediaFolderPath.'/'.$article.'/'.'index.html')){
            JFile::write($mediaFolderPath.'/'.$article.'/'.'index.html',htmlspecialchars_decode('<!DOCTYPE html><title></title>'));
        }
        if(!JFolder::exists($mediaFolderPath.'/'.$article.'/'.$cache)){
            JFolder::create($mediaFolderPath.'/'.$article.'/'.$cache);
        }
        if(!JFile::exists($mediaFolderPath.'/'.$article.'/'.$cache.'/'.'index.html')){
            JFile::write($mediaFolderPath.'/'.$article.'/'.$cache.'/'.'index.html',htmlspecialchars_decode('<!DOCTYPE html><title></title>'));
        }
        if(!JFolder::exists($mediaFolderPath.'/'.$article.'/'.$src)){
            JFolder::create($mediaFolderPath.'/'.$article.'/'.$src);
        }
        if(!JFile::exists($mediaFolderPath.'/'.$article.'/'.$src.'/'.'index.html')){
            JFile::write($mediaFolderPath.'/'.$article.'/'.$src.'/'.'index.html',htmlspecialchars_decode('<!DOCTYPE html><title></title>'));
        }
        //Install plugins
        $status = new stdClass;
        $status->modules = array();
        $src = $parent->getParent()->getPath('source');

        if(version_compare( JVERSION, '1.6.0', 'ge' )) {
            $modules = &$parent->getParent()->manifest->xpath('modules/module');

            foreach($modules as $module){
                $result = null;
                $mname = $module->attributes() -> module;
                $client = $module->attributes() -> client;
                if(is_null($client)) $client = 'site';
                ($client=='administrator')? $path=$src.'/'.'administrator'.'/'.'modules'.'/'.$mname: $path = $src.'/'.'modules'.'/'.$mname;
                $installer = new JInstaller();
                $result = $installer->install($path);
                $status->modules[] = array('name'=>$mname,'client'=>$client, 'result'=>$result);
            }

            $plugins = &$parent->getParent()->manifest->xpath('plugins/plugin');
            foreach($plugins as $plugin){
                $result = null;
                $folder = null;
                $pname  = $plugin->attributes() -> plugin;
                $group  = $plugin->attributes() -> group;
                $folder = $plugin -> attributes() -> folder;
                if(isset($folder)){
                    $folder = $plugin -> attributes() -> folder;
                }
                $path   = $src.'/'.'plugins'.'/'.$group.'/'.$folder;

                $installer = new JInstaller();
                $result = $installer->install($path);
                $status->plugins[] = array('name'=>$pname,'group'=>$group, 'result'=>$result);
            }

            $query  = 'UPDATE #__extensions SET `enabled`=1 WHERE `type`="plugin" AND `element`="tz_portfolio" AND `folder`="system"';
            $db -> setQuery($query);
            $db -> query();

            $query  = 'UPDATE #__extensions SET `enabled`=1 WHERE `type`="plugin" AND `element`="tz_portfolio" AND `folder`="user"';
            $db -> setQuery($query);
            $db -> query();

            $query  = 'UPDATE #__extensions SET `enabled`=1 WHERE `type`="plugin" AND `element`="tz_portfolio_comment" AND `folder`="content"';
            $db -> setQuery($query);
            $db -> query();

            $query  = 'UPDATE #__extensions SET `enabled`=1 WHERE `type`="plugin" AND `element`="tz_portfolio_vote" AND `folder`="content"';
            $db -> setQuery($query);
            $db -> query();

            $query  = 'UPDATE #__extensions SET `enabled`=1 WHERE `type`="plugin" AND `element`="tz_portfolio_content" AND `folder`="search"';
            $db -> setQuery($query);
            $db -> query();

            $query  = 'UPDATE #__extensions SET `enabled`=1 WHERE `type`="plugin" AND `element`="tz_portfolio_categories" AND `folder`="search"';
            $db -> setQuery($query);
            $db -> query();

            $query  = 'UPDATE #__extensions SET `enabled`=0 WHERE `type`="plugin" AND `element`="vote" AND `folder`="content"';
            $db -> setQuery($query);
            $db -> query();

            $query  = 'UPDATE #__extensions SET `enabled`=0 WHERE `type`="plugin" AND `element`="content" AND `folder`="search"';
            $db -> setQuery($query);
            $db -> query();

            $query  = 'UPDATE #__extensions SET `enabled`=0 WHERE `type`="plugin" AND `element`="categories" AND `folder`="search"';
            $db -> setQuery($query);
            $db -> query();
        }
        $this -> installationResult($status);
    }
    function uninstall($parent){
        $mediaFolder    = 'tz_portfolio';
        $mediaFolderPath    = JPATH_SITE.'/'.'media'.'/'.$mediaFolder;
        if(JFolder::exists($mediaFolderPath)){
            JFolder::delete($mediaFolderPath);
        }
        $imageFolderPath    = JPATH_SITE.'/'.'images'.'/'.$mediaFolder;
        if(JFolder::exists($imageFolderPath)){
            JFolder::delete($imageFolderPath);
        }

        $status = new stdClass();
        $status->modules = array ();
        $status->plugins = array ();

        $modules = & $parent -> getParent() -> manifest -> xpath('modules/module');
        $plugins = & $parent -> getParent() -> manifest -> xpath('plugins/plugin');

        $result = null;
        if($modules){
            foreach($modules as $module){
                $mname = (string)$module->attributes() -> module;
                $client = (string)$module->attributes() -> client;

                $db = & JFactory::getDBO();
                $query = "SELECT `extension_id` FROM #__extensions WHERE `type`='module' AND `element` = ".$db->Quote($mname)."";
                $db->setQuery($query);
                $IDs = $db->loadColumn();
                if (count($IDs)) {
                    foreach ($IDs as $id) {
                        $installer = new JInstaller;
                        $result = $installer->uninstall('module', $id);
                    }
                }
                $status->modules[] = array ('name'=>$mname, 'client'=>$client, 'result'=>$result);
            }
        }

        if($plugins){
            foreach ($plugins as $plugin) {

                $pname = (string)$plugin->attributes() -> plugin;
                $pgroup = (string)$plugin->attributes() -> group;

                $db = & JFactory::getDBO();
                $query = "SELECT `extension_id` FROM #__extensions WHERE `type`='plugin' AND `element` = "
                         .$db->Quote($pname)." AND `folder` = ".$db->Quote($pgroup);
                $db->setQuery($query);
                $IDs = $db->loadColumn();
                if (count($IDs)) {
                    foreach ($IDs as $id) {
                        $installer = new JInstaller;
                        $result = $installer->uninstall('plugin', $id);
                    }
                }
                $status->plugins[] = array ('name'=>$pname, 'group'=>$pgroup, 'result'=>$result);
            }
        }
        $this -> uninstallationResult($status);
    }

    function UpdateSql(){
        $db     = &JFactory::getDbo();
        $arr    = null;
        $fields = $db -> getTableColumns('#__tz_portfolio_xref_content');
        if(!array_key_exists('gallery',$fields)){
            $arr[]  = 'ADD `gallery` TEXT NOT NULL';
        }
        if(!array_key_exists('gallerytitle',$fields)){
            $arr[]  = 'ADD `gallerytitle` TEXT NOT NULL';
        }
        if(!array_key_exists('video',$fields)){
            $arr[]  = 'ADD `video` TEXT NOT NULL';
        }
        if(!array_key_exists('videotitle',$fields)){
            $arr[]  = 'ADD `videotitle` TEXT NOT NULL';
        }
        if(!array_key_exists('type',$fields)){
            $arr[]  = 'ADD `type` VARCHAR(25)';
        }
        if(!array_key_exists('videothumb',$fields)){
            $arr[]  = 'ADD `videothumb` TEXT';
        }
        if(!array_key_exists('images_hover',$fields)){
            $arr[]  = 'ADD `images_hover` TEXT';
        }
        if($arr && count($arr)>0){
            $arr    = implode(',',$arr);
            if($arr){
                $query  = 'ALTER TABLE `#__tz_portfolio_xref_content` '.$arr;
                $db -> setQuery($query);
                $db -> query();
            }
        }

        //TZ Categories
        $fields = $db -> getTableColumns('#__tz_portfolio_categories');
        if(!array_key_exists('images',$fields)){
            $query  = 'ALTER TABLE `#__tz_portfolio_categories` ADD `images` TEXT NOT NULL';
            $db -> setQuery($query);
            $db -> query();
        }

        // extra fields
        $arr    = null;
        $fields = $db -> getTableColumns('#__tz_portfolio_fields');
        if(!array_key_exists('default_value',$fields)){
            $arr[]  = 'ADD `default_value` TEXT NOT NULL';
        }
        if($arr && count($arr)>0){
            $arr    = implode(',',$arr);
            if($arr){
                $query  = 'ALTER TABLE `#__tz_portfolio_fields` '.$arr;
                $db -> setQuery($query);
                $db -> query();
            }
        }
    }

    public function installationResult($status){
        JFactory::getLanguage() -> load('com_tz_portfolio');
        $rows   = 0;
?>
        <h2><?php echo JText::_('COM_TZ_PORTFOLIO_HEADING_INSTALL_STATUS'); ?></h2>
        <table class="table table-striped table-condensed">
            <thead>
                <tr>
                    <th class="title" colspan="2"><?php echo JText::_('COM_TZ_PORTFOLIO_EXTENSION'); ?></th>
                    <th width="30%"><?php echo JText::_('COM_TZ_PORTFOLIO_STATUS'); ?></th>
                </tr>
            </thead>
            <tfoot>
                <tr>
                    <td colspan="3"></td>
                </tr>
            </tfoot>
            <tbody>
                <tr class="row0">
                    <td class="key" colspan="2"><?php echo JText::_('COM_TZ_PORTFOLIO').' '.JText::_('COM_TZ_PORTFOLIO_COMPONENT'); ?></td>
                    <td><strong><?php echo JText::_('COM_TZ_PORTFOLIO_INSTALLED'); ?></strong></td>
                </tr>
                <?php if (count($status->modules)): ?>
                <tr>
                    <th><?php echo JText::_('COM_TZ_PORTFOLIO_MODULE'); ?></th>
                    <th><?php echo JText::_('COM_TZ_PORTFOLIO_CLIENT'); ?></th>
                    <th></th>
                </tr>
                <?php foreach ($status->modules as $module): ?>
                <tr class="row<?php echo (++ $rows % 2); ?>">
                    <td class="key"><?php echo $module['name']; ?></td>
                    <td class="key"><?php echo ucfirst($module['client']); ?></td>
                    <td><strong><?php echo ($module['result'])?JText::_('COM_TZ_PORTFOLIO_INSTALLED'):JText::_('COM_TZ_PORTFOLIO_NOT_INSTALLED'); ?></strong></td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>

                <?php if (count($status->plugins)): ?>
                <tr>
                    <th><?php echo JText::_('COM_TZ_PORTFOLIO_PLUGIN'); ?></th>
                    <th><?php echo JText::_('COM_TZ_PORTFOLIO_GROUP'); ?></th>
                    <th></th>
                </tr>
                <?php foreach ($status->plugins as $plugin): ?>
                <tr class="row<?php echo (++ $rows % 2); ?>">
                    <td class="key"><?php echo ucfirst($plugin['name']); ?></td>
                    <td class="key"><?php echo ucfirst($plugin['group']); ?></td>
                    <td><strong><?php echo ($plugin['result'])?JText::_('COM_TZ_PORTFOLIO_INSTALLED'):JText::_('COM_TZ_PORTFOLIO_NOT_INSTALLED'); ?></strong></td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>

                <?php if (count($status->languages)): ?>
                <tr>
                    <th><?php echo JText::_('COM_TZ_PORTFOLIO_LANGUAGES'); ?></th>
                    <th><?php echo JText::_('COM_TZ_PORTFOLIO_COUNTRY'); ?></th>
                    <th></th>
                </tr>
                <?php foreach ($status->languages as $language): ?>
                <tr class="row<?php echo (++ $rows % 2); ?>">
                    <td class="key"><?php echo ucfirst($language['language']); ?></td>
                    <td class="key"><?php echo ucfirst($language['country']); ?></td>
                    <td><strong><?php echo ($language['result'])?JText::_('COM_TZ_PORTFOLIO_INSTALLED'):JText::_('COM_TZ_PORTFOLIO_NOT_INSTALLED'); ?></strong></td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>

            </tbody>
        </table>
<?php
    }
    function uninstallationResult($status){
        JFactory::getLanguage()->load('com_tz_portfolio');
        $rows   = 0;
?>
        <h2><?php echo JText::_('COM_TZ_PORTFOLIO_HEADING_REMOVE_STATUS'); ?></h2>
        <table class="table table-striped table-condensed">
            <thead>
                <tr>
                    <th class="title" colspan="2"><?php echo JText::_('COM_TZ_PORTFOLIO_EXTENSION'); ?></th>
                    <th width="30%"><?php echo JText::_('COM_TZ_PORTFOLIO_STATUS'); ?></th>
                </tr>
            </thead>
            <tfoot>
                <tr>
                    <td colspan="3"></td>
                </tr>
            </tfoot>
            <tbody>
                <tr class="row0">
                    <td class="key" colspan="2"><?php echo JText::_('COM_TZ_PORTFOLIO').' '.JText::_('COM_TZ_PORTFOLIO_COMPONENT'); ?></td>
                    <td><strong><?php echo JText::_('COM_TZ_PORTFOLIO_REMOVED'); ?></strong></td>
                </tr>
                <?php if (count($status->modules)): ?>
                <tr>
                    <th><?php echo JText::_('COM_TZ_PORTFOLIO_MODULE'); ?></th>
                    <th><?php echo JText::_('COM_TZ_PORTFOLIO_CLIENT'); ?></th>
                    <th></th>
                </tr>
                <?php foreach ($status->modules as $module): ?>
                <tr class="row<?php echo (++ $rows % 2); ?>">
                    <td class="key"><?php echo $module['name']; ?></td>
                    <td class="key"><?php echo ucfirst($module['client']); ?></td>
                    <td><strong><?php echo ($module['result'])?JText::_('COM_TZ_PORTFOLIO_REMOVED'):JText::_('COM_TZ_PORTFOLIO_NOT_REMOVED'); ?></strong></td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>

                <?php if (count($status->plugins)): ?>
                <tr>
                    <th><?php echo JText::_('COM_TZ_PORTFOLIO_PLUGIN'); ?></th>
                    <th><?php echo JText::_('COM_TZ_PORTFOLIO_GROUP'); ?></th>
                    <th></th>
                </tr>
                <?php foreach ($status->plugins as $plugin): ?>
                <tr class="row<?php echo (++ $rows % 2); ?>">
                    <td class="key"><?php echo ucfirst($plugin['name']); ?></td>
                    <td class="key"><?php echo ucfirst($plugin['group']); ?></td>
                    <td><strong><?php echo ($plugin['result'])?JText::_('COM_TZ_PORTFOLIO_REMOVED'):JText::_('COM_TZ_PORTFOLIO_NOT_REMOVED'); ?></strong></td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>

                <?php if (count($status->languages)): ?>
                <tr>
                    <th><?php echo JText::_('COM_TZ_PORTFOLIO_LANGUAGES'); ?></th>
                    <th><?php echo JText::_('COM_TZ_PORTFOLIO_COUNTRY'); ?></th>
                    <th></th>
                </tr>
                <?php foreach ($status->languages as $language): ?>
                <tr class="row<?php echo (++ $rows % 2); ?>">
                    <td class="key"><?php echo ucfirst($language['language']); ?></td>
                    <td class="key"><?php echo ucfirst($language['country']); ?></td>
                    <td><strong><?php echo ($language['result'])?JText::_('COM_TZ_PORTFOLIO_REMOVED'):JText::_('COM_TZ_PORTFOLIO_NOT_REMOVED'); ?></strong></td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
<?php
    }
}
?>