<?php
namespace Tests\TestGenerators;
class ImportTestFiles extends StagingJobTestFile
{
    public static function generateNameSpace(): string{
        return "App\PhpUnitJobs\Import";
    }
}