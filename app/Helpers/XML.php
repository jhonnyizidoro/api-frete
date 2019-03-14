<?php

namespace App\Helpers;

use SimpleXMLElement;
use DOMDocument;
use stdClass;

class XML
{
	private static $instance = null;
	private static $xmlArray = null;
	
	public static function parse($xmlString)
	{
		$document = new DOMDocument;
		$document->loadXML($xmlString);
		$rootElement = $document->documentElement;
		$output = Self::nodeToArray($rootElement);
		$output['@root'] = $rootElement->tagName;
		if (Self::$instance === null) {
			Self::$instance = new Self;
		}
		Self::$xmlArray = $output;
		return Self::$instance;
	}
	
	public static function nodeToArray($node) {
		$output = [];
		switch ($node->nodeType) {
			case XML_CDATA_SECTION_NODE:
			case XML_TEXT_NODE:
				$output = trim($node->textContent);
				break;
			case XML_ELEMENT_NODE:
				for ($i = 0, $m = $node->childNodes->length; $i < $m; $i++) {
					$child = $node->childNodes->item($i);
					$v = Self::nodeToArray($child);
					if(isset($child->tagName)) {
						$tagName = $child->tagName;
						$tagName = explode(':', $tagName);
						$tagName = isset($tagName[1]) ? $tagName[1] : $tagName[0];
						if(!isset($output[$tagName])) {
							$output[$tagName] = [];
						}
						$output[$tagName][] = $v;
					}
					elseif($v || $v === '0') {
						$output = (string) $v;
					}
				}
				if($node->attributes->length && !is_array($output)) { //Has attributes but isn't an array
					$output = ['@content' => $output]; //Change output into an array.
				}
				if(is_array($output)) {
					if($node->attributes->length) {
						$a = [];
						foreach($node->attributes as $attrName => $attrNode) {
							$a[$attrName] = (string) $attrNode->value;
						}
						$output['@attributes'] = $a;
					}
					foreach ($output as $tagName => $v) {
						if(is_array($v) && count($v) === 1 && $tagName !== '@attributes') {
							$output[$tagName] = $v[0];
						}
					}
				}
				break;
		}
		return $output;
	}

	public function toArray()
	{
		return Self::$xmlArray;
	}

	public function toObject()
	{
		return Self::arrayToObject(Self::$xmlArray);
	}


	public function arrayToObject($array)
	{
		$object = new stdClass;
		foreach ($array as $key => $value) {
			if (is_array($value)) {
				if (sizeof($value) > 0) {
					$value = Self::arrayToObject($value);
				} else {
					$value = null;
				}
			}
			$object->{$key} = $value;
		}
		return $object;
	}
	
}