<?php

// This file is part of the Moodle module "EJSApp booking system"
//
// EJSApp booking system is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// EJSApp booking system is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// The GNU General Public License is available on <http://www.gnu.org/licenses/>
//
// EJSApp booking system has been developed by:
//  - Francisco José Calvillo Muñoz: fcalvillo9@alumno.uned.es
//  - Luis de la Torre: ldelatorre@dia.uned.es
//	- Ruben Heradio: rheradio@issi.uned.es
//
//  at the Computer Science and Automatic Control, Spanish Open University
//  (UNED), Madrid, Spain


/**
 * Searches for the bookings of a user
 *
 * @package    mod
 * @subpackage ejsappbooking
 * @copyright  2012 Francisco José Calvillo Muñoz, Luis de la Torre and Ruben Heradio
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_login();
global $CFG, $DB;

$selectDay = optional_param('dateActual', 0, PARAM_RAW);

$today = new DateTime("now");
$fecha = DateTime::createFromFormat("Y-m-d", $selectDay);

header('Content-type: application/json; charset=utf-8');

$response = array();
$answer = 'NO';

if (isset($_GET['dateActual'])) {

    $sql = 'SELECT a.id, a.username, a.ejsappid, a.practiceid, a.starttime, a.endtime, a.valid, b.name FROM {ejsappbooking_remlab_access} a INNER JOIN {ejsapp} b ON a.ejsappid = b.id WHERE a.username = "' . $USER->username . '" AND DATE_FORMAT(starttime, "%Y-%m-%d") >= "' . $today->format('Y-m-d') . '"  AND DATE_FORMAT(endtime, "%Y-%m") = "' . $fecha->format('Y-m') . '"ORDER BY a.starttime ASC';
    $userBookings = $DB->get_records_sql($sql);
    $i=0;
    foreach ($userBookings as $event) {

        $time = new DateTime($event->starttime);
        $time2 = new DateTime($event->endtime);
        $plant = $event->name;
        $date = $time->format("Y-m-d");
        $day = $time->format("d");
        $hour = $time->format("H:i") . '-' . $time2->format("H:i");
        $i++;
        $response[$i]= $day . ',' . $plant . ',' . $date . ',' . $hour;
    }

} else {
    $response = $answer;
}

echo json_encode($response);