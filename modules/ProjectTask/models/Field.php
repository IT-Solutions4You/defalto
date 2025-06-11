<?php
/*
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class ProjectTask_Field_Model extends Vtiger_Field_Model {
    public function getPicklistValues()
    {
        if ('projecttaskprogress' === $this->getName()) {
            return ProjectTask_Install_Model::$progressValues;
        }

        return parent::getPicklistValues();
    }
}
