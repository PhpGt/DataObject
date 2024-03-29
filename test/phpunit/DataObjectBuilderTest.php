<?php
namespace Gt\DataObject\Test;

use Gt\DataObject\AssociativeArrayWithinObjectException;
use Gt\DataObject\DataObjectBuilder;
use Gt\DataObject\ObjectWithinAssociativeArrayException;
use Gt\DataObject\Test\Helper\CustomDataObject;
use PHPUnit\Framework\TestCase;
use stdClass;

class DataObjectBuilderTest extends TestCase {
	public function testFromObjectSimple() {
		$obj = new StdClass();
		$obj->key1 = "value1";
		$obj->key2 = "value2";

		$sut = new DataObjectBuilder();
		$output = $sut->fromObject($obj);

		self::assertEquals("value1", $output->getString("key1"));
		self::assertEquals("value2", $output->getString("key2"));
	}

	public function testFromObjectNested() {
		$obj = new StdClass();
		$obj->key1 = "value1";
		$obj->key2 = "value2";
		$obj->nested = new StdClass();
		$obj->nested->key3 = "value3";
		$obj->nested->key4 = "value4";

		$sut = new DataObjectBuilder();
		$output = $sut->fromObject($obj);

		self::assertEquals("value1", $output->getString("key1"));
		self::assertEquals("value2", $output->getString("key2"));
		$nestedOutput = $output->get("nested");
		self::assertIsObject($nestedOutput);
		self::assertEquals("value3", $nestedOutput->getString("key3"));
		self::assertEquals("value4", $nestedOutput->getString("key4"));
	}

	public function testFromObjectNestedWithCustomClass() {
		$obj = new StdClass();
		$obj->key1 = "value1";
		$obj->key2 = "value2";
		$obj->nested = new StdClass();
		$obj->nested->deepNested = new StdClass();
		$obj->nested->key3 = "value3";
		$obj->nested->key4 = "value4";

		$sut = new DataObjectBuilder();
		$output = $sut->fromObject($obj, CustomDataObject::class);

		$nestedOutput = $output->get("nested");
		$deepNestedOutput = $nestedOutput->getObject("deepNested");
		self::assertIsObject($nestedOutput);
		self::assertInstanceOf(CustomDataObject::class, $nestedOutput);
		self::assertInstanceOf(CustomDataObject::class, $deepNestedOutput);
	}

	public function testFromObjectNestedArray() {
		$obj = new StdClass();
		$obj->key1 = "value1";
		$obj->key2 = "value2";
		$obj->nested = new StdClass();
		$obj->nested->key3 = "value3";
		$obj->nested->key4 = "value4";

		$innerObj1 = new StdClass();
		$innerObj1->key5 = "value5";
		$innerObj2 = new StdClass();
		$innerObj2->key6 = "value6";
		$obj->nested->arr = array(
			$innerObj1,
			$innerObj2,
		);

		$sut = new DataObjectBuilder();
		$output = $sut->fromObject($obj);

		self::assertEquals("value1", $output->getString("key1"));
		self::assertEquals("value2", $output->getString("key2"));
		$nestedOutput = $output->get("nested");
		self::assertIsObject($nestedOutput);
		self::assertEquals("value3", $nestedOutput->getString("key3"));
		self::assertEquals("value4", $nestedOutput->getString("key4"));
		$nestedArray = $nestedOutput->get("arr");
		self::assertIsArray($nestedArray);
		self::assertEquals("value5", $nestedArray[0]->getString("key5"));
		self::assertEquals("value6", $nestedArray[1]->getString("key6"));
	}

	public function testFromArraySimple() {
		$array = array(
			"key1" => "value1",
			"key2" => "value2",
		);

		$sut = new DataObjectBuilder();
		$output = $sut->fromAssociativeArray($array);

		self::assertEquals("value1", $output->getString("key1"));
		self::assertEquals("value2", $output->getString("key2"));
	}

	public function testFromArrayNested() {
		$array = array(
			"key1" => "value1",
			"key2" => "value2",
			"nested" => [
				"key3" => "value3",
				"key4" => "value4",
			]
		);

		$sut = new DataObjectBuilder();
		$output = $sut->fromAssociativeArray($array);

		self::assertEquals("value1", $output->getString("key1"));
		self::assertEquals("value2", $output->getString("key2"));
		$nestedOutput = $output->get("nested");
		self::assertIsObject($nestedOutput);
		self::assertEquals("value3", $nestedOutput->getString("key3"));
		self::assertEquals("value4", $nestedOutput->getString("key4"));
	}

	public function testFromArrayNestedArray() {
		$array = array(
			"key1" => "value1",
			"key2" => "value2",
			"nested" => [
				"key3" => "value3",
				"key4" => "value4",
				"arr" => [
					["key5" => "value5"],
					["key6" => "value6"],
				]
			]
		);

		$sut = new DataObjectBuilder();
		$output = $sut->fromAssociativeArray($array);

		self::assertEquals("value1", $output->getString("key1"));
		self::assertEquals("value2", $output->getString("key2"));
		$nestedOutput = $output->get("nested");
		self::assertIsObject($nestedOutput);
		self::assertEquals("value3", $nestedOutput->getString("key3"));
		self::assertEquals("value4", $nestedOutput->getString("key4"));
		$nestedArray = $nestedOutput->get("arr");
		self::assertIsArray($nestedArray);
		self::assertEquals("value5", $nestedArray[0]->getString("key5"));
		self::assertEquals("value6", $nestedArray[1]->getString("key6"));
	}

	public function testMixingAssociativeArrayInObjectThrowsError() {
		$object = new StdClass();
		$object->key1 = "value1";
		$object->assoc = [
			"key2" => "value2",
			"key3" => "value3",
		];

		$sut = new DataObjectBuilder();
		self::expectException(AssociativeArrayWithinObjectException::class);
		$sut->fromObject($object);
	}

	public function testMixingObjectInAssociativeArrayThrowsError() {
		$object = new StdClass();
		$object->key2 = "value2";
		$object->key3 = "value3";

		$array = array(
			"key1" => "value1",
			"obj" => $object,
		);
		$sut = new DataObjectBuilder();
		self::expectException(ObjectWithinAssociativeArrayException::class);
		$sut->fromAssociativeArray($array);
	}

	public function testEmptyNestedArray():void {
		$array = array (
			"key1" => "value1",
			"key2" => "value2",
			"nested" => [],
		);
		$sut = new DataObjectBuilder();
		$output = $sut->fromAssociativeArray($array);

		self::assertSame("value2", $output->getString("key2"));
		self::assertSame([], $output->getArray("nested"));
	}

	public function testEmptyNestedArrayInObject():void {
		$object = (object)[
			"key1" => "value1",
			"key2" => "value2",
			"nested" => [],
		];
		$sut = new DataObjectBuilder();
		$output = $sut->fromObject($object);

		self::assertSame("value2", $output->getString("key2"));
		self::assertSame([], $output->getArray("nested"));
	}
}
