<?php

declare(strict_types=1);

namespace brokiem\simplepets\entity\pets;

use brokiem\simplepets\entity\pets\base\BasePet;
use pocketmine\entity\EntitySizeInfo;

class GoatPet extends BasePet {

    public static function getNetworkTypeId(): string {
        return "minecraft:goat";
    }

    public function getPetType(): string {
        return "GoatPet";
    }

    protected function getInitialSizeInfo(): EntitySizeInfo {
        return new EntitySizeInfo(0.7, 0.7);
    }
}