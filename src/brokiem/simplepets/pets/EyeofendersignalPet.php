<?php

declare(strict_types=1);

namespace brokiem\simplepets\pets;

use brokiem\simplepets\pets\base\BasePet;
use pocketmine\entity\EntitySizeInfo;

class EyeofendersignalPet extends BasePet {

    public static function getNetworkTypeId(): string {
        return "minecraft:eye_of_ender_signal";
    }

    public function getPetType(): string {
        return "EyeofendersignalPet";
    }

    protected function getInitialSizeInfo(): EntitySizeInfo {
        return new EntitySizeInfo(0.85, 0.8);
    }
}