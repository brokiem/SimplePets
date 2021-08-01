<?php

/*
 * Copyright (c) 2021 broki
 * brokiem/SimplePets is licensed under the MIT License
 */

declare(strict_types=1);

namespace brokiem\simplepets\pets\base;

use brokiem\simplepets\SimplePets;
use pocketmine\entity\Human;
use pocketmine\entity\Location;
use pocketmine\entity\Skin;
use pocketmine\nbt\tag\CompoundTag;

abstract class CustomPet extends Human {

    private ?string $petOwner = null;
    private ?string $petName = null;
    private float|int $petSize = 1;
    private float|int $checkVal = 0;

    public function __construct(Location $location, Skin $skin, ?CompoundTag $nbt = null) {
        parent::__construct($location, $skin, $nbt);
        $this->setNameTagAlwaysVisible();
        $this->setCanSaveWithChunk(false);

        $this->setMaxHealth(20);
        $this->setHealth(20);
    }

    abstract public function getPetType(): string;

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

    protected function entityBaseTick(int $tickDiff = 1): bool {
        $this->followOwner();

        if ($this->checkVal <= 0) {
            $this->checkVal = 60;

            $owner = $this->getPetOwner();

            if ($owner !== null) {
                $target = SimplePets::getInstance()->getPlayerByXuid($owner);

                if (($target !== null) && $this->getPosition()->distance($target->getPosition()) >= 20) {
                    $this->teleport($target->getPosition());
                }
            }
        }

        --$this->checkVal;
        return parent::entityBaseTick($tickDiff);
    }

    public function followOwner(): void {
        $owner = $this->getPetOwner();

        if ($owner === null) {
            return;
        }

        $target = SimplePets::getInstance()->getPlayerByXuid($owner);

        if ($target === null) {
            return;
        }

        if ($this->getPosition()->distance($target->getPosition()) <= 2) {
            return;
        }

        $x = $target->getLocation()->x - $this->getLocation()->x;
        $y = $target->getLocation()->y - $this->getLocation()->y;
        $z = $target->getLocation()->z - $this->getLocation()->z;
        /** @noinspection RandomApiMigrationInspection */
        if ($x * $x + $z * $z < mt_rand(3, 8)) {
            $this->motion->x = 0;
            $this->motion->z = 0;
        } else {
            $this->motion->x = 1 * 0.17 * ($x / (abs($x) + abs($z)));
            $this->motion->z = 1 * 0.17 * ($z / (abs($x) + abs($z)));
        }

        $this->getLocation()->yaw = rad2deg(atan2(-$x, $z));
        $this->getLocation()->pitch = rad2deg(-atan2($y, sqrt($x * $x + $z * $z)));

        $this->move($this->motion->x, $this->motion->y, $this->motion->z);
        $this->lookAt($target->getPosition());

        $this->updateMovement();
    }
}