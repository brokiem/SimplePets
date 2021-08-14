<?php

/*
 * Copyright (c) 2021 broki
 * brokiem/SimplePets is licensed under the MIT License
 */

declare(strict_types=1);

namespace brokiem\simplepets\manager;

use brokiem\simplepets\pets\ArmorstandPet;
use brokiem\simplepets\pets\ArrowPet;
use brokiem\simplepets\pets\base\BasePet;
use brokiem\simplepets\pets\base\CustomPet;
use brokiem\simplepets\pets\BatPet;
use brokiem\simplepets\pets\BeePet;
use brokiem\simplepets\pets\BlazePet;
use brokiem\simplepets\pets\BoatPet;
use brokiem\simplepets\pets\CatPet;
use brokiem\simplepets\pets\CavespiderPet;
use brokiem\simplepets\pets\ChestminecartPet;
use brokiem\simplepets\pets\ChickenPet;
use brokiem\simplepets\pets\CodPet;
use brokiem\simplepets\pets\CommandblockminecartPet;
use brokiem\simplepets\pets\CowPet;
use brokiem\simplepets\pets\CreeperPet;
use brokiem\simplepets\pets\DolphinPet;
use brokiem\simplepets\pets\DonkeyPet;
use brokiem\simplepets\pets\DragonfireballPet;
use brokiem\simplepets\pets\DrownedPet;
use brokiem\simplepets\pets\EggPet;
use brokiem\simplepets\pets\ElderguardianghostPet;
use brokiem\simplepets\pets\ElderguardianPet;
use brokiem\simplepets\pets\EndercrystalPet;
use brokiem\simplepets\pets\EnderdragonPet;
use brokiem\simplepets\pets\EndermanPet;
use brokiem\simplepets\pets\EndermitePet;
use brokiem\simplepets\pets\EnderpearlPet;
use brokiem\simplepets\pets\EvocationfangPet;
use brokiem\simplepets\pets\EvocationillagerPet;
use brokiem\simplepets\pets\EyeofendersignalPet;
use brokiem\simplepets\pets\FireballPet;
use brokiem\simplepets\pets\FoxPet;
use brokiem\simplepets\pets\GhastPet;
use brokiem\simplepets\pets\GuardianPet;
use brokiem\simplepets\pets\HoglinPet;
use brokiem\simplepets\pets\HopperminecartPet;
use brokiem\simplepets\pets\HorsePet;
use brokiem\simplepets\pets\HuskPet;
use brokiem\simplepets\pets\IrongolemPet;
use brokiem\simplepets\pets\LingeringpotionPet;
use brokiem\simplepets\pets\LlamaPet;
use brokiem\simplepets\pets\MagmacubePet;
use brokiem\simplepets\pets\MinecartPet;
use brokiem\simplepets\pets\MooshroomPet;
use brokiem\simplepets\pets\MulePet;
use brokiem\simplepets\pets\OcelotPet;
use brokiem\simplepets\pets\PandaPet;
use brokiem\simplepets\pets\ParrotPet;
use brokiem\simplepets\pets\PhantomPet;
use brokiem\simplepets\pets\PiglinPet;
use brokiem\simplepets\pets\PigPet;
use brokiem\simplepets\pets\PillagerPet;
use brokiem\simplepets\pets\PolarbearPet;
use brokiem\simplepets\pets\PufferfishPet;
use brokiem\simplepets\pets\RabbitPet;
use brokiem\simplepets\pets\RavagerPet;
use brokiem\simplepets\pets\SalmonPet;
use brokiem\simplepets\pets\SheepPet;
use brokiem\simplepets\pets\ShulkerbulletPet;
use brokiem\simplepets\pets\ShulkerPet;
use brokiem\simplepets\pets\SilverfishPet;
use brokiem\simplepets\pets\SkeletonhorsePet;
use brokiem\simplepets\pets\SkeletonPet;
use brokiem\simplepets\pets\SlimePet;
use brokiem\simplepets\pets\SmallfireballPet;
use brokiem\simplepets\pets\SnowballPet;
use brokiem\simplepets\pets\SnowgolemPet;
use brokiem\simplepets\pets\SpiderPet;
use brokiem\simplepets\pets\SplashpotionPet;
use brokiem\simplepets\pets\SquidPet;
use brokiem\simplepets\pets\StrayPet;
use brokiem\simplepets\pets\StriderPet;
use brokiem\simplepets\pets\TntminecartPet;
use brokiem\simplepets\pets\TntPet;
use brokiem\simplepets\pets\TropicalfishPet;
use brokiem\simplepets\pets\TurtlePet;
use brokiem\simplepets\pets\VexPet;
use brokiem\simplepets\pets\VillagerPet;
use brokiem\simplepets\pets\Villagerv2Pet;
use brokiem\simplepets\pets\VindicatorPet;
use brokiem\simplepets\pets\WanderingtraderPet;
use brokiem\simplepets\pets\WitchPet;
use brokiem\simplepets\pets\WitherPet;
use brokiem\simplepets\pets\WitherskeletonPet;
use brokiem\simplepets\pets\WitherskullPet;
use brokiem\simplepets\pets\WolfPet;
use brokiem\simplepets\pets\ZoglinPet;
use brokiem\simplepets\pets\ZombiehorsePet;
use brokiem\simplepets\pets\ZombiePet;
use brokiem\simplepets\pets\ZombiepigmanPet;
use brokiem\simplepets\pets\ZombievillagerPet;
use brokiem\simplepets\pets\Zombievillagerv2Pet;
use brokiem\simplepets\SimplePets;
use pocketmine\entity\Entity;
use pocketmine\level\Location;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\Player;

final class PetManager {

    private array $default_pets = [
        "ArmorstandPet" => ArmorstandPet::class,
        "ArrowPet" => ArrowPet::class,
        "BatPet" => BatPet::class,
        "BeePet" => BeePet::class,
        "BlazePet" => BlazePet::class,
        "BoatPet" => BoatPet::class,
        "CatPet" => CatPet::class,
        "CavespiderPet" => CavespiderPet::class,
        "ChestminecartPet" => ChestminecartPet::class,
        "ChickenPet" => ChickenPet::class,
        "CodPet" => CodPet::class,
        "CommandblockminecartPet" => CommandblockminecartPet::class,
        "CowPet" => CowPet::class,
        "CreeperPet" => CreeperPet::class,
        "DolphinPet" => DolphinPet::class,
        "DonkeyPet" => DonkeyPet::class,
        "DragonfireballPet" => DragonfireballPet::class,
        "DrownedPet" => DrownedPet::class,
        "EggPet" => EggPet::class,
        "ElderguardianPet" => ElderguardianPet::class,
        "ElderguardianghostPet" => ElderguardianghostPet::class,
        "EndercrystalPet" => EndercrystalPet::class,
        "EnderdragonPet" => EnderdragonPet::class,
        "EndermanPet" => EndermanPet::class,
        "EndermitePet" => EndermitePet::class,
        "EnderpearlPet" => EnderpearlPet::class,
        "EvocationfangPet" => EvocationfangPet::class,
        "EvocationillagerPet" => EvocationillagerPet::class,
        "EyeofendersignalPet" => EyeofendersignalPet::class,
        "FireballPet" => FireballPet::class,
        "FoxPet" => FoxPet::class,
        "GhastPet" => GhastPet::class,
        "GuardianPet" => GuardianPet::class,
        "HoglinPet" => HoglinPet::class,
        "HopperminecartPet" => HopperminecartPet::class,
        "HorsePet" => HorsePet::class,
        "HuskPet" => HuskPet::class,
        "IrongolemPet" => IrongolemPet::class,
        "LingeringpotionPet" => LingeringpotionPet::class,
        "LlamaPet" => LlamaPet::class,
        "MagmacubePet" => MagmacubePet::class,
        "MinecartPet" => MinecartPet::class,
        "MooshroomPet" => MooshroomPet::class,
        "MulePet" => MulePet::class,
        "OcelotPet" => OcelotPet::class,
        "PandaPet" => PandaPet::class,
        "ParrotPet" => ParrotPet::class,
        "PhantomPet" => PhantomPet::class,
        "PigPet" => PigPet::class,
        "PiglinPet" => PiglinPet::class,
        "PillagerPet" => PillagerPet::class,
        "PolarbearPet" => PolarbearPet::class,
        "PufferfishPet" => PufferfishPet::class,
        "RabbitPet" => RabbitPet::class,
        "RavagerPet" => RavagerPet::class,
        "SalmonPet" => SalmonPet::class,
        "SheepPet" => SheepPet::class,
        "ShulkerPet" => ShulkerPet::class,
        "ShulkerbulletPet" => ShulkerbulletPet::class,
        "SilverfishPet" => SilverfishPet::class,
        "SkeletonPet" => SkeletonPet::class,
        "SkeletonhorsePet" => SkeletonhorsePet::class,
        "SlimePet" => SlimePet::class,
        "SmallfireballPet" => SmallfireballPet::class,
        "SnowballPet" => SnowballPet::class,
        "SnowgolemPet" => SnowgolemPet::class,
        "SpiderPet" => SpiderPet::class,
        "SplashpotionPet" => SplashpotionPet::class,
        "SquidPet" => SquidPet::class,
        "StrayPet" => StrayPet::class,
        "StriderPet" => StriderPet::class,
        "TntPet" => TntPet::class,
        "TntminecartPet" => TntminecartPet::class,
        "TropicalfishPet" => TropicalfishPet::class,
        "TurtlePet" => TurtlePet::class,
        "VexPet" => VexPet::class,
        "VillagerPet" => VillagerPet::class,
        "Villagerv2Pet" => Villagerv2Pet::class,
        "VindicatorPet" => VindicatorPet::class,
        "WanderingtraderPet" => WanderingtraderPet::class,
        "WitchPet" => WitchPet::class,
        "WitherPet" => WitherPet::class,
        "WitherskeletonPet" => WitherskeletonPet::class,
        "WitherskullPet" => WitherskullPet::class,
        "WolfPet" => WolfPet::class,
        "ZoglinPet" => ZoglinPet::class,
        "ZombiePet" => ZombiePet::class,
        "ZombiehorsePet" => ZombiehorsePet::class,
        "ZombiepigmanPet" => ZombiepigmanPet::class,
        "ZombievillagerPet" => ZombievillagerPet::class,
        "Zombievillagerv2Pet" => Zombievillagerv2Pet::class
    ];

    private array $registered_pets = [];
    private array $active_pets = [];
    private array $ridden_pet = [];

    public const VISIBLE_TO_EVERYONE = 0;
    public const VISIBLE_TO_OWNER = 1;
    public const INVISIBLE_TO_EVERYONE = 3;

    public function __construct() {
        foreach ($this->default_pets as $type => $class) {
            Entity::registerEntity($class, true, [$type]);
            $this->registerPet($type, $class);
        }
    }

    public function registerPet(string $type, string $class): void {
        if (!class_exists($class)) {
            throw new \RuntimeException("Class $class not found.");
        }

        $refClass = new \ReflectionClass($class);
        if (is_a($class, BasePet::class, true) || is_a($class, CustomPet::class, true) and !$refClass->isAbstract()) {
            $this->registered_pets[$type] = $class;
        }
    }

    public function spawnPet(Player $owner, string $petType, string $petName, float $petSize = 1, bool $petBaby = false, int $petVis = PetManager::VISIBLE_TO_EVERYONE, bool $enableInv = true, bool $enableRiding = true, ?string $extraData = "null"): void {
        $nbt = Entity::createBaseNBT($owner->getPosition());
        $nbt->setString("petOwner", $owner->getXuid());
        $nbt->setString("petName", $petName);
        $nbt->setFloat("petSize", $petSize);
        $nbt->setInt("petBaby", (int)$petBaby);
        $nbt->setInt("petVisibility", $petVis);
        $nbt->setInt("invEnabled", (int)$enableInv);
        $nbt->setInt("ridingEnabled", (int)$enableRiding);
        $nbt->setString("extraData", $extraData ?? "null");
        $pet = $this->createEntity($petType, $owner->getLocation(), $nbt);

        if ($pet !== null) {
            $pet->setPetName($petName);
            $pet->setPetBaby($petBaby);
            $pet->setPetVisibility($petVis);
            $pet->spawnToAll();

            $this->active_pets[$owner->getName()][$pet->getPetName()] = $pet->getId();
            SimplePets::getInstance()->getDatabaseManager()->registerPet($pet);
        }
    }

    public function respawnPet(Player $owner, string $petType, string $petName, float $petSize = 1, bool $petBaby = false, int $petVis = PetManager::VISIBLE_TO_EVERYONE, bool $enableInv = true, bool $enableRiding = true, ?string $extraData = "null"): void {
        $nbt = Entity::createBaseNBT($owner->getPosition());
        $nbt->setString("petOwner", $owner->getXuid());
        $nbt->setString("petName", $petName);
        $nbt->setFloat("petSize", $petSize);
        $nbt->setInt("petBaby", (int)$petBaby);
        $nbt->setInt("petVisibility", $petVis);
        $nbt->setInt("invEnabled", (int)$enableInv);
        $nbt->setInt("ridingEnabled", (int)$enableRiding);
        $nbt->setString("extraData", $extraData ?? "null");
        $pet = $this->createEntity($petType, $owner->getLocation(), $nbt);

        if ($pet !== null) {
            $pet->setPetName($petName);
            $pet->setPetBaby($petBaby);
            $pet->setPetVisibility($petVis);
            $pet->spawnToAll();

            $this->active_pets[$owner->getName()][$pet->getPetName()] = $pet->getId();
        }
    }

    /**
     * @param Player $owner
     * @var BasePet|CustomPet $pet
     */
    public function addRiddenPet(Player $owner, $pet): void {
        $this->ridden_pet[$owner->getName()] = $pet;
    }

    public function getRegisteredPets(): array {
        return $this->registered_pets;
    }

    public function getActivePets(): array {
        return $this->active_pets;
    }

    /** @return null|BasePet|CustomPet */
    public function getRiddenPet(Player $owner) {
        return $this->ridden_pet[$owner->getName()] ?? null;
    }

    /**
     * @param Player $owner
     * @var BasePet|CustomPet $pet
     */
    public function removeRiddenPet(Player $owner, $pet): void {
        $this->ridden_pet[$owner->getName()] = $pet;
    }

    public function removeActivePet(Player $owner, string $petName): bool {
        if (isset($this->active_pets[$owner->getName()][$petName])) {
            unset($this->active_pets[$owner->getName()][$petName]);
            return true;
        }

        return false;
    }

    /** @return  BasePet|CustomPet $pet */
    public function createEntity(string $type, Location $location, CompoundTag $nbt) {
        if (isset($this->registered_pets[$type])) {
            /** @var BasePet|CustomPet $class */
            $class = $this->registered_pets[$type];

            if (is_a($class, BasePet::class, true) || is_a($class, CustomPet::class, true)) {
                return new $class($location, $nbt);
            }
        }

        return null;
    }
}