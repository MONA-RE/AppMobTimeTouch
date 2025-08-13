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
 *  \file       core/modules/appmobtimetouch/modules_timeclockovertimepaid.php
 *  \ingroup    appmobtimetouch
 *  \brief      File that contains parent class for timeclockovertimepaid numbering modules
 */

require_once DOL_DOCUMENT_ROOT.'/core/class/commonnumrefgenerator.class.php';

/**
 * Parent class of numbering modules for TimeclockOvertimePaid references
 */
abstract class ModeleNumRefTimeclockOvertimePaid extends CommonNumRefGenerator
{
    // No overload code
}