<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\DevOps\Jenkins;
class Factory {
	/**
	 * @param string $url
	 * @return JenkinsAPI
	 */
	public function build(string $url): JenkinsAPI{
		return new JenkinsAPI($url);
	}
}
