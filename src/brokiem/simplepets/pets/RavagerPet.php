<?php

declare(strict_types=1);

namespace brokiem\simplepets\pets;

use brokiem\simplepets\pets\base\BasePet;
use pocketmine\entity\EntitySizeInfo;

class RavagerPet extends BasePet {

    public static function getNetworkTypeId(): string {
        return "minecraft:ravager";
    }

    public function getPetType(): string {
        return "RavagerPet";
    }

    protected function getInitialSizeInfo(): EntitySizeInfo {
        return new EntitySizeInfo(0.85, 0.8);
    }
}