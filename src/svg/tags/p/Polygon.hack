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

xhp class polygon
  extends element
  implements Cat\GraphicsElement, Cat\RenderableElement, Cat\ShapeElement {

  attribute
    string requiredExtensions,
    string systemLanguage,
    float pathLength,
    string points;

  protected string $tagName = 'polygon';
}
