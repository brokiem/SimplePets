<?php

/*
 * Copyright (c) 2021 broki
 * brokiem/SimplePets is licensed under the MIT License
 */

declare(strict_types=1);

namespace brokiem\simplepets\pets;

use brokiem\simplepets\pets\base\BasePet;

class WolfPet extends BasePet {

    public const SPET_ENTITY_ID = "minecraft:wolf";

    public $height = 0.85;
    public $width = 0.8;

    public function getPetType(): string {
        return "WolfPet";
    }
}