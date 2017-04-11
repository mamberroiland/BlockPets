<?php

namespace BlockHorizons\BlockPets\pets;

use pocketmine\block\Block;
use pocketmine\block\Slab;
use pocketmine\block\Stair;
use pocketmine\math\Math;
use pocketmine\math\Vector3;

abstract class WalkingPet extends BasePet {

	public function onUpdate($currentTick) {
		$petOwner = $this->getPetOwner();
		parent::onUpdate($currentTick);
		if($petOwner === null || $this->isRidden()) {
			return false;
		}

		$this->motionY -= $this->gravity;
		if(!$this->isOnGround()) {
			if($this->motionY > -$this->gravity * 4) {
				$this->motionY = -$this->gravity * 4;
			} else {
				$this->motionY -= $this->gravity;
			}
		} elseif($this->isCollidedHorizontally) {
			$this->jump();
		} else {
			$this->motionY -= $this->gravity;
		}

		$x = $petOwner->x - $this->x;
		$y = $petOwner->y - $this->y;
		$z = $petOwner->z - $this->z;

		if($x * $x + $z * $z < 5) {
			$this->motionX = 0;
			$this->motionZ = 0;
		} else {
			$this->motionX = $this->getSpeed() * 0.15 * ($x / (abs($x) + abs($z)));
			$this->motionZ = $this->getSpeed() * 0.15 * ($z / (abs($x) + abs($z)));
		}
		$this->yaw = rad2deg(atan2(-$x, $z));
		$this->pitch = rad2deg(-atan2($y, sqrt($x * $x + $z * $z)));

		$this->move($this->motionX, $this->motionY, $this->motionZ);
		$this->updateMovement();
		return true;
	}

	public function doRidingMovement($currentTick) {
		$rider = $this->getPetOwner();

		$this->pitch = 90;
		$this->yaw = $rider->yaw;
		if(!$this->isOnGround()) {
			if($this->motionY > -$this->gravity * 4) {
				$this->motionY = -$this->gravity * 4;
			} else {
				$this->motionY -= $this->gravity;
			}
		} elseif($this->isCollidedHorizontally) {
			$this->jump();
		} else {
			$this->motionY -= $this->gravity;
		}

		$x = $rider->getDirectionVector()->x;
		$z = $rider->getDirectionVector()->z;

		$this->motionX = $this->getSpeed() * 0.4 * ($x / (abs($x) + abs($z)));
		$this->motionZ = $this->getSpeed() * 0.4 * ($z / (abs($x) + abs($z)));

		$this->move($this->motionX, $this->motionY, $this->motionZ);
		$this->checkBlockCollision();

		$this->updateMovement();
	}

	protected function jump() {
		$posAhead = $this->getTargetBlock(3);
		$blockAhead = $this->getLevel()->getBlock(new Vector3($posAhead->x, Math::floorFloat($posAhead->y), $posAhead->z));
		if($blockAhead->isSolid()) {
			$this->motionY = $this->gravity * 8;
			$this->move($this->motionX, $this->motionY, $this->motionZ);
		} elseif($blockAhead instanceof Slab || $blockAhead instanceof Stair) {
			$this->motionY = $this->gravity * 4;
			$this->move($this->motionX, $this->motionY, $this->motionZ);
		}
	}
}
