<?php

declare(strict_types=1);

namespace brokiem\simplepets\pets;

use brokiem\simplepets\pets\base\BasePet;

class PillagerPet extends BasePet {

    public const SPET_ENTITY_ID = "minecraft:pillager";

    public $height = 0.9;
    public $width = 0.9;

    public function getPetType(): string {
        return "PillagerPet";
    }
}