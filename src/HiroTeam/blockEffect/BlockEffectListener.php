<?php

declare(strict_types=1);

namespace HiroTeam\blockEffect;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerMoveEvent;

// Modified by NhanAZ in 2026 to match API 5 block states.
class BlockEffectListener implements Listener {
	public function __construct(private BlockEffect $main){}

	public function onBlock(PlayerMoveEvent $event) : void{
		$player = $event->getPlayer();
		$stateId = $player->getWorld()->getBlock($event->getTo()->down())->getStateId();
		foreach($this->main->getRulesForStateId($stateId) as $effect){
			$player->getEffects()->add(clone $effect);
		}
	}
}
