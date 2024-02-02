<?php
namespace Tests\TestGenerators;
class UnitTestFile extends BasePhpUnitTestFile
{
    /**
     * @param string|null $content
     * @param string|null $namePrefix
     * @return string
     */
    public static function generateAndGetUrl(string $content = null, string $namePrefix = null): string {
        return parent::generateAndGetUrl(static::getFileContent(), $content ?? static::generateName());
    }
    public static function generateNameSpace(): string{
        return 'Tests\UnitTests';
    }
}
