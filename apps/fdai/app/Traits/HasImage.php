<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits;
trait HasImage {
	use HasFiles;
	abstract public function getImage(): string;
	abstract public function getTitleAttribute(): string;
	public function getImageHtml(): string{
		$image = $this->getImage();
		$title = $this->getTitleAttribute();
		$caption = '';
		return "
            <figure class=\"featured-media\">
                <div class=\"featured-media-inner section-inner\">
                    <img src=\"$image\" class=\"external-img wp-post-image \" alt=\"$title\">
			        <figcaption class=\"wp-caption-text\">$caption</figcaption>
                </div><!-- .featured-media-inner -->
            </figure>
        ";
	}
	public function registerMediaCollections(){
		$this
			->addMediaCollection('images')
			->useDisk($this->getDiskName());
	}
}
