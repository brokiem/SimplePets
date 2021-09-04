<?php

declare(strict_types=1);

namespace brokiem\simplepets\pets;

use brokiem\simplepets\pets\base\BasePet;
use pocketmine\entity\EntitySizeInfo;

class PolarbearPet extends BasePet {

    public static function getNetworkTypeId(): string {
        return "minecraft:polar_bear";
    }

    public function getPetType(): string {
        return "PolarbearPet";
    }

    protected function getInitialSizeInfo(): EntitySizeInfo {
        return new EntitySizeInfo(0.85, 0.8);
    }
}