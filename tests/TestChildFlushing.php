<?hh
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

use function Facebook\FBExpect\expect;

class :test:verbatim-root extends :x:element {
  attribute XHPRoot root @required;

  protected function render(): XHPRoot {
    return $this->:root;
  }
}

class :test:verbatim-root:async extends :x:element {
  use XHPAsync;

  attribute XHPRoot root @required;

  protected async function asyncRender(): Awaitable<XHPRoot> {
    return $this->:root;
  }
}

class XHPChildFlushTest extends PHPUnit_Framework_TestCase {
  public function xhpRootProvider() {
    return [
      [<div />, '<div></div>'],
      [<div><div /><div /></div>, '<div><div></div><div></div></div>'],
      [<test:verbatim-root root={<div />} />, '<div></div>'],
      [<x:frag><div /></x:frag>, '<div></div>'],
      [<x:frag><div /><div /></x:frag>, '<div></div><div></div>'],
      [
        <test:verbatim-root root={<x:frag><div /><div /></x:frag>} />,
        '<div></div><div></div>',
      ],
      [
        <test:verbatim-root root={<test:verbatim-root root={<div />} />} />,
        '<div></div>',
      ],
      [<test:verbatim-root:async root={<div />} />, '<div></div>'],
    ];
  }

  /**
   * @dataProvider xhpRootProvider
   */
  public function testSynchronous(XHPRoot $root, string $expected) {
    $elem = <test:verbatim-root />;
    $elem->setContext('root', $root);
    expect($elem->toString())->toBeSame($expected);
  }

  /**
   * @dataProvider xhpRootProvider
   */
  public function testAsynchronous(XHPRoot $root, string $expected) {
    $elem = <test:verbatim-root:async />;
    $elem->setContext('root', $root);
    expect($elem->toString())->toBeSame($expected);
  }
}
