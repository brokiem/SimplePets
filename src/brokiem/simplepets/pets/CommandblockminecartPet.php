<?php

declare(strict_types=1);

namespace brokiem\simplepets\pets;

use brokiem\simplepets\pets\base\BasePet;
use pocketmine\entity\EntitySizeInfo;

class CommandblockminecartPet extends BasePet {

    public static function getNetworkTypeId(): string {
        return "minecraft:command_block_minecart";
    }

    public function getPetType(): string {
        return "CommandblockminecartPet";
    }

    protected function getInitialSizeInfo(): EntitySizeInfo {
        return new EntitySizeInfo(0.85, 0.8);
    }
}