/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

use function Facebook\FBExpect\expect;

use namespace Facebook\XHP\Core as x;
use namespace Facebook\XHP\ChildValidation as XHPChild;
use type Facebook\XHP\{
  ReflectionXHPAttribute,
  ReflectionXHPChildrenDeclaration,
  ReflectionXHPClass,
};
use type Facebook\XHP\HTML\{a, code, div};

use namespace HH\Lib\{Dict, Str};

xhp class test:for_reflection extends x\element {
  use XHPChild\Validation;
  attribute
    string mystring @required,
    enum {'herp', 'derp'} myenum,
    string mystringwithdefault = 'mydefault';

  protected static function getChildrenDeclaration(): XHPChild\Constraint {
    return XHPChild\sequence(
      XHPChild\at_least_one_of(XHPChild\of_type<div>()),
      XHPChild\optional(
        XHPChild\sequence(XHPChild\of_type<code>(), XHPChild\of_type<a>()),
      ),
    );
  }

  category %herp_DEPRECATED, %derp_DEPRECATED;

  <<__Override>>
  public async function renderAsync(): Awaitable<x\node> {
    return <div />;
  }
}

class ReflectionTest extends Facebook\HackTest\HackTest {
  private ?ReflectionXHPClass $rxc;

  <<__Override>>
  public async function beforeEachTestAsync(): Awaitable<void> {
    $this->rxc = new ReflectionXHPClass(:test:for_reflection::class);
  }

  public function testClassName(): void {
    expect($this->rxc?->getClassName())->toEqual(:test:for_reflection::class);
  }

  public function testReflectionClass(): void {
    $rc = $this->rxc?->getReflectionClass();
    expect($rc)->toBeInstanceOf(ReflectionClass::class);
    expect($rc?->getName())->toEqual(:test:for_reflection::class);
  }

  public function testGetChildren(): void {
    $children = $this->rxc?->getChildren();
    expect($children)->toBeInstanceOf(ReflectionXHPChildrenDeclaration::class);
    expect($children?->__toString())->toEqual(
      Str\format('\\%s+,(\\%s,\\%s)?', div::class, code::class, a::class),
    );
  }

  public function testGetAttributes(): void {
    $attrs = $this->rxc?->getAttributes();
    expect($attrs)->toNotBeEmpty();
    expect(Dict\map($attrs as nonnull, ($attr ==> $attr->__toString())))
      ->toEqual(
        dict[
          'mystring' => 'string mystring @required',
          'myenum' => "enum {'herp', 'derp'} myenum",
          'mystringwithdefault' => "string mystringwithdefault = 'mydefault'",
        ],
      );
  }

  public function testGetCategories(): void {
    $categories = $this->rxc?->getCategories();
    expect($categories)->toEqual(keyset['herp_DEPRECATED', 'derp_DEPRECATED']);
  }
}
