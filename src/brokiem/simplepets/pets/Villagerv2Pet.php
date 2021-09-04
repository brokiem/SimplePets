<?php

declare(strict_types=1);

namespace brokiem\simplepets\pets;

use brokiem\simplepets\pets\base\BasePet;
use pocketmine\entity\EntitySizeInfo;

class Villagerv2Pet extends BasePet {

    public static function getNetworkTypeId(): string {
        return "minecraft:villager_v2";
    }

    public function getPetType(): string {
        return "Villagerv2Pet";
    }

    protected function getInitialSizeInfo(): EntitySizeInfo {
        return new EntitySizeInfo(0.85, 0.8);
    }
}