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
     * @param   string      $mode       'next' for next value or 'last' for last value
     * @return  string                  Value if OK, <0 if KO
     */
    public function getNextValue($objsoc, $object, $mode = 'next')
    {
        global $db, $conf;

        dol_syslog(get_class($this)."::getNextValue mode=".$mode, LOG_DEBUG);

        // First we get the max value
        $posindice = strlen($this->prefix) + 6; // PO + YYYY + 1 for dash
        $sql = "SELECT MAX(CAST(SUBSTRING(ref FROM ".$posindice.") AS SIGNED)) as max"; // This is standard SQL
        $sql .= " FROM ".MAIN_DB_PREFIX."appmobtimetouch_timeclockovertimepaid";
        $sql .= " WHERE ref LIKE '".$db->escape($this->prefix)."____-%'";
        $sql .= " AND entity IN (".getEntity('timeclockovertimepaid', 1, $object).")";

        $resql = $db->query($sql);
        if ($resql) {
            $obj = $db->fetch_object($resql);
            if ($obj) {
                $max = intval($obj->max);
            } else {
                $max = 0;
            }
        } else {
            return -1;
        }

        if ($mode == 'last') {
            if ($max >= (pow(10, 4) - 1)) {
                $num = $max; // If counter > 9999, we do not format on 4 chars, we take number as it is
            } else {
                $num = sprintf("%04s", $max);
            }

            $ref = '';
            $sql = "SELECT ref as ref";
            $sql .= " FROM ".MAIN_DB_PREFIX."appmobtimetouch_timeclockovertimepaid";
            $sql .= " WHERE ref LIKE '".$db->escape($this->prefix)."____-".$num."'";
            $sql .= " AND entity IN (".getEntity('timeclockovertimepaid', 1, $object).")";
            $sql .= " ORDER BY ref DESC";

            $resql = $db->query($sql);
            if ($resql) {
                $obj = $db->fetch_object($resql);
                if ($obj) {
                    $ref = $obj->ref;
                }
            } else {
                dol_print_error($db);
            }

            return $ref;
        } elseif ($mode == 'next') {
            $date = dol_now(); // Use current date for paid overtime
            $yymm = dol_print_date($date, "%Y");

            if ($max >= (pow(10, 4) - 1)) {
                $num = $max + 1; // If counter > 9999, we do not format on 4 chars, we take number as it is
            } else {
                $num = sprintf("%04s", $max + 1);
            }

            dol_syslog(get_class($this)."::getNextValue return ".$this->prefix.$yymm."-".$num, LOG_DEBUG);
            return $this->prefix.$yymm."-".$num;
        } else {
            dol_print_error('', 'Bad parameter for getNextValue');
        }

        return 0;
    }
}