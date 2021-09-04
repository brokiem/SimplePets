<?php

declare(strict_types=1);

namespace brokiem\simplepets\pets;

use brokiem\simplepets\pets\base\BasePet;
use pocketmine\entity\EntitySizeInfo;

class PiglinPet extends BasePet {

    public static function getNetworkTypeId(): string {
        return "minecraft:piglin";
    }

    public function getPetType(): string {
        return "PiglinPet";
    }

    protected function getInitialSizeInfo(): EntitySizeInfo {
        return new EntitySizeInfo(0.85, 0.8);
    }
}