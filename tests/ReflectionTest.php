<?hh // partial
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

use function Facebook\FBExpect\expect;

class :test:for-reflection extends :x:element {
  attribute
    string mystring @required,
    enum {'herp', 'derp'} myenum,
    string mystringwithdefault = 'mydefault';
  children (:div+, (:code, :a)?);
  category %herp, %derp;

  public function render(): XHPRoot {
    return <div />;
  }
}

class ReflectionTest extends Facebook\HackTest\HackTest {
  private ?ReflectionXHPClass $rxc;

  public async function beforeEachTestAsync(): Awaitable<void> {
    $this->rxc = new ReflectionXHPClass(:test:for-reflection::class);
  }

  public function testClassName(): void {
    expect($this->rxc?->getClassName())->toEqual(:test:for-reflection::class);
  }

  public function testElementName(): void {
    expect($this->rxc?->getElementName())->toEqual('test:for-reflection');
  }

  public function testReflectionClass(): void {
    $rc = $this->rxc?->getReflectionClass();
    expect($rc)->toBeInstanceOf(ReflectionClass::class);
    expect($rc?->getName())->toEqual(:test:for-reflection::class);
  }

  public function testGetChildren(): void {
    $children = $this->rxc?->getChildren();
    expect($children)->toBeInstanceOf(ReflectionXHPChildrenDeclaration::class);
    expect($children?->__toString())->toEqual('(:div+,(:code,:a)?)');
  }

  public function testGetAttributes(): void {
    $attrs = $this->rxc?->getAttributes();
    expect($attrs)->toNotBeEmpty();
    expect($attrs?->map($attr ==> /* HH_FIXME[4281] */(string)$attr))
      ->toHaveSameContentAs(
        Map {
          'mystring' => 'string mystring @required',
          'myenum' => "enum {'herp', 'derp'} myenum",
          'mystringwithdefault' => "string mystringwithdefault = 'mydefault'",
        },
      );
  }

  public function testGetCategories(): void {
    $categories = $this->rxc?->getCategories();
    expect($categories)->toHaveSameContentAs(Set {'herp', 'derp'});
  }
}
