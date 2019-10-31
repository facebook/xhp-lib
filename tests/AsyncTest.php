<?hh // strict
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

use function Facebook\FBExpect\expect;
use type Facebook\HackTest\DataProvider;

class :async:test extends :x:element {
  use XHPAsync;

  protected async function asyncRender(): Awaitable<XHPRoot> {
    return <div>{$this->getChildren()}</div>;
  }
}

class :test:xfrag-wrap extends :x:element {

  protected function render(): XHPRoot {
    return <x:frag>{$this->getChildren()}</x:frag>;
  }
}

class :test:async-xfrag-wrap extends :x:element {
  use XHPAsync;

  protected async function asyncRender(): Awaitable<XHPRoot> {
    return <x:frag>{$this->getChildren()}</x:frag>;
  }
}

class :async:par-test extends :x:element {
  use XHPAsync;

  attribute string label @required;

  public static Vector<(string, string)> $log = Vector {};

  protected async function asyncRender(): Awaitable<XHPRoot> {
    $label = $this->:label;
    self::$log[] = tuple($label, 'start');
    await RescheduleWaitHandle::create(RescheduleWaitHandle::QUEUE_DEFAULT, 0);
    self::$log[] = tuple($label, 'mid');
    await RescheduleWaitHandle::create(RescheduleWaitHandle::QUEUE_DEFAULT, 0);
    self::$log[] = tuple($label, 'finish');
    return <div>{$label}</div>;
  }
}

class AsyncTest extends Facebook\HackTest\HackTest {
  public function testDiv(): void {
    $xhp = <async:test>Herp</async:test>;
    expect($xhp->toString())->toBePHPEqual('<div>Herp</div>');
  }

  public function testXFrag(): void {
    $frag = <x:frag>{1}{2}</x:frag>;
    $xhp = <async:test>{$frag}</async:test>;
    expect($xhp->toString())->toBePHPEqual('<div>12</div>');
  }

  public function testNested(): void {
    $xhp = <async:test><async:test>herp derp</async:test></async:test>;
    expect($xhp->toString())->toBePHPEqual('<div><div>herp derp</div></div>');
  }

  public function testEmpty(): void {
    $xhp = <async:test />;
    expect($xhp->toString())->toBePHPEqual('<div></div>');
  }

  public function testNestedEmpty(): void {
    $xhp = <async:test><async:test /></async:test>;
    expect($xhp->toString())->toBePHPEqual('<div><div></div></div>');
  }

  public function testNestedWithNonAsyncChild(): void {
    $xhp = <async:test><b>BE BOLD</b></async:test>;
    expect($xhp->toString())->toBePHPEqual('<div><b>BE BOLD</b></div>');
  }

  public function testInstanceOfInterface(): void {
    $xhp = <async:test><b>BE BOLD</b></async:test>;
    expect($xhp)->toBeInstanceOf(XHPAwaitable::class);
  }

  public function parallelizationContainersProvider(): varray<varray<:xhp>> {
    return varray[varray[<test:xfrag-wrap />], varray[<test:async-xfrag-wrap />]];
  }

  <<DataProvider('parallelizationContainersProvider')>>
  public function testParallelization(:x:element $container): void {
    :async:par-test::$log = Vector {};

    $a = <async:par-test label="a" />;
    $b = <async:par-test label="b" />;
    $c = <async:par-test label="c" />;

    $container->replaceChildren(varray[$b, $c]);

    $tree = <async:test>{$a}{$container}</async:test>;
    expect($tree->toString())->toBeSame(
      '<div><div>a</div><div>b</div><div>c</div></div>',
    );

    $log = :async:par-test::$log;
    $by_node = Map { 'a' => Map {}, 'b' => Map {}, 'c' => Map {} };

    foreach ($log as $idx => $data) {
      list($label, $action) = $data;
      $by_node[$label][$action] = $idx;
    }

    $max_start = max($by_node->map($x ==> $x['start']));
    $min_mid = min($by_node->map($x ==> $x['mid']));
    $max_mid = max($by_node->map($x ==> $x['mid']));
    $min_finish = min($by_node->map($x ==> $x['finish']));

    expect($min_mid)->toBeGreaterThan(
      $max_start,
      'all should be started before any get continued',
    );
    expect($min_finish)->toBeGreaterThan(
      $max_mid,
      'all should have reached stage two before any finish',
    );
    expect($min_finish)->toBeGreaterThan(
      $max_start,
      'sanity check: all have started before any finish',
    );
  }
}
