<?php

declare(strict_types=1);

namespace brokiem\simplepets\pets;

use brokiem\simplepets\pets\base\BasePet;
use pocketmine\entity\EntitySizeInfo;

class WardenPet extends BasePet {

    public static function getNetworkTypeId(): string {
        return "minecraft:warden";
    }

    public function getPetType(): string {
        return "WardenPet";
    }

    protected function getInitialSizeInfo(): EntitySizeInfo {
        return new EntitySizeInfo(2.9, 0.9);
    }
}