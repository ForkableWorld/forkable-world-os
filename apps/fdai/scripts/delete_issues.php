<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn 
 */

require_once __DIR__ . '/php/bootstrap_script.php';

use App\Repos\CrowdsourcingCuresAppRepo;

CrowdsourcingCuresAppRepo::deleteIssues();
