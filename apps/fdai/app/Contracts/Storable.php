<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Contracts;

interface Storable
{
    /**
     * Get the disk that the field is stored on.
     *
     * @return string|null
     */
    public function getStorageDisk();

    /**
     * Get the dir that the field is stored at on disk.
     *
     * @return string|null
     */
    public function getStorageDir();

    /**
     * Get the full path that the field is stored at on disk.
     *
     * @return string|null
     */
    public function getStoragePath();
}
