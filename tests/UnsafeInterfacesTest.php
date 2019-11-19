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

// Please see MIGRATING.md for information on how these should be used in
// practice; please don't create/use classes as unsafe as these examples.

class ExampleUnsafeRenderable implements XHPUnsafeRenderable {
  public function __construct(public string $htmlString) {
  }

  public function toHTMLString(): string {
    return $this->htmlString;
  }
}

class ExampleVeryUnsafeRenderable
  extends ExampleUnsafeRenderable
  implements XHPUnsafeRenderable, XHPAlwaysValidChild {
}

class ExampleUnsafeAttribute extends XHPUnsafeAttributeValue {
  public function __construct(public string $htmlString) {
  }

  public function toHTMLString(): string {
    return $this->htmlString;
  }
}

class UnsafeInterfacesTest extends Facebook\HackTest\HackTest {
  public function testUnsafeRenderable(): void {
    $x = new ExampleUnsafeRenderable('<script>lollerskates</script>');
    $xhp = <div>{$x}</div>;
    expect($xhp->toString())->toEqual(
      '<div><script>lollerskates</script></div>',
    );
  }

  public function testInvalidChild(): void {
    expect(() ==> {
      $x = new ExampleUnsafeRenderable('foo');
      $xhp = <html>{$x}<body /></html>;
      $xhp->toString(); // validate, throw exception
    })->toThrow(XHPInvalidChildrenException::class);
  }

  public function testAlwaysValidChild(): void {
    $x = new ExampleVeryUnsafeRenderable('foo');
    $xhp = <html>{$x}<body /></html>;
    expect($xhp->toString())->toEqual('<html>foo<body></body></html>');
  }

  public function testUnsafeAttribute(): void {
    // without using XHPUnsafeAttributeValue, each &amp; will be double-escaped as &amp;amp;
    $attr = "foo &amp;&amp; bar";
    $xhp = <div onclick={$attr} />;
    expect($xhp->toString())->toEqual(
      '<div onclick="foo &amp;amp;&amp;amp; bar"></div>',
    );

    // using XHPUnsafeAttributeValue the &amp; is not double escaped
    $escaped = new ExampleUnsafeAttribute("foo &amp;&amp; bar");
    $xhp = <div onclick={$escaped} />;
    expect($xhp->toString())->toEqual(
      '<div onclick="foo &amp;&amp; bar"></div>',
    );
  }
}
