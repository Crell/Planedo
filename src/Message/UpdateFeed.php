<?php

namespace App\Message;

final class UpdateFeed
{
    public function __construct(
        public int $feedId,
    ) {}

    /*
     * Add whatever properties & methods you need to hold the
     * data for this message class.
     */

//     private $name;
//
//     public function __construct(string $name)
//     {
//         $this->name = $name;
//     }
//
//    public function getName(): string
//    {
//        return $this->name;
//    }
}
