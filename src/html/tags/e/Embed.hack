/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

namespace Facebook\XHP\HTML;

use namespace Facebook\XHP\ChildValidation as XHPChild;

final xhp class embed
  extends element
  implements
    Cat\PhraseElement,
    Cat\FlowElement,
    Cat\InteractiveElement,
    Cat\EmbeddedElement {
  use XHPChild\Validation;
  /*
   * The HTML spec permits all non-namespaced attributes
   * on the embed element.
   * It is safe to add these attributes
   * to this class if you need them.
   * Make a PR against this repository.
   * Adding all attributes that are use 'in the wild'
   * seems like the best approach of this.
   */
  attribute
    int height,
    string src,
    string type,
    int width,
    /**
     * The following attributes are Flash specific.
     * Most notable use: youtube video embedding
     */
    bool allowfullscreen,
    enum {'always', 'never'} allowscriptaccess,
    string wmode;

  protected static function getChildrenDeclaration(): XHPChild\Constraint {
    return XHPChild\any_number_of(
      XHPChild\any_of(XHPChild\pcdata(), XHPChild\category('%phrase')),
    );
  }

  protected string $tagName = 'embed';
}
