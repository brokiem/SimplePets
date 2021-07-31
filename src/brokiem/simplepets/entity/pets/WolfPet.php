<?php

/*
 * Copyright (c) 2021 broki
 * brokiem/SimplePets is licensed under the MIT License
 */

declare(strict_types=1);

namespace brokiem\simplepets\entity\pets;

use brokiem\simplepets\entity\pets\base\BasePet;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;

class WolfPet extends BasePet {

    public static function getNetworkTypeId(): string {
        return EntityIds::WOLF;
    }

    public function getPetType(): string {
        return "WolfPet";
    }

    protected function getInitialSizeInfo(): EntitySizeInfo {
        return new EntitySizeInfo(0.85, 0.8);
    }
}