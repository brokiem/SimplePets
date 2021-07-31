<?php

declare(strict_types=1);

namespace brokiem\simplepets\entity\pets\base;

use pocketmine\entity\Entity;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\entity\Location;
use pocketmine\nbt\tag\CompoundTag;

abstract class BasePet extends Entity {

    private ?string $petOwner = null;
    private ?string $petName = null;
    private float $petSize = 1;

    public function __construct(Location $location, ?CompoundTag $nbt = null) {
        parent::__construct($location, $nbt);
        $this->setNameTagAlwaysVisible();
    }

    abstract public static function getNetworkTypeId(): string;

    public function getPetOwner(): ?string {
        return $this->petOwner;
    }

    public function setPetOwner(string $xuid): void {
        $this->petOwner = $xuid;
    }

    public function getPetName(): ?string {
        return $this->petName;
    }

    public function setPetName(string $name): void {
        $this->petName = $name;
        $this->setNameTag($name);
    }

    public function getPetSize(): float {
        return $this->petSize;
    }

    public function setPetSize(float $size): void {
        $this->petSize = $size;
        $this->setScale($size);
    }

    abstract public function getPetType(): string;

    abstract protected function getInitialSizeInfo(): EntitySizeInfo;
}