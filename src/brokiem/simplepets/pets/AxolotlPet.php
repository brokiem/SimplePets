<?php

declare(strict_types=1);

namespace brokiem\simplepets\pets;

use brokiem\simplepets\pets\base\BasePet;
use pocketmine\entity\EntitySizeInfo;

class AxolotlPet extends BasePet {

    public static function getNetworkTypeId(): string {
        return "minecraft:axolotl";
    }

    public function getPetType(): string {
        return "AxolotlPet";
    }

    protected function getInitialSizeInfo(): EntitySizeInfo {
        return new EntitySizeInfo(1.3, 0.6);
    }
}