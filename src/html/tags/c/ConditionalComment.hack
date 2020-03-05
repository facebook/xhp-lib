/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */


use namespace Facebook\XHP\Elements\Core as x;
use namespace HH\Lib\{Str, Vec};

/**
 * Render an HTML conditional comment. You can choose whatever you like as
 * the conditional statement.
 */
use namespace Facebook\XHP\ChildValidation as XHPChild;

xhp class x:conditional_comment extends x\primitive {
  use XHPChildValidation;
  attribute string if @required;

  protected static function getChildrenDeclaration(): XHPChild\Constraint {
    return XHPChild\anyNumberOf(
      XHPChild\anyOf(XHPChild\pcdata(), XHPChild\ofType<x\xhp>()),
    );
  }


  protected async function stringifyAsync(): Awaitable<string> {
    $children = $this->getChildren();
    $html = '<!--[if '.$this->:if.']>';
    $html .= await Vec\map_async(
      $this->getChildren(),
      async $child ==> await x\xhp::renderChildAsync($child),
    )
      |> Str\join($$, '');
    $html .= '<![endif]-->';
    return $html;
  }
}
