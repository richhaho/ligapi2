<?php


namespace App\Tests\Controller;


trait AdditionalAssertsTrait
{
    private function assertArrayContainsSameObject($theArray, $theObject): bool
    {
        foreach($theArray as $arrayItem) {
            if($arrayItem == $theObject) {
                return true;
            }
        }
        return false;
    }
}
