<?php

declare(strict_types=1);

namespace HiroTeam\blockEffect;

use pocketmine\data\bedrock\block\BlockStateDeserializeException;
use pocketmine\data\bedrock\EffectIdMap;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\plugin\PluginBase;
use pocketmine\world\format\io\GlobalBlockStateHandlers;

// Modified by NhanAZ in 2026 to support API 5 block states and validate rules.
class BlockEffect extends PluginBase {
	/** @var array<int, list<EffectInstance>> */
	private array $rules = [];

	protected function onEnable() : void{
		$this->saveDefaultConfig();
		$count = 0;
		foreach($this->getConfig()->getAll() as $name => $rule){
			if(!is_array($rule) || !isset($rule['block'], $rule['effect'], $rule['visible']) || !is_string($rule['block']) || !is_string($rule['effect'])){
				$this->getLogger()->warning("Ignoring invalid rule $name");
				continue;
			}
			if(!preg_match('/^(\d+):(\d+)$/D', $rule['block'], $blockMatch) || !preg_match('/^(\d+):(\d+):(\d+)$/D', $rule['effect'], $effectMatch)){
				$this->getLogger()->warning("Ignoring malformed block or effect in rule $name");
				continue;
			}
			$blockId = filter_var($blockMatch[1], FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
			$blockMeta = filter_var($blockMatch[2], FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
			$effectId = filter_var($effectMatch[1], FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
			$seconds = filter_var($effectMatch[2], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 107374182]]);
			$amplifier = filter_var($effectMatch[3], FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 255]]);
			$visible = is_bool($rule['visible']) || is_string($rule['visible']) ? filter_var($rule['visible'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) : null;
			if($blockId === false || $blockMeta === false || $effectId === false || $seconds === false || $amplifier === false || $visible === null){
				$this->getLogger()->warning("Ignoring out-of-range values in rule $name");
				continue;
			}
			$effect = EffectIdMap::getInstance()->fromId($effectId);
			if($effect === null){
				$this->getLogger()->warning("Ignoring unknown effect in rule $name");
				continue;
			}
			try{
				$stateData = GlobalBlockStateHandlers::getUpgrader()->upgradeIntIdMeta($blockId, $blockMeta);
				$stateId = GlobalBlockStateHandlers::getDeserializer()->deserialize($stateData);
			}catch(BlockStateDeserializeException $e){
				$this->getLogger()->warning("Ignoring unknown block in rule $name: " . $e->getMessage());
				continue;
			}
			$this->rules[$stateId][] = new EffectInstance($effect, $seconds * 20, $amplifier, $visible);
			++$count;
		}
		$this->getServer()->getPluginManager()->registerEvents(new BlockEffectListener($this), $this);
		$this->getLogger()->info("Loaded $count block effect rules");
	}

	/** @return list<EffectInstance> */
	public function getRulesForStateId(int $stateId) : array{
		return $this->rules[$stateId] ?? [];
	}
}
