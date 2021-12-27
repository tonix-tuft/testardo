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

namespace Test\Components\Consumer;

use Test\Components\Dependency\DependencyInterface;

/**
 * @author Anton Bagdatyev (Tonix) <antonytuft@gmail.com>
 */
class Consumer {
  /**
   * @var DependencyInterface
   */
  protected $dependency;

  /**
   * Constructor.
   *
   * @param DependencyInterface $dependency The dependency of the consumer.
   */
  public function __construct(DependencyInterface $dependency) {
    $this->dependency = $dependency;
  }

  /**
   * A method accepting different PHP data types as parameters delegating to the underlying dependency.
   *
   * @param string $str A string.
   * @param int $i An integer.
   * @param float $double A float/double.
   * @param bool $bool A boolean.
   * @return void
   */
  public function methodReceivingDifferentPHPDataTypesAsParametersDelegatingToUnderlyingDependency(
    string $str = '',
    int $i = 0,
    float $double = 0.0,
    bool $bool = false,
    array $arr = [],
    callable $callable,
    $null = null,
    ...$args
  ) {
    $this->dependency->methodReceivingDifferentPHPDataTypesAsParameters(
      $str,
      $i,
      $double,
      $bool,
      $arr,
      $callable,
      $null,
      ...$args
    );
  }
}
