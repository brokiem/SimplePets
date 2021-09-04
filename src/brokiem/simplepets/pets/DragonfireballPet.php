<?php

declare(strict_types=1);

namespace brokiem\simplepets\pets;

use brokiem\simplepets\pets\base\BasePet;
use pocketmine\entity\EntitySizeInfo;

class DragonfireballPet extends BasePet {

    public static function getNetworkTypeId(): string {
        return "minecraft:dragon_fireball";
    }

    public function getPetType(): string {
        return "DragonfireballPet";
    }

    protected function getInitialSizeInfo(): EntitySizeInfo {
        return new EntitySizeInfo(0.85, 0.8);
    }
}