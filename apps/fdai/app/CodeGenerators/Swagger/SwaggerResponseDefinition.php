<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\CodeGenerators\Swagger;
use Illuminate\Support\Pluralizer;
use App\Types\QMStr;
class SwaggerResponseDefinition extends SwaggerDefinition {
    public $required = [
        "description",
        "summary"
    ];
    /**
     * SwaggerResponseDefinition constructor.
     * @param $className
     */
    public function __construct($className){
        $camelPlural = Pluralizer::plural(QMStr::camelize($className));
        $this->required = [$camelPlural];
        $this->properties = json_decode('
        {
            "'.$camelPlural.'": {
                "items": {
                    "$ref": "#/definitions/'.$className.'"
                },
                "type": "array"
            },
            "description": {
              "description": "'.$className.'",
              "type": "string"
            },
            "summary": {
              "description": "'.$className.'",
              "type": "string"
            },
            "image": {
              "$ref": "#/definitions/Image"
            },
            "avatar": {
              "description": "Square icon png url",
              "type": "string"
            },
            "ionIcon": {
              "description": "Ex: ion-ios-person",
              "type": "string"
            },
            "html": {
              "description": "Embeddable list of study summaries with explanation at the top",
              "type": "string"
            }
          }
        ');
        $this->unsetNullFields();
    }
}
