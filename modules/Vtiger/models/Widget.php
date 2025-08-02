<?php
/*************************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * Modifications and additions by IT-Solutions4You (ITS4YOU) are Copyright (c) IT-Solutions4You s.r.o.
 *
 * These contributions are licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

/**
 * Vtiger Widget Model Class
 */
class Vtiger_Widget_Model extends Vtiger_Base_Model
{
    public function getWidth()
    {
        $largerSizedWidgets = ['GroupedBySalesPerson', 'PipelinedAmountPerSalesPerson', 'GroupedBySalesStage', 'Funnel Amount'];
        $title = $this->getName();
        if (in_array($title, $largerSizedWidgets)) {
            $this->set('width', '2');
        }

        $width = $this->get('width');
        if (empty($width)) {
            $this->set('width', '1');
        }

        return $this->get('width');
    }

    public function getHeight()
    {
        //Special case for History widget
        $title = $this->getTitle();
        if ($title == 'History') {
            $this->set('height', '2');
        }
        $height = $this->get('height');
        if (empty($height)) {
            $this->set('height', '1');
        }

        return $this->get('height');
    }

    public function getSizeX()
    {
        $size = $this->get('size');
        if ($size) {
            $size = Zend_Json::decode(decode_html($size));
            $width = intval($size['sizex']);
            $this->set('width', $width);

            return $width;
        }

        return $this->getWidth();
    }

    public function getSizeY()
    {
        $size = $this->get('size');
        if ($size) {
            $size = Zend_Json::decode(decode_html($size));
            $height = intval($size['sizey']);
            $this->set('height', $height);

            return $height;
        }

        return $this->getHeight();
    }

    public function getPositionCol($default = 0)
    {
        $position = $this->get('position');
        if ($position) {
            $position = Zend_Json::decode(decode_html($position));

            return intval($position['col']);
        }

        return $default;
    }

    public function getPositionRow($default = 0)
    {
        $position = $this->get('position');
        if ($position) {
            $position = Zend_Json::decode(decode_html($position));

            return intval($position['row']);
        }

        return $default;
    }

    /**
     * Function to get the url of the widget
     * @return <String>
     */
    public function getUrl()
    {
        $url = decode_html($this->get('linkurl')) . '&linkid=' . $this->get('linkid');
        $widgetid = $this->has('widgetid') ? $this->get('widgetid') : $this->get('id');
        if ($widgetid) {
            $url .= '&widgetid=' . $widgetid;
        }

        return $url;
    }

    /**
     *  Function to get the Title of the widget
     */
    public function getTitle()
    {
        $title = $this->get('title');
        if (!$title) {
            $title = $this->get('linklabel');
        }

        return $title;
    }

    public function getName()
    {
        $widgetName = $this->get('name');
        if (empty($widgetName)) {
            $linkUrl = decode_html($this->getUrl());
            preg_match('/name=[a-zA-Z]+/', $linkUrl, $matches);
            $matches = explode('=', $matches[0]);
            $widgetName = $matches[1];
            $this->set('name', $widgetName);
        }

        return $widgetName;
    }

    /**
     * Function to get the instance of Vtiger Widget Model from the given array of key-value mapping
     *
     * @param <Array> $valueMap
     *
     * @return Vtiger_Widget_Model instance
     */
    public static function getInstanceFromValues($valueMap)
    {
        $self = new self();
        $self->setData($valueMap);

        return $self;
    }

    public static function getInstance($linkId, $userId)
    {
        $db = PearDatabase::getInstance();
        $result = $db->pquery(
            'SELECT * FROM vtiger_module_dashboard_widgets
			INNER JOIN vtiger_links ON vtiger_links.linkid = vtiger_module_dashboard_widgets.linkid
			WHERE linktype = ? AND vtiger_links.linkid = ? AND userid = ?',
            ['DASHBOARDWIDGET', $linkId, $userId]
        );

        $self = new self();
        if ($db->num_rows($result)) {
            $row = $db->query_result_rowdata($result, 0);
            $self->setData($row);
        }

        return $self;
    }

    public static function updateWidgetPosition($position, $linkId, $widgetId, $userId)
    {
        if (!$linkId && !$widgetId) {
            return;
        }

        $db = PearDatabase::getInstance();
        $sql = 'UPDATE vtiger_module_dashboard_widgets SET position=? WHERE userid=?';
        $params = [$position, $userId];
        if ($linkId) {
            $sql .= ' AND linkid = ?';
            $params[] = $linkId;
        } elseif ($widgetId) {
            $sql .= ' AND id = ?';
            $params[] = $widgetId;
        }
        $db->pquery($sql, $params);
    }

    public static function updateWidgetSize($size, $linkId, $widgetId, $userId, $tabId)
    {
        if ($linkId || $widgetId) {
            $db = PearDatabase::getInstance();
            $sql = 'UPDATE vtiger_module_dashboard_widgets SET size=? WHERE userid=?';
            $params = [$size, $userId];
            if ($linkId) {
                $sql .= ' AND linkid=?';
                $params[] = $linkId;
            } elseif ($widgetId) {
                $sql .= ' AND id=?';
                $params[] = $widgetId;
            }
            $sql .= ' AND dashboardtabid=?';
            $params[] = $tabId;
            $db->pquery($sql, $params);
        }
    }

    public static function getInstanceWithWidgetId($widgetId, $userId)
    {
        $db = PearDatabase::getInstance();
        $result = $db->pquery(
            'SELECT * FROM vtiger_module_dashboard_widgets
			INNER JOIN vtiger_links ON vtiger_links.linkid = vtiger_module_dashboard_widgets.linkid
			WHERE linktype = ? AND vtiger_module_dashboard_widgets.id = ? AND userid = ?',
            ['DASHBOARDWIDGET', $widgetId, $userId]
        );

        $self = new self();
        if ($db->num_rows($result)) {
            $row = $db->query_result_rowdata($result, 0);
            $self->setData($row);
        }

        return $self;
    }

    /**
     * Function to add a widget from the Users Dashboard
     */
    public function add()
    {
        $db = PearDatabase::getInstance();

        $tabid = 1;
        if ($this->get("tabid")) {
            $tabid = $this->get("tabid");
        }

        $sql = 'SELECT id FROM vtiger_module_dashboard_widgets WHERE linkid = ? AND userid = ? AND dashboardtabid=?';
        $params = [$this->get('linkid'), $this->get('userid'), $tabid];

        $filterid = $this->get('filterid');
        if (!empty($filterid)) {
            $sql .= ' AND filterid = ?';
            $params[] = $this->get('filterid');
        }

        $result = $db->pquery($sql, $params);

        if (!$db->num_rows($result)) {
            $db->pquery(
                'INSERT INTO vtiger_module_dashboard_widgets(linkid, userid, filterid, title, data,dashboardtabid) VALUES(?,?,?,?,?,?)',
                [$this->get('linkid'), $this->get('userid'), $this->get('filterid'), $this->get('title'), Zend_Json::encode($this->get('data')), $tabid]
            );
            $this->set('id', $db->getLastInsertID());
        } elseif ($this->has('data')) {
            $db->pquery(
                'INSERT INTO vtiger_module_dashboard_widgets(linkid, userid, filterid, title, data,dashboardtabid) VALUES(?,?,?,?,?,?)',
                [$this->get('linkid'), $this->get('userid'), $this->get('filterid'), $this->get('title'), Zend_Json::encode($this->get('data')), $tabid]
            );
            $this->set('id', $db->getLastInsertID());
        } else {
            $this->set('id', $db->query_result($result, 0, 'id'));
        }
    }

    /**
     * Function to remove the widget from the Users Dashboard
     */
    public function remove()
    {
        $db = PearDatabase::getInstance();
        $db->pquery(
            'DELETE FROM vtiger_module_dashboard_widgets WHERE id = ? AND userid = ?',
            [$this->get('id'), $this->get('userid')]
        );
    }

    /**
     * Function returns URL that will remove a widget for a User
     * @return <String>
     */
    public function getDeleteUrl()
    {
        $url = 'index.php?module=Vtiger&action=RemoveWidget&linkid=' . $this->get('linkid');
        $widgetid = $this->has('widgetid') ? $this->get('widgetid') : $this->get('id');
        if ($widgetid) {
            $url .= '&widgetid=' . $widgetid;
        }

        return $url;
    }

    /**
     * Function to check the Widget is Default widget or not
     * @return <boolean> true/false
     */
    public function isDefault()
    {
        $defaultWidgets = $this->getDefaultWidgets();
        $widgetName = $this->getName();

        if (in_array($widgetName, $defaultWidgets)) {
            return true;
        }

        return false;
    }

    /**
     * Function to get Default widget Names
     * @return <type>
     */
    public function getDefaultWidgets()
    {
        return array();
    }
}