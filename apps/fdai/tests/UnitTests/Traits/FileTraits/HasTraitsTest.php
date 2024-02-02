<?php

namespace Tests\UnitTests\Traits\FileTraits;
use App\Traits\FileTraits\HasTraits;
use App\Traits\FileTraits\IsSolution;
use Tests\UnitTestCase;
class HasTraitsTest extends UnitTestCase
{
    public function testAddTraitToFiles(){
        $this->skipTest("TODO");
        HasTraits::addTraitToFiles('app/Solutions', IsSolution::class);
    }
}
