<?php

declare(strict_types=1);

namespace brokiem\simplepets\pets;

use brokiem\simplepets\pets\base\BasePet;
use pocketmine\entity\EntitySizeInfo;

class TropicalfishPet extends BasePet {

    public static function getNetworkTypeId(): string {
        return "minecraft:tropicalfish";
    }

    public function getPetType(): string {
        return "TropicalfishPet";
    }

    protected function getInitialSizeInfo(): EntitySizeInfo {
        return new EntitySizeInfo(0.85, 0.8);
    }
}