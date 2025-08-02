<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

if (!class_exists('Migration_20230420073338')) {
    class Migration_20230420073338 extends AbstractMigrations
    {
        /**
         * @param string $strFileName
         */
        public function migrate(string $strFileName): void
        {
            $sql = 'CREATE TABLE IF NOT EXISTS `its4you_sharing_users` (
                      `crmid` int(19) NOT NULL,
                      `userid` int(19) NOT NULL,
                      `type` int(1) NOT NULL,
                      KEY `crmid` (`crmid`),
                      KEY `userid` (`userid`),
                      KEY `crmid_2` (`crmid`,`userid`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;';
            $this->db->pquery($sql);

            $sql = 'CREATE TABLE IF NOT EXISTS `its4you_sharing_groups` (
                      `crmid` int(19) NOT NULL,
                      `groupid` int(19) NOT NULL,
                      `type` int(1) NOT NULL,
                      KEY `crmid` (`crmid`),
                      KEY `groupid` (`groupid`),
                      KEY `crmid_2` (`crmid`,`groupid`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;';
            $this->db->pquery($sql);

            $sql = 'CREATE TABLE IF NOT EXISTS `its4you_sharing_roles` (
                      `crmid` int(19) NOT NULL,
                      `roleid` varchar(255) NOT NULL,
                      `type` int(1) NOT NULL,
                      KEY `crmid` (`crmid`),
                      KEY `roleid` (`roleid`),
                      KEY `crmid_2` (`crmid`,`roleid`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;';
            $this->db->pquery($sql);

            $sql = 'CREATE TABLE IF NOT EXISTS `its4you_sharing_rolessubroles` (
                      `crmid` int(19) NOT NULL,
                      `roleid` varchar(255) NOT NULL,
                      `type` int(1) NOT NULL,
                      KEY `crmid` (`crmid`),
                      KEY `rolesid` (`roleid`),
                      KEY `crmid_2` (`crmid`,`roleid`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;';
            $this->db->pquery($sql);

            $sql = 'CREATE TABLE IF NOT EXISTS `its4you_sharing_multicompany` (
                      `crmid` int(19) NOT NULL,
                      `companyid` int(19) NOT NULL,
                      `type` int(1) NOT NULL,
                      KEY `crmid` (`crmid`),
                      KEY `companyid` (`companyid`),
                      KEY `crmid_2` (`crmid`,`companyid`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;';

            $this->db->pquery($sql, []);
        }
    }
} else {
    $baseFileName = str_replace('.php', '', basename(__FILE__));
    $this->makeAborting($this->wrongClassName . $baseFileName);
}