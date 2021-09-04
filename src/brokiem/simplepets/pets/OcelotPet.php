<?php

declare(strict_types=1);

namespace brokiem\simplepets\pets;

use brokiem\simplepets\pets\base\BasePet;
use pocketmine\entity\EntitySizeInfo;

class OcelotPet extends BasePet {

    public static function getNetworkTypeId(): string {
        return "minecraft:ocelot";
    }

    public function getPetType(): string {
        return "OcelotPet";
    }

    protected function getInitialSizeInfo(): EntitySizeInfo {
        return new EntitySizeInfo(0.85, 0.8);
    }
}