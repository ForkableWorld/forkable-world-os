<?php
namespace Tests;
abstract class AstralTestCase extends LaravelStagingTestCase
{
    protected function assertResourceFieldsEqual(array $expected, array $resources, string $attribute){
        $values = [];
        foreach($resources as $resource){
            $fields = $resource->fields;
            $field = collect($fields)->firstWhere('attribute',$attribute);
            $values[] = $field->value;
        }
        $this->assertArrayEquals($expected, $values);
    }
    protected function assertResourceFieldsContain(array $expected, array $resources, string $indexName){
        $values = [];
        foreach($resources as $resource){
            $fields = $resource->fields;
            $field = collect($fields)->firstWhere('indexName', $indexName);
            $values[] = $field->value;
        }
        $this->assertArrayEquals($expected, $values);
    }
    protected function assertResourcesDoNotHaveField(array $resources, string $attribute, string $message = ""){
        foreach($resources as $resource){
            $fields = $resource->fields;
            $userField = collect($fields)->firstWhere('attribute', $attribute);
            $this->assertNull($userField, $message);
        }
    }
    protected function assertResourcesHaveField(array $resources, string $attribute, string $message = ""){
        foreach($resources as $resource){
            $fields = $resource->fields;
            $userField = collect($fields)->firstWhere('attribute', $attribute);
            $this->assertNotNull($userField, $message);
        }
    }
}
