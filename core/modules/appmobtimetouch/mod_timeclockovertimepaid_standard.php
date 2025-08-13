<?php
/* Copyright (C) 2025 SuperAdmin
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

/**
 *  \file       core/modules/appmobtimetouch/mod_timeclockovertimepaid_standard.php
 *  \ingroup    appmobtimetouch
 *  \brief      Standard numbering module for TimeclockOvertimePaid
 */

require_once DOL_DOCUMENT_ROOT.'/custom/appmobtimetouch/core/modules/appmobtimetouch/modules_timeclockovertimepaid.php';

/**
 * Class to manage the Standard numbering rule for TimeclockOvertimePaid
 */
class mod_timeclockovertimepaid_standard extends ModeleNumRefTimeclockOvertimePaid
{
    /**
     * @var string model name
     */
    public $name = 'standard';

    /**
     * @var string model description (short text)
     */
    public $description = "Standard numbering for paid overtime hours";

    /**
     * @var string Dolibarr version of the loaded document
     */
    public $version = 'dolibarr';

    /**
     * @var string prefix
     */
    public $prefix = 'PO';

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->code_auto = 1;
    }

    /**
     * Return description of numbering module
     *
     * @return string      Text with description
     */
    public function info()
    {
        global $langs;
        return $langs->trans("Standard numbering for paid overtime hours") . ': PO{YYYY}-{NNNN}';
    }

    /**
     * Return an example of numbering
     *
     * @return string      Example
     */
    public function getExample()
    {
        return $this->prefix . date('Y') . '-0001';
    }

    /**
     * Return next free value
     *
     * @param   Societe     $objsoc     Object thirdparty
     * @param   Object      $object     Object we need next value for
     * @return  string                  Value if KO, <0 if KO
     */
    public function getNextValue($objsoc, $object)
    {
        global $db, $conf;

        // First we get the max value
        $posindice = strlen($this->prefix . date('Y')) + 1;
        $sql = "SELECT MAX(CAST(SUBSTRING(ref FROM ".$posindice.") AS SIGNED)) as max";
        $sql .= " FROM ".MAIN_DB_PREFIX."appmobtimetouch_timeclockovertimepaid";
        $sql .= " WHERE ref LIKE '".$this->prefix.date('Y')."-%'";
        if (method_exists($object, 'getEntity') && $object->getEntity() > 0) {
            $sql .= " AND entity = ".$object->getEntity();
        } elseif (isset($object->entity)) {
            $sql .= " AND entity = ".$object->entity;
        } else {
            $sql .= " AND entity = ".$conf->entity;
        }

        $resql = $db->query($sql);
        if ($resql) {
            $obj = $db->fetch_object($resql);
            if ($obj) {
                $max = intval($obj->max);
            } else {
                $max = 0;
            }
        } else {
            dol_syslog("mod_timeclockovertimepaid_standard::getNextValue", LOG_DEBUG);
            return -1;
        }

        $date = time();
        $yymm = strftime("%Y", $date);
        $num = sprintf("%04d", $max + 1);

        dol_syslog("mod_timeclockovertimepaid_standard::getNextValue return ".$this->prefix.$yymm."-".$num);
        return $this->prefix.$yymm."-".$num;
    }
}