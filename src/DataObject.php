<?php
namespace Gt\DataObject;

use DateTimeInterface;
use Gt\TypeSafeGetter\TypeSafeGetter;
use JsonSerializable;

class DataObject implements JsonSerializable, TypeSafeGetter {
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
		return $this->getAsScalar($name, "string");
	}

	public function getInt(string $name):?int {
		return $this->getAsScalar($name, "int");
	}

	public function getFloat(string $name):?float {
		return $this->getAsScalar($name, "float");
	}

	public function getBool(string $name):?bool {
		// TODO: Implement getBool() method.
	}

	public function getDateTime(string $name):DateTimeInterface {
		// TODO: Implement getDateTime() method.
	}

	public function jsonSerialize():mixed {
		return $this->asArray();
	}

	public function asArray(bool $nested = true):array {

	}

	public function asObject(bool $nested = true):object {
		return (object)$this->asArray($nested);
	}

	private function getAsScalar(
		string $name,
		string $type
	):float|null|bool|int|string {
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
	}
}