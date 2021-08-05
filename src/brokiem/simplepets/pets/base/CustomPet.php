<?php

/*
 * Copyright (c) 2021 broki
 * brokiem/SimplePets is licensed under the MIT License
 */

declare(strict_types=1);

namespace brokiem\simplepets\pets\base;

use brokiem\simplepets\manager\PetManager;
use brokiem\simplepets\SimplePets;
use muqsit\invmenu\InvMenu;
use muqsit\invmenu\MenuIds;
use pocketmine\block\Flowable;
use pocketmine\entity\Entity;
use pocketmine\entity\Human;
use pocketmine\item\Item;
use pocketmine\level\Location;
use pocketmine\math\Vector3;
use pocketmine\nbt\LittleEndianNBTStream;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\NamedTag;
use pocketmine\network\mcpe\protocol\SetActorLinkPacket;
use pocketmine\network\mcpe\protocol\types\EntityLink;
use pocketmine\Player;

abstract class CustomPet extends Human {

    private ?string $petOwner = null;
    private ?string $petName = null;
    private float $petSize = 1;
    private bool $petBaby = false;
    private int $petVisibility = PetManager::VISIBLE_TO_EVERYONE;
    private bool $invEnabled = true;
    private bool $ridingEnabled = true;
    private ?string $extraData = null;
    private float $checkVal = 0;

    private InvMenu $petInventoryMenu;

    private ?string $rider = null;

    public function __construct(Location $location, ?CompoundTag $nbt = null) {
        if ($nbt instanceof CompoundTag) {
            $this->petOwner = $nbt->getString("petOwner");
            $this->petName = $nbt->getString("petName");
            $this->petSize = $nbt->getFloat("petSize", 1);
            $this->petBaby = (bool)$nbt->getInt("petBaby", 0);
            $this->petVisibility = $nbt->getInt("petVisibility", PetManager::VISIBLE_TO_EVERYONE);
            $this->invEnabled = (bool)$nbt->getInt("invEnabled", 1);
            $this->ridingEnabled = (bool)$nbt->getInt("ridingEnabled", 1);
            $this->extraData = $nbt->getString("extraData") === "" ? null : $nbt->getString("extraData");
        }

        parent::__construct($location->getLevel(), $nbt);

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

    public function setPetBaby(bool $val): void {
        $this->petBaby = $val;

        $this->setGenericFlag(Entity::DATA_FLAG_BABY, $val);
    }

    public function isBabyPet(): bool {
        return $this->petBaby;
    }

    public function setPetVisibility(int $val): void {
        $this->petVisibility = $val;

        switch ($val) {
            case PetManager::VISIBLE_TO_EVERYONE:
                $this->despawnFromAll();
                $this->spawnToAll();
                break;
            case PetManager::VISIBLE_TO_OWNER:
                if ($this->getPetOwner() !== null) {
                    $owner = SimplePets::getInstance()->getPlayerByXuid($this->getPetOwner());

                    if ($owner !== null) {
                        $this->despawnFromAll();
                        $this->spawnTo($owner);
                    }
                }
                break;
            case PetManager::INVISIBLE_TO_EVERYONE:
                $this->despawnFromAll();
                break;
        }
    }

    public function getPetVisibility(): int {
        return $this->petVisibility;
    }

    public function setInvEnabled(bool $val): void {
        $this->invEnabled = $val;
    }

    public function isInvEnabled(): bool {
        return $this->invEnabled;
    }

    public function setRidingEnabled(bool $val): void {
        $this->ridingEnabled = $val;
    }

    public function isRidingEnabled(): bool {
        return $this->ridingEnabled;
    }

    public function getPetExtraData(): ?string {
        return $this->extraData;
    }

    public function getInventoryMenu(): InvMenu {
        return $this->petInventoryMenu;
    }

    public function link(Player $rider): void {
        $rider->setGenericFlag(Entity::DATA_FLAG_RIDING, true);
        $rider->getDataPropertyManager()->setVector3(Entity::DATA_RIDER_SEAT_POSITION, new Vector3(0, $this->height + 1, 0));

        $pk = new SetActorLinkPacket();
        $pk->link = new EntityLink($this->getId(), $rider->getId(), EntityLink::TYPE_RIDER, false, true);
        $rider->getServer()->broadcastPacket($this->getViewers(), $pk);

        SimplePets::getInstance()->getPetManager()->addRiddenPet($rider, $this);
        $this->rider = $rider->getXuid();
    }

    public function unlink(): void {
        if ($this->rider !== null) {
            if ($this->getRider() !== null) {
                $this->getRider()->setGenericFlag(Entity::DATA_FLAG_RIDING, false);
                $this->getRider()->getDataPropertyManager()->setVector3(Entity::DATA_RIDER_SEAT_POSITION, new Vector3(0, 0, 0));

                $pk = new SetActorLinkPacket();
                $pk->link = new EntityLink($this->getId(), $this->getRider()->getId(), EntityLink::TYPE_REMOVE, false, true);
                $this->getRider()->getServer()->broadcastPacket($this->getViewers(), $pk);

                SimplePets::getInstance()->getPetManager()->removeRiddenPet($this->getRider(), $this);
            }

            $this->rider = null;
        }
    }

    public function getRider(): ?Player {
        return SimplePets::getInstance()->getPlayerByXuid($this->rider);
    }

    public function despawn(): void {
        if (!$this->isFlaggedForDespawn()) {
            $this->flagForDespawn();
        }
    }

    public function saveInventory(ListTag $petInventoryTag): void {
        $nbt = new CompoundTag("PetInventory");
        $nbt->setTag($petInventoryTag);

        $file = SimplePets::getInstance()->getDataFolder() . "pets_inventory/" . $this->getPetOwner() . "-" . $this->getName() . ".dat";
        file_put_contents($file, (new LittleEndianNBTStream())->writeCompressed($nbt));
    }

    /** @return null|NamedTag|NamedTag[] */
    public function getSavedInventory() {
        $file = SimplePets::getInstance()->getDataFolder() . "pets_inventory/" . $this->getPetOwner() . "-" . $this->getName() . ".dat";

        if (is_file($file)) {
            return (new LittleEndianNBTStream())->readCompressed(file_get_contents($file));
        }

        return null;
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

        if ($this->shouldJump()) {
            $this->jump();
        }

        $this->move($finalMotionX, $this->motion->y, $finalMotionZ);
        $this->updateMovement();
    }

    protected function initEntity(): void {
        parent::initEntity();

        $this->petInventoryMenu = InvMenu::create(MenuIds::TYPE_CHEST);

        $petInventoryTag = $this->getSavedInventory();
        if ($petInventoryTag !== null) {
            /** @var CompoundTag $petInventoryTag */
            $inv = $petInventoryTag->getListTag("PetInventory");
            if ($inv !== null) {
                /** @var CompoundTag $item */
                foreach ($inv as $item) {
                    $this->petInventoryMenu->getInventory()->setItem($item->getByte("Slot"), Item::nbtDeserialize($item));
                }
            }
        }
    }

    public function saveNBT(): void {
        if ($this->petInventoryMenu !== null) {
            /** @var CompoundTag[] $items */
            $items = [];

            $slotCount = $this->petInventoryMenu->getInventory()->getSize();
            for ($slot = 0; $slot < $slotCount; ++$slot) {
                $item = $this->petInventoryMenu->getInventory()->getItem($slot);
                if (!$item->isNull()) {
                    $items[] = $item->nbtSerialize($slot);
                }
            }

            $this->saveInventory(new ListTag("PetInventory", $items));
        }
    }

    public function entityBaseTick(int $tickDiff = 1): bool {
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

        if ($this->shouldJump()) {
            $this->jump();
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

    public function shouldJump(): bool {
        $pos = $this->add($this->getDirectionVector()->x * $this->getScale(), 0, $this->getDirectionVector()->z * $this->getScale())->round();
        return $this->getLevelNonNull()->getBlock($pos)->getId() !== 0 and !$this->getLevelNonNull()->getBlock($pos) instanceof Flowable;
    }
}