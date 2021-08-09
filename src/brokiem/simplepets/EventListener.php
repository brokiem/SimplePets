<?php

/*
 * Copyright (c) 2021 broki
 * brokiem/SimplePets is licensed under the MIT License
 */

declare(strict_types=1);

namespace brokiem\simplepets;

use brokiem\simplepets\pets\base\BasePet;
use brokiem\simplepets\pets\base\CustomPet;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityTeleportEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\InteractPacket;
use pocketmine\network\mcpe\protocol\PlayerInputPacket;
use pocketmine\Player;

final class EventListener implements Listener {

    public function onJoin(PlayerJoinEvent $event): void {
        $player = $event->getPlayer();

        if ($player->hasPermission("simplenpc.notify") and !empty(SimplePets::getInstance()->cachedUpdate)) {
            [$latestVersion, $updateDate, $updateUrl] = SimplePets::getInstance()->cachedUpdate;

            if (SimplePets::getInstance()->getDescription()->getVersion() !== $latestVersion) {
                $player->sendMessage(" \n§aSimpleNPC §bv$latestVersion §ahas been released on §b$updateDate. §aDownload the new update at §b$updateUrl\n ");
            }
        }

        SimplePets::getInstance()->addPlayer($player);
        SimplePets::getInstance()->getDatabaseManager()->respawnPet($player);
    }

    public function onQuit(PlayerQuitEvent $event): void {
        $player = $event->getPlayer();

        if (isset(SimplePets::getInstance()->getPetManager()->getActivePets()[$player->getName()])) {
            foreach (SimplePets::getInstance()->getPetManager()->getActivePets()[$player->getName()] as $petName => $petId) {
                $pet = $player->getServer()->findEntity($petId);

                if ($pet instanceof BasePet || $pet instanceof CustomPet) {
                    $pet->despawn();
                    SimplePets::getInstance()->getPetManager()->removeActivePet($player, $petName);
                }
            }
        }

        SimplePets::getInstance()->removePlayer($player);
    }

    public function onDamage(EntityDamageEvent $event): void {
        $entity = $event->getEntity();

        if ($entity instanceof BasePet || $entity instanceof CustomPet) {
            $event->setCancelled();
        }
    }

    public function onDataPacket(DataPacketReceiveEvent $event): void {
        $packet = $event->getPacket();
        $player = $event->getPlayer();

        if ($packet instanceof PlayerInputPacket) {
            $pet = SimplePets::getInstance()->getPetManager()->getRiddenPet($player);
            if ($pet !== null) {
                $pet->walk($packet->motionX, $packet->motionY, $player);
            }
        } elseif ($packet instanceof InteractPacket) {
            if ($packet->action === InteractPacket::ACTION_LEAVE_VEHICLE) {
                $entity = $player->getServer()->findEntity($packet->target);

                if ($entity instanceof BasePet || $entity instanceof CustomPet) {
                    if ($entity->getRider()->getXuid() === $player->getXuid()) {
                        $entity->unlink();
                    }
                }
            }
        }
    }

    public function onTeleport(EntityTeleportEvent $event): void {
        $entity = $event->getEntity();

        if (!$entity instanceof Player) {
            return;
        }

        if (isset(SimplePets::getInstance()->getPetManager()->getActivePets()[$entity->getName()])) {
            foreach (SimplePets::getInstance()->getPetManager()->getActivePets()[$entity->getName()] as $petName => $petId) {
                $pet = $entity->getServer()->findEntity($petId);

                if ($pet instanceof BasePet || $pet instanceof CustomPet) {
                    $pet->teleport($entity->getLocation());
                }
            }
        }
    }
}