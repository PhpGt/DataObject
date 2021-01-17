<?php
namespace Gt\DataObject;

use DateTimeImmutable;
use DateTimeInterface;
use Gt\TypeSafeGetter\TypeSafeGetter;
use JsonSerializable;

class DataObject implements JsonSerializable, TypeSafeGetter {
	/** @var mixed[] */
	private array $data;

	public function __construct() {
		$this->data = [];
	}

	public function with(string $key, mixed $data):static {
		$clone = clone $this;
		$clone->data[$key] = $data;
		return $clone;
	}

	public function without(string $key):static {
		$clone = clone $this;
		unset($clone->data[$key]);
		return $clone;
	}

	public function get(string $name):mixed {
		return $this->data[$name] ?? null;
	}

	public function getString(string $name):?string {
		return $this->getAsType($name, "string");
	}

	public function getInt(string $name):?int {
		return $this->getAsType($name, "int");
	}

	public function getFloat(string $name):?float {
		return $this->getAsType($name, "float");
	}

	public function getBool(string $name):?bool {
		return $this->getAsType($name, "bool");
	}

	public function getDateTime(string $name):DateTimeInterface {
		return $this->getAsType($name, DateTimeInterface::class);
	}

	public function jsonSerialize():mixed {
		return $this->asArray();
	}

	/** @return mixed[] */
	public function asArray():array {
		$array = $this->data;

		array_walk_recursive($array, function(&$item):void {
			if($item instanceof static) {
				$item = $item->asArray();
			}
		});

		return $array;
	}

	public function asObject():object {
		$array = $this->data;

		array_walk_recursive($array, function(&$item):void {
			if($item instanceof static) {
				$item = $item->asObject();
			}
		});

		return (object)$array;
	}

	private function getAsType(
		string $name,
		string $type
	):mixed {
		$value = $this->get($name);
		if(is_null($value)) {
			return null;
		}

		switch($type) {
		case "int":
			return (int)$value;
		case "float":
			return (float)$value;
		case "string":
			return (string)$value;
		case "bool":
			return (bool)$value;
		}

		if(method_exists($this, "getAs$type")) {
			return call_user_func(
				[$this, "getAs$type"],
				$value
			);
		}

		return null;
	}

	private function getAsDateTimeInterface(mixed $value):DateTimeInterface {
		$dateTime = new DateTimeImmutable();

		if($value instanceof DateTimeInterface) {
			return $value;
		}
		elseif(is_int($value)) {
			$dateTime = $dateTime->setTimestamp($value);
		}
		elseif(is_float($value)) {
			$timestamp = (int)floor($value);
			$microsecond = ($value - $timestamp) * 1_000_000;
			$dateTime = $dateTime->setTimestamp($timestamp);
			$dateTime = $dateTime->setTime(
				(int)$dateTime->format("H"),
				(int)$dateTime->format("i"),
				(int)$dateTime->format("s"),
				(int)round($microsecond)
			);
		}
		else {
			$dateTime = new DateTimeImmutable($value);
		}

		return $dateTime;
	}
}