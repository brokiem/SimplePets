<?php

declare(strict_types=1);

namespace brokiem\simplepets\entity\pets\base;

use pocketmine\entity\Human;

abstract class CustomPet extends Human {

    abstract public function getPetType(): string;
}