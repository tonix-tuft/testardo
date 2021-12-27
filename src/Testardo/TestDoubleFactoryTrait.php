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

namespace Testardo;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\Constraint\Constraint;
use PHPUnit\Framework\MockObject\Builder\InvocationMocker;

/**
 * A Testardo testing utility trait which is a factory for test doubles.
 *
 * @author Anton Bagdatyev (Tonix) <antonytuft@gmail.com>
 */
trait TestDoubleFactoryTrait {
  /**
   * Makes a new mock.
   *
   * @var Constraint $options['expectations]['method']
   *
   * @see https://phpunit.readthedocs.io/en/8.5/assertions.html#appendixes-assertions-assertthat-tables-constraints
   * @see https://phpunit.readthedocs.io/en/9.5/assertions.html#appendixes-assertions-assertthat-tables-constraints
   *
   * @param string $whatToMock The fully qualified name of the interface, class, abstract class or trait to mock (e.g. `AClass::class`).
   * @param array $options An array of options:
   *
   *                           - 'expectations' (array): An array of expectations, each expectation being an associative array with the following shape:
   *
   *                                                         - 'method' (Constraint|string): The method constraint;
   *
   *                                                         - 'numberOfInvocations' (string|int): The number of invocations of the expectation, either one of the following:
   *
   *                                                               - 'any' (string) -> 0 or more times (default);
   *
   *                                                               - 'never' (string) or 0 (int) -> Never;
   *
   *                                                               - 'atLeastOnce' (string) -> At least once;
   *
   *                                                               - 'atLeast 1' (string), 'atLeast 2' (string), 'atLeast 3' (string), etc... -> At least that number of invocations
   *                                                                                                                                             (e.g. if `$numberOfInvocations` is 'atLeast 7', then exactly 7 times);
   *
   *                                                               - 'once' (string) or 1 (int) -> Exactly once;
   *
   *                                                               - (int) -> Exactly `$numberOfInvocations` times (e.g. if `$numberOfInvocations` is 2, then exactly 2 times);
   *
   *                                                         - 'args' (array): An array of arguments constraints to pass to the {@link InvocationMocker::with()} method,
   *                                                                           each element of the array being either one of the following:
   *
   *                                                                               - (Constraint): A constraint ({@link Constraint});
   *
   *                                                                               - (string): A string representing the constraint method of PHPUnit's {@link TestCase} returning the constraint which does not accept any parameters,
   *                                                                                           e.g.: 'anything', 'isFalse', 'isNull', 'isTrue', etc... as well as the custom strings 'isCallable' (asserts the argument is a callable) or 'isArray' (asserts the argument is an array);
   *
   *                                                                               - (array): An array where the first element is a string with the constraint method of PHPUnit's {@link TestCase} returning the constraint
   *                                                                                          and its subsequent elements represent the positional arguments for the constraint method,
   *                                                                                          e.g.: `['greaterThan', 0]` (which translates and is the same as `$this->greaterThan(0)`), `['stringContains', 'something']` (which translates and is the same as `$this->stringContains('something')`),
   *                                                                                          `['equalTo', 'foo']` (which translates and is the same as `$this->equalTo('foo')`), `['equalTo', 'bar']` (which translates and is the same as `$this->equalTo('bar')`), etc...;
   *
   *                                                                               - (callable): A callable to pass to the {@link TestCase::callback()} constraint method;
   *
   *                                                                           NOTE: Either 'args' or 'consecutiveArgs' or neither of the two should be passed, but not both ('consecutiveArgs' will always take precedence over 'args' if passed).
   *
   *                                                         - 'consecutiveArgs' (array): An array of consecutive arguments constraints to pass to the {@link InvocationMocker::withConsecutive()} method,
   *                                                                                      each element of the array being an array of the same shape as the shape of the 'args' option above.
   *
   *                                                                                      NOTE: Either 'args' or 'consecutiveArgs' or neither of the two should be passed, but not both ('consecutiveArgs' will always take precedence over 'args' if passed).
   *
   * @return MockObject The mock.
   */
  public function makeMock($whatToMock, $options = []) {
    /**
     * @var TestCase $this
     */

    /**
     * @var MockObject $mock
     */

    [
      'expectations' => $expectations,
    ] = $options + [
      'expectations' => [],
    ];

    $reflectionClass = new \ReflectionClass($whatToMock);
    $isInterface = $reflectionClass->isInterface();
    $isAbstractClass = $reflectionClass->isAbstract();
    $isTrait = $reflectionClass->isTrait();
    $mockMethod = 'createMock';
    if (!$isInterface && $isAbstractClass) {
      $mockMethod = 'getMockForAbstractClass';
    } elseif ($isTrait) {
      $mockMethod = 'getMockForTrait';
    }

    $mock = $this->{$mockMethod}($whatToMock);
    foreach ($expectations as $expectation) {
      /**
       * `numberOfInvocations`:
       *
       *     - 'any' (string) -> 0 or more times (default)
       *     - 'never' (string) or 0 (int) -> Never
       *     - 'atLeastOnce' (string) -> At least once
       *     - 'atLeast 1' (string), 'atLeast 2' (string), 'atLeast 3' (string), etc... -> At least that number of invocations
       *                                                                                   (e.g. if `$numberOfInvocations` is 'atLeast 7', then exactly 7 times)
       *     - 'once' (string) or 1 (int) -> Exactly once
       *     - (int) -> Exactly `$numberOfInvocations` times (e.g. if `$numberOfInvocations` is 2, then exactly 2 times)
       */
      [
        'method' => $method,
        'numberOfInvocations' => $numberOfInvocations,
        'args' => $args, // NOTE: Either 'args' or 'consecutiveArgs' or neither of the two should be passed, but not both ('consecutiveArgs' will always take precedence over 'args' if passed).
        'consecutiveArgs' => $consecutiveArgs, // NOTE: Either 'args' or 'consecutiveArgs' or neither of the two should be passed, but not both ('consecutiveArgs' will always take precedence over 'args' if passed).
      ] = $expectation + [
        'numberOfInvocations' => 'any',
        'args' => null, // NOTE: Either 'args' or 'consecutiveArgs' or neither of the two should be passed, but not both ('consecutiveArgs' will always take precedence over 'args' if passed).
        'consecutiveArgs' => null, // NOTE: Either 'args' or 'consecutiveArgs' or neither of the two should be passed, but not both ('consecutiveArgs' will always take precedence over 'args' if passed).
      ];

      $numberOfInvocationsExpectation = $this->any();
      if (
        $numberOfInvocations === 'never' ||
        ($numberOfInvocations === 0 || $numberOfInvocations === '0')
      ) {
        $numberOfInvocationsExpectation = $this->never();
      } elseif ($numberOfInvocations === 'atLeastOnce') {
        $numberOfInvocationsExpectation = $this->atLeastOnce();
      } elseif (
        preg_match(
          '/atLeast\s+(?P<numberOfTimes>[0-9]+)/',
          $numberOfInvocations,
          $atLeastMatches
        )
      ) {
        $numberOfTimes = (int) $atLeastMatches['numberOfTimes'];
        $numberOfInvocationsExpectation = $this->atLeast($numberOfTimes);
      } elseif ($numberOfInvocations === 'once') {
        $numberOfInvocationsExpectation = $this->once();
      } elseif (
        is_int($numberOfInvocations) ||
        ctype_digit($numberOfInvocations)
      ) {
        $numberOfInvocationsToInt = (int) $numberOfInvocations;
        $numberOfInvocationsExpectation = $this->exactly(
          $numberOfInvocationsToInt
        );
      }

      $withMethod = 'with';
      $argsConstraints = null;
      if ($consecutiveArgs !== null) {
        $withMethod = 'withConsecutive';
        $argsConstraints = array_map(function ($args) {
          return $this->parseArgsConstraints($args);
        }, $consecutiveArgs);
      } elseif ($args !== null) {
        $argsConstraints = $this->parseArgsConstraints($args);
      }

      /**
       * @var InvocationMocker
       */
      $invocationMocker = $mock
        ->expects($numberOfInvocationsExpectation)
        ->method($method);

      if ($argsConstraints !== null) {
        $invocationMocker->{$withMethod}(...$argsConstraints);
      }
    }

    return $mock;
  }

  /**
   * Parses arguments constraints.
   *
   * @see https://phpunit.readthedocs.io/en/8.5/assertions.html#appendixes-assertions-assertthat-tables-constraints
   * @see https://phpunit.readthedocs.io/en/9.5/assertions.html#appendixes-assertions-assertthat-tables-constraints
   *
   * @param array An array of arguments constraints, like those passed to the `$options['expectations']['args']` or `$options['expectations']['consecutiveArgs']` options
   *              of the {@link TestDoubleFactoryTrait::makeMock()} method.
   * @return Constraint[] The parsed arguments constraints.
   */
  protected function parseArgsConstraints($args) {
    /**
     * @var TestCase $this
     */

    $argsConstraints = [];
    foreach ($args as $arg) {
      /**
       * @var Constraint|string|array|callable $argConstraint
       */

      /**
       *
       * Constraint.
       *
       * @see https://phpunit.readthedocs.io/en/8.5/assertions.html#appendixes-assertions-assertthat-tables-constraints
       * @see https://phpunit.readthedocs.io/en/9.5/assertions.html#appendixes-assertions-assertthat-tables-constraints
       */
      $argConstraint = $arg;
      if (is_string($arg)) {
        /**
         * String, e.g.: 'anything', 'isFalse', 'isNull', 'isTrue', etc... + custom 'isCallable' and 'isArray'
         *
         * @see https://phpunit.readthedocs.io/en/8.5/assertions.html#appendixes-assertions-assertthat-tables-constraints
         * @see https://phpunit.readthedocs.io/en/9.5/assertions.html#appendixes-assertions-assertthat-tables-constraints
         */
        if ($arg === 'isCallable') {
          $argConstraint = $this->callback(function ($maybeCallable) {
            return is_callable($maybeCallable);
          });
        } elseif ($arg === 'isArray') {
          $argConstraint = $this->callback(function ($maybeArray) {
            return is_array($maybeArray);
          });
        } else {
          $constraintMethod = $arg;
          $argConstraint = $this->{$constraintMethod}();
        }
      } elseif (is_array($arg)) {
        /**
         * Array, e.g.: `['greaterThan', 0]`, `['stringContains', 'something']`, `['equalTo', 'foo']`, `['equalTo', 'bar']`, etc...
         *
         * @see https://phpunit.readthedocs.io/en/8.5/assertions.html#appendixes-assertions-assertthat-tables-constraints
         * @see https://phpunit.readthedocs.io/en/9.5/assertions.html#appendixes-assertions-assertthat-tables-constraints
         */
        $constraintMethod = $arg[0];
        unset($arg[0]);
        $constraintArgs = $arg;
        $argConstraint = $this->{$constraintMethod}(...$constraintArgs);
      } elseif (is_callable($arg)) {
        /**
         * Callback.
         *
         * @see https://phpunit.readthedocs.io/en/8.5/assertions.html#appendixes-assertions-assertthat-tables-constraints
         * @see https://phpunit.readthedocs.io/en/9.5/assertions.html#appendixes-assertions-assertthat-tables-constraints
         */
        $argConstraint = $this->callback($arg);
      }
      $argsConstraints[] = $argConstraint;
    }
    return $argsConstraints;
  }
}
