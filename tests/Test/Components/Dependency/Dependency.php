<?php

/*
 * Copyright (c) 2021 Anton Bagdatyev (Tonix)
 *
 * Permission is hereby granted, free of charge, to any person
 * obtaining a copy of this software and associated documentation
 * files (the "Software"), to deal in the Software without
 * restriction, including without limitation the rights to use,
 * copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the
 * Software is furnished to do so, subject to the following
 * conditions:
 *
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
 * OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
 * HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
 * WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
 * OTHER DEALINGS IN THE SOFTWARE.
 */

namespace Test\Components\Dependency;

use Test\Components\Dependency\AbstractDependency;
use Test\Components\Dependency\DependencyInterface;
use Test\Components\Dependency\DependencyTrait;

/**
 * @author Anton Bagdatyev (Tonix) <antonytuft@gmail.com>
 */
class Dependency extends AbstractDependency implements DependencyInterface {
  use DependencyTrait;

  /**
   * {@inheritdoc}
   */
  public function methodReceivingDifferentPHPDataTypesAsParameters(
    string $str = '',
    int $i = 0,
    float $double = 0.0,
    bool $bool = false,
    array $arr = [],
    callable $callable = null,
    $null = null,
    ...$args
  ) {
    $str = strtolower($str);
    $i = $i + 1;
    $double = $double + 1.0;
    $bool = !$bool;
    $arr = array_map(function ($element) {
      return $element;
    }, $arr);
    if (is_callable($callable)) {
      $callable();
    }
    foreach ($args as $arg) {
      gettype($arg);
    }
    $null = !$null;
  }
}
