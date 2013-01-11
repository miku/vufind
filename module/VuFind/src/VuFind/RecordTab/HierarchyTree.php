<?php
/**
 * HierarchyTree tab
 *
 * PHP version 5
 *
 * Copyright (C) Villanova University 2010.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License version 2,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * @category VuFind2
 * @package  RecordTabs
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/vufind2:record_tabs Wiki
 */
namespace VuFind\RecordTab;
use VuFind\Config\Reader as ConfigReader;

/**
 * HierarchyTree tab
 *
 * @category VuFind2
 * @package  RecordTabs
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/vufind2:record_tabs Wiki
 */
class HierarchyTree extends AbstractBase
{
    /**
     * Tree data
     *
     * @var array
     */
    protected $treeList = null;

    /**
     * Configuration
     *
     * @var \Zend\Config\Config
     */
    protected $config = null;

    /**
     * Get the VuFind configuration.
     *
     * @return \Zend\Config\Config
     */
    protected function getConfig()
    {
        if (null === $this->config) {
            $this->config = ConfigReader::getConfig();
        }
        return $this->config;
    }

    /**
     * Get the on-screen description for this tab.
     *
     * @return string
     */
    public function getDescription()
    {
        return 'hierarchy_tree';
    }

    /**
     * Is this tab active?
     *
     * @return bool
     */
    public function isActive()
    {
        $trees = $this->getTreeList();
        return !empty($trees);
    }

    /**
     * Get the ID of the active tree (false if none)
     *
     * @return string|bool
     */
    public function getActiveTree()
    {
        $treeList = $this->getTreeList();
        $hierarchySetting = ($request = $this->getRequest())
            ? $request->getPost('hierarchy', $request->getQuery('hierarchy', false))
            : false;
        if (count($treeList) == 1 || !$hierarchySetting) {
            $keys = array_keys($treeList);
            return $keys[0];
        } else {
            return $hierarchySetting;
        }
    }

    /**
     * Get an array of tree data
     *
     * @return array
     */
    public function getTreeList()
    {
        if (null === $this->treeList) {
            $this->treeList
                = $this->getRecordDriver()->tryMethod('getHierarchyTrees');
            if (null === $this->treeList) {
                $this->treeList = array();
            }
        }
        return $this->treeList;
    }

    /**
     * Should we display the full tree, or just a partial tree?
     *
     * @return bool
     */
    public function getFullHierarchySetting()
    {
        // Get hierarchy driver:
        $recordDriver = $this->getRecordDriver();
        $hierarchyDriver = $recordDriver->tryMethod('getHierarchyDriver');

        // We need a driver to proceed:
        if (is_object($hierarchyDriver)) {
            // No setting, or true setting -- use default setting:
            $settings = $hierarchyDriver->getTreeSettings();
            if (!isset($settings['fullHierarchyRecordView'])
                || $settings['fullHierarchyRecordView']
            ) {
                return true;
            }
        }

        // Currently displaying top of tree?  Disable partial hierarchy:
        if ($this->getActiveTree() == $recordDriver->getUniqueId()) {
            return true;
        }

        // Only if we got this far is it appropriate to use a partial hierarchy:
        return false;
    }

    /**
     * Render a hierarchy tree
     *
     * @param string $baseUrl Base URL to use in links within tree
     * @param string $id      Hierarchy ID (omit to use active tree)
     * @param string $context Context for use by renderer
     *
     * @return string
     */
    public function renderTree($baseUrl, $id = null, $context = 'Record')
    {
        $id = (null === $id) ? $this->getActiveTree() : $id;
        $recordDriver = $this->getRecordDriver();
        $hierarchyDriver = $recordDriver->tryMethod('getHierarchyDriver');
        if (is_object($hierarchyDriver)) {
            $tree = $hierarchyDriver->render($recordDriver, $context, 'List', $id);
            return str_replace(
                '%%%%VUFIND-BASE-URL%%%%', rtrim($baseUrl, '/'), $tree
            );
        }
        return '';
    }

    /**
     * Is tree searching active?
     *
     * @return bool
     */
    public function searchActive()
    {
        $config = $this->getConfig();
        return (!isset($config->Hierarchy->search) || $config->Hierarchy->search);
    }

    /**
     * Get the tree search result limit.
     *
     * @return int
     */
    public function getSearchLimit()
    {
        $config = $this->getConfig();
        return isset($config->Hierarchy->treeSearchLimit)
            ? $config->Hierarchy->treeSearchLimit : -1;
    }
}