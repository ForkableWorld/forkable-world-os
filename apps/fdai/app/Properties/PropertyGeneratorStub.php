<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties;
class PropertyGeneratorStub extends BasePropertyGenerator
{
    public $title;
    public $description;
    public $required;
    public $properties;
    public $type;
    public $format;
    public $items;
    public $collectionFormat;
    public $default = \OpenApi\Generator::UNDEFINED;
    public $maximum;
    public $exclusiveMaximum;
    public $minimum;
    public $exclusiveMinimum;
    public $maxLength;
    public $minLength;
    public $pattern;
    public $maxItems;
    public $minItems;
    public $uniqueItems;
    public $enum;
    public $multipleOf;
    public $readOnly;
    public $externalDocs;
}
