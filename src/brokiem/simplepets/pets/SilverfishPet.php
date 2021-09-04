<?php

declare(strict_types=1);

namespace brokiem\simplepets\pets;

use brokiem\simplepets\pets\base\BasePet;
use pocketmine\entity\EntitySizeInfo;

class SilverfishPet extends BasePet {

    public static function getNetworkTypeId(): string {
        return "minecraft:silverfish";
    }

    public function getPetType(): string {
        return "SilverfishPet";
    }

    protected function getInitialSizeInfo(): EntitySizeInfo {
        return new EntitySizeInfo(0.85, 0.8);
    }
}