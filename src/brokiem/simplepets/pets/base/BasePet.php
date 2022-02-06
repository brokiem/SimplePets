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
use muqsit\invmenu\type\InvMenuTypeIds;
use pocketmine\block\Block;
use pocketmine\block\BlockLegacyIds;
use pocketmine\block\Carpet;
use pocketmine\block\Flowable;
use pocketmine\entity\Living;
use pocketmine\entity\Location;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\nbt\LittleEndianNbtSerializer;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\TreeRoot;
use pocketmine\network\mcpe\protocol\SetActorLinkPacket;
use pocketmine\network\mcpe\protocol\types\entity\EntityLink;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataFlags;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataProperties;
use pocketmine\player\Player;

abstract class BasePet extends Living {

    private ?string $petOwner = null;
    private ?string $petName = null;
    private float|int $petSize = 1;
    private bool $petBaby = false;
    private int $petVisibility = PetManager::VISIBLE_TO_EVERYONE;
    private bool $invEnabled = true;
    private bool $ridingEnabled = true;
    private ?string $extraData = null;
    private float|int $checkVal = 0;

    private ?string $rider = null;

    private InvMenu $petInventoryMenu;

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

        parent::__construct($location, $nbt);
        $this->setNameTagAlwaysVisible();
        $this->setCanSaveWithChunk(false);

        $this->setMaxHealth(20);
        $this->setHealth(20);
    }

    abstract public static function getNetworkTypeId(): string;

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

        $this->getNetworkProperties()->setGenericFlag(EntityMetadataFlags::BABY, $val);
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

                SimplePets::getInstance()->getPetManager()->removeRiddenPet($this->getRider(), $this);
            }

            $this->rider = null;
        }
    }

    public function getRider(): ?Player {
        return SimplePets::getInstance()->getPlayerByXuid($this->rider);
    }

    public function getName(): string {
        return $this->petName ?? "s_pet_no_name";
    }

    public function getInventoryMenu(): InvMenu {
        return $this->petInventoryMenu;
    }

    public function despawn(): void {
        if (!$this->isFlaggedForDespawn()) {
            $this->flagForDespawn();
        }
    }

    public function saveInventory(ListTag $petInventoryTag): void {
        $nbt = CompoundTag::create()->setTag("PetInventory", $petInventoryTag);
        $file = SimplePets::getInstance()->getDataFolder() . "pets_inventory/" . $this->getPetOwner() . "-" . $this->getName() . ".dat";
        file_put_contents($file, zlib_encode((new LittleEndianNbtSerializer())->write(new TreeRoot($nbt)), ZLIB_ENCODING_GZIP));
    }

    public function getSavedInventory(): ?CompoundTag {
        $file = SimplePets::getInstance()->getDataFolder() . "pets_inventory/" . $this->getPetOwner() . "-" . $this->getName() . ".dat";

        if (is_file($file)) {
            $decompressed = @zlib_decode(file_get_contents($file));
            return (new LittleEndianNbtSerializer())->read($decompressed)->mustGetCompoundTag();
        }

        return null;
    }

    // hehe thx blockpet
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

    public function flagForDespawn(): void {
        $this->saveNBT();

        parent::flagForDespawn();
    }

    protected function initEntity(CompoundTag $nbt): void {
        parent::initEntity($nbt);

        $this->petInventoryMenu = InvMenu::create(InvMenuTypeIds::TYPE_CHEST);

        $petInventoryTag = $this->getSavedInventory();
        if ($petInventoryTag !== null) {
            $inv = $petInventoryTag->getListTag("PetInventory");
            if ($inv !== null) {
                /** @var CompoundTag $item */
                foreach ($inv as $item) {
                    $this->petInventoryMenu->getInventory()->setItem($item->getByte("Slot"), Item::nbtDeserialize($item));
                }
            }
        }
    }

    public function saveNBT(): CompoundTag {
        $nbt = parent::saveNBT();

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

            $this->saveInventory(new ListTag($items, NBT::TAG_Compound));
        }

        return $nbt;
    }

    protected function entityBaseTick(int $tickDiff = 1): bool {
        if ($this->rider !== null or $this->isClosed()) {
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
        if ($this->getBlockInFront()->getId() !== BlockLegacyIds::AIR) {
            return $this->getBlockInFront(1)->getId() === BlockLegacyIds::AIR;
        }

        if ($this->getBlockInFront(-0.1) instanceof Carpet) {
            return true;
        }

        if ($this->getBlockInFront() instanceof Flowable) {
            return false;
        }

        return false;
    }

    public function getBlockInFront(float $y = 0): Block {
        $pos = $this->getPosition()->add($this->getDirectionVector()->x * $this->getScale(), $y, $this->getDirectionVector()->z * $this->getScale())->round();
        return $this->getWorld()->getBlock($pos);
    }
}