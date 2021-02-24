/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */
namespace Facebook\XHP\SVG;

use namespace Facebook\XHP\ChildValidation as XHPChild;
use namespace Facebook\XHP\HTML;

xhp class animateMotion extends element implements Cat\AnimationElement {
  use XHPChild\Validation;

  attribute
    enum {'replace', 'sum'} additive,
    enum {'none', 'sum'} accumulate,
    string onbegin,
    // Heads-up, HTML defines onended, but SVG defines onend
    string onend,
    string onrepeat,
    string href,
    string begin,
    // <Clock-value> or "media" or "indefinite"
    string dur,
    string end,
    // <Clock-value> or "media"
    string min,
    // <Clock-value> or "media"
    string max,
    enum {'always', 'whenNotActive', 'never'} restart,
    // <number> (but fractions don't make sense)  or "indefinite"
    arraykey repeatCount,
    // <Clock-value> or "indefinite"
    string repeatDur,
    string fill,
    string values,
    string keyTimes,
    string keySplines,
    string from,
    string to,
    string by,
    string requiredExtensions,
    string systemLanguage,
    string path,
    string keyPoints,
    // float or "auto" or "auto-reverse"
    mixed rotate,
    // The ‘origin’ attribute ... has no effect in SVG.
    enum {'default'} origin;

  /**
   * Spec: Any number of descriptive elements,
   *       ‘script’ and at most one ‘mpath’ element, in any order.
   *
   * Note: We allow any number of mpath elements, not just 1.
   */
  protected static function getChildrenDeclaration(): XHPChild\Constraint {
    return XHPChild\any_number_of(XHPChild\any_of(
      XHPChild\of_type<Cat\DescriptiveElement>(),
      XHPChild\of_type<mpath>(),
      XHPChild\of_type<HTML\script>(),
    ));
  }

  protected string $tagName = 'animateMotion';
}
