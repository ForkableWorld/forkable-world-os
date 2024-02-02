<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\CodeGenerators\Swagger;
use Illuminate\Support\Pluralizer;
use App\Types\QMStr;
class SwaggerPathMethod {
    public $operationId;
    public $tags;
    public $summary;
    public $description;
    public $produces;
    public $responses;
    public $parameters;
    public $security;
    /**
     * SwaggerPathMethod constructor.
     * @param $method
     * @param $path
     */
    public function __construct($method, $path){
        $className = $path;
        if(stripos($path, '/v') === false){
            $plural = Pluralizer::plural($path);
            $this->operationId = $method.$plural;
            $tag = QMStr::camelToTitle($plural);
            $tag = QMStr::getFirstWordOfString($tag);
            $this->tags[] = $tag;
            $this->summary = ucfirst($method)." ".$plural;
        }else{
            $partsOfPath = explode('/', $path);
            $this->operationId = $method;
            $this->summary = ucfirst($method);
            for($i = 2, $iMax = count($partsOfPath); $i < $iMax; $i++){
                $this->operationId .= ucfirst($partsOfPath[$i]);
                $this->summary .= " ".ucfirst($partsOfPath[$i]);
            }
            if(isset($partsOfPath[2])){
                $this->tags[] = $partsOfPath[2];
            }else{
                $this->tags[] = $partsOfPath[1];
            }
        }
        $this->description = $this->summary;
        $this->produces[] = "application/json";
        $this->parameters = json_decode('
        [
                  {
                    "$ref": "#/parameters/sortParam"
                  },
                  {
                    "$ref": "#/parameters/limitParam"
                  },
                  {
                    "$ref": "#/parameters/offsetParam"
                  },
                  {
                    "$ref": "#/parameters/updatedAtParam"
                  },
                  {
                    "$ref": "#/parameters/userIdParam"
                  },
                  {
                    "$ref": "#/parameters/createdAtParam"
                  },
                  {
                    "description": "Id",
                    "in": "query",
                    "name": "id",
                    "required": false,
                    "type": "integer"
                  },
                  {
                    "$ref": "#/parameters/clientIdParam"
                  },
                  {
                    "$ref": "#/parameters/platform"
                  }
              ]
        ');
        $successResponseCode = ($method === "post") ? "201" : "200";
        $this->security = json_decode('
             [
                  {
                    "access_token": []
                  },
                  {
                    "quantimodo_oauth2": [
                      "readmeasurements"
                    ]
                  }
            ]
        ');
        $this->responses = json_decode('
            {
              "'.$successResponseCode.'": {
                "description": "Successful operation",
                "schema": {
                  "items": {
                    "$ref": "#/definitions/'.Pluralizer::plural($className).'Response"
                  },
                  "type": "array"
                }
              },
              "401": {
                "description": "Not authenticated"
              },
              "404": {
                "description": "Not found"
              },
              "500": {
                "description": "Internal server error"
              }
            }
        ');
        //$this->responses[] = new SwaggerResponse();
    }
}
