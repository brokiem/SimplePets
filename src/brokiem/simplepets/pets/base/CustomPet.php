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
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\SetActorLinkPacket;
use pocketmine\network\mcpe\protocol\types\entity\EntityLink;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataFlags;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataProperties;
use pocketmine\player\Player;

abstract class CustomPet extends Human {

    private ?string $petOwner = null;
    private ?string $petName = null;
    private float|int $petSize = 1;
    private float|int $checkVal = 0;

    private ?string $rider = null;

    public function __construct(Location $location, Skin $skin, ?CompoundTag $nbt = null) {
        if ($nbt instanceof CompoundTag) {
            $this->petOwner = $nbt->getString("petOwner");
            $this->petName = $nbt->getString("petName");
            $this->petSize = $nbt->getFloat("petSize", 1);
        }

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

    public function link(Player $rider): void {
        $rider->getNetworkProperties()->setGenericFlag(EntityMetadataFlags::RIDING, true);
        $rider->getNetworkProperties()->setVector3(EntityMetadataProperties::RIDER_SEAT_POSITION, new Vector3(0, $this->getInitialSizeInfo()->getHeight() + 1, 0));

        $pk = new SetActorLinkPacket();
        $pk->link = new EntityLink($this->getId(), $rider->getId(), EntityLink::TYPE_RIDER, false, true);
        $rider->getServer()->broadcastPackets($this->getViewers(), [$pk]);

        SimplePets::getInstance()->getPetManager()->addRiddenPet($rider, $this);
        $this->rider = $rider->getXuid();
    }

    public function unlink(): void {
        if ($this->rider !== null) {
            if ($this->getRider() !== null) {
                $this->getRider()->getNetworkProperties()->setGenericFlag(EntityMetadataFlags::RIDING, false);
                $this->getRider()->getNetworkProperties()->setVector3(EntityMetadataProperties::RIDER_SEAT_POSITION, new Vector3(0, 0, 0));

                $pk = new SetActorLinkPacket();
                $pk->link = new EntityLink($this->getId(), $this->getRider()->getId(), EntityLink::TYPE_REMOVE, false, true);
                $this->getRider()->getServer()->broadcastPackets($this->getViewers(), [$pk]);
            }

            $this->rider = null;
        }
    }

    public function getRider(): ?Player {
        return SimplePets::getInstance()->getPlayerByXuid($this->rider);
    }

    public function walk(float $motionX, float $motionZ, Player $rider): void {
        $this->setRotation($rider->getLocation()->yaw, $rider->getLocation()->pitch);

        $direction_plane = $this->getDirectionPlane();
        $x = $direction_plane->x / 2.5;
        $z = $direction_plane->y / 2.5;

        switch ($motionZ) {
            case 1:
                $finalMotionX = $x;
                $finalMotionZ = $z;
                break;
            case -1:
                $finalMotionX = -$x;
                $finalMotionZ = -$z;
                break;
            default:
                $average = $x + $z / 2;
                $finalMotionX = $average / 1.414 * $motionZ;
                $finalMotionZ = $average / 1.414 * $motionX;
                break;
        }

        switch ($motionX) {
            case 1:
                $finalMotionX = $z;
                $finalMotionZ = -$x;
                break;
            case -1:
                $finalMotionX = -$z;
                $finalMotionZ = $x;
                break;
        }

        $this->move($finalMotionX, $this->motion->y, $finalMotionZ);
        $this->updateMovement();
    }

    protected function entityBaseTick(int $tickDiff = 1): bool {
        if ($this->rider !== null) {
            return parent::entityBaseTick($tickDiff);
        }

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