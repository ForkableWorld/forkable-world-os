<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Console\Concerns;

use Illuminate\Support\Str;

trait AcceptsNameAndVendor
{
    /**
     * Determine if the name argument is valid.
     *
     * @return bool
     */
    public function hasValidNameArgument()
    {
        $name = $this->argument('name');

        if (! Str::contains($name, '/')) {
            $this->error("The name argument expects a vendor and name in 'Composer' format. Here's an example: `vendor/name`.");

            return false;
        }

        return true;
    }
}
