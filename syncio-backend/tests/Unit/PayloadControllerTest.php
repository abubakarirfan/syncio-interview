<?php

use PHPUnit\Framework\TestCase;
use App\Http\Controllers\PayloadController;

class PayloadControllerTest extends TestCase
{
    public function testArrayDiffAssocRecursive()
    {
        $controller = new PayloadController();

        $reflection = new ReflectionClass($controller);
        $method = $reflection->getMethod('arrayDiffAssocRecursive');
        $method->setAccessible(true);

        // Define two sample arrays.
        $array1 = [
            'name' => 'Alice',
            'age'  => 30,
            'preferences' => [
                'color' => 'blue',
                'food'  => 'pasta'
            ]
        ];

        $array2 = [
            'name' => 'Alice',
            'age'  => 31,
            'preferences' => [
                'color' => 'green',
                'food'  => 'pasta',
                'music' => 'jazz'
            ]
        ];

        $expected = [
            'age' => ['old' => 30, 'new' => 31],
            'preferences' => [
                'color' => ['old' => 'blue', 'new' => 'green'],
                'music' => ['old' => null, 'new' => 'jazz']
            ]
        ];

        $result = $method->invoke($controller, $array1, $array2);
        $this->assertEquals($expected, $result);
    }
}
