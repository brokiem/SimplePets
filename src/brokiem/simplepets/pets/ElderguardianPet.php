<?php

/*
 * Copyright (c) 2021 broki
 * brokiem/SimplePets is licensed under the MIT License
 */

declare(strict_types=1);

namespace brokiem\simplepets\pets;

use brokiem\simplepets\pets\base\BasePet;

class ElderguardianPet extends BasePet {

    public const SPET_ENTITY_ID = "minecraft:elder_guardian";

    public $height = 0.9;
    public $width = 0.9;

    public function getPetType(): string {
        return "ElderguardianPet";
    }
}