<?php

declare(strict_types=1);

namespace brokiem\simplepets\pets;

use brokiem\simplepets\pets\base\BasePet;
use pocketmine\entity\EntitySizeInfo;

class CreeperPet extends BasePet {

    public static function getNetworkTypeId(): string {
        return "minecraft:creeper";
    }

    public function getPetType(): string {
        return "CreeperPet";
    }

    protected function getInitialSizeInfo(): EntitySizeInfo {
        return new EntitySizeInfo(0.85, 0.8);
    }
}