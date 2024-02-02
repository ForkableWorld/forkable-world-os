<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Files;
use App\Types\QMStr;
use App\Utils\AppMode;
use Spatie\MediaLibrary\Models\Media;
use Spatie\MediaLibrary\PathGenerator\PathGenerator;
class CustomPathGenerator implements PathGenerator {
	/**
	 * Get the path for the given media, relative to the root storage path.
	 * @param Media $media
	 * @return string
	 */
	public function getPath(Media $media): string{
		$shortClass = QMStr::getShortPluralizedClassName($media->getAttribute('model_type'));
		return $shortClass . '/' . $media->getAttribute('model_id') . '/';
	}
	/**
	 * Get the path for conversions of the given media, relative to the root storage path.
	 * @param Media $media
	 * @return string
	 */
	public function getPathForConversions(Media $media): string{
		$path = $this->getPath($media) . '/conversions/';
		if(AppMode::isTestingOrStaging()){
			$path = "testing/$path";
		}
		return $path;
	}
	/**
	 * @param Media $media
	 * @return string
	 */
	public function getPathForResponsiveImages(Media $media): string{
		return $this->getPath($media) . '/responsive-images/';
	}
}
