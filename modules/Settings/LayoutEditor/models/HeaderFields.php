<?php
/*+**********************************************************************************
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 ************************************************************************************/


class Settings_LayoutEditor_HeaderFields_Model extends Vtiger_Field_Model {

    protected PearDatabase $db;

    protected function db()
    {
        if (!isset($this->db)) {
            $this->db = PearDatabase::getInstance();
        }

        return $this->db;
    }

    public function saveHeaderFields($moduleName, $headerFields)
    {
        $this->db()->pquery('UPDATE vtiger_field SET headerfieldsequence=NULL WHERE tabid=?', [getTabid($moduleName)]);

        foreach ($headerFields as $key => $fieldName) {
            $this->db()->pquery('UPDATE vtiger_field SET headerfieldsequence=? WHERE tabid=? AND fieldname=?', [
                $key + 1,
                getTabid($moduleName),
                $fieldName
            ]);
        }
    }

    public function getHeaderFields($moduleName)
    {
        return $this->db()->run_query_allrecords(sprintf(
            'SELECT fieldname, headerfieldsequence, fieldlabel 
                FROM vtiger_field 
                WHERE tabid="%s" 
                AND headerfieldsequence IS NOT NULL
                ORDER BY headerfieldsequence ASC
            ',
            getTabid($moduleName)
        ));
    }

    public function getHeaderFieldNames($moduleName): array
    {
        $fieldNames = [];
        $fieldNameRows = $this->db()->run_query_allrecords(sprintf(
            'SELECT fieldname 
                FROM vtiger_field 
                WHERE tabid="%s" 
                AND headerfieldsequence IS NOT NULL
                ORDER BY headerfieldsequence ASC
            ',
            getTabid($moduleName)
        ));

        foreach ($fieldNameRows as $fieldNameRow) {
            $fieldNames[] = $fieldNameRow['fieldname'];
        }

        return $fieldNames;
    }

}