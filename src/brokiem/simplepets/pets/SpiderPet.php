<?php

declare(strict_types=1);

namespace brokiem\simplepets\pets;

use brokiem\simplepets\pets\base\BasePet;
use pocketmine\entity\EntitySizeInfo;

class SpiderPet extends BasePet {

    public static function getNetworkTypeId(): string {
        return "minecraft:spider";
    }

    public function getPetType(): string {
        return "SpiderPet";
    }

    protected function getInitialSizeInfo(): EntitySizeInfo {
        return new EntitySizeInfo(0.85, 0.8);
    }
}