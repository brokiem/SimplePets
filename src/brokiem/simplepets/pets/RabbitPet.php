<?php

declare(strict_types=1);

namespace brokiem\simplepets\pets;

use brokiem\simplepets\pets\base\BasePet;
use pocketmine\entity\EntitySizeInfo;

class RabbitPet extends BasePet {

    public static function getNetworkTypeId(): string {
        return "minecraft:rabbit";
    }

    public function getPetType(): string {
        return "RabbitPet";
    }

    protected function getInitialSizeInfo(): EntitySizeInfo {
        return new EntitySizeInfo(0.85, 0.8);
    }
}