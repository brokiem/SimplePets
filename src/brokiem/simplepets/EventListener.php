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
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;

final class EventListener implements Listener {

    public function onJoin(PlayerJoinEvent $event): void {
        $player = $event->getPlayer();

        SimplePets::getInstance()->addPlayer($player);
        SimplePets::getInstance()->getDatabaseManager()->respawnPet($player);
    }

    public function onQuit(PlayerQuitEvent $event): void {
        $player = $event->getPlayer();

        if (isset(SimplePets::getInstance()->getPetManager()->getActivePets()[$player->getName()])) {
            foreach (SimplePets::getInstance()->getPetManager()->getActivePets()[$player->getName()] as $petName => $petId) {
                $pet = $player->getServer()->getWorldManager()->findEntity($petId);

                if ($pet instanceof BasePet || $pet instanceof CustomPet) {
                    SimplePets::getInstance()->getPetManager()->despawnPet($pet);
                    SimplePets::getInstance()->getPetManager()->removeActivePet($player, $petName);
                }
            }
        }

        SimplePets::getInstance()->removePlayer($player);
    }

    public function onDamage(EntityDamageEvent $event): void {
        $entity = $event->getEntity();

        if ($entity instanceof BasePet || $entity instanceof CustomPet) {
            $event->cancel();
        }
    }
}