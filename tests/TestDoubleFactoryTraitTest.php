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

use PHPUnit\Framework\TestCase;
use Test\Components\Dependency\AbstractDependency;
use Test\Components\Dependency\DependencyInterface;
use Test\Components\Dependency\DependencyTrait;
use Test\Components\TestDoubleFactoryTraitConsumerTest;
use Mockery\MockInterface;
use PHPUnit\Framework\Constraint\Callback;
use PHPUnit\Framework\Constraint\IsAnything;
use PHPUnit\Framework\Constraint\IsEqual;
use PHPUnit\Framework\Constraint\IsFalse;
use PHPUnit\Framework\Constraint\IsNull;
use PHPUnit\Framework\Constraint\IsTrue;
use PHPUnit\Framework\MockObject\Builder\InvocationMocker;
use PHPUnit\Framework\MockObject\MockObject;

require_once __DIR__ . '/../tests-deps/TestDoubleFactoryTraitConsumerTest.php';

/**
 * @author Anton Bagdatyev (Tonix) <antonytuft@gmail.com>
 */
class TestDoubleFactoryTraitTest extends TestCase {
  public function tearDown(): void {
    \Mockery::close();
  }

  public function test can mock an interface() {
    $testDoubleTraitConsumer = new TestDoubleFactoryTraitConsumerTest();
    $mock = $testDoubleTraitConsumer->makeMock(DependencyInterface::class);
    $this->assertInstanceOf(DependencyInterface::class, $mock);
  }

  public function test can mock an abstract class() {
    $testDoubleTraitConsumer = new TestDoubleFactoryTraitConsumerTest();
    $mock = $testDoubleTraitConsumer->makeMock(AbstractDependency::class);
    $this->assertInstanceOf(AbstractDependency::class, $mock);
  }

  public function test can mock a trait() {
    $testDoubleTraitConsumer = new TestDoubleFactoryTraitConsumerTest();
    $mock = $testDoubleTraitConsumer->makeMock(DependencyTrait::class);
    $this->assertEquals("string", $mock->traitMethod("string"));
  }

  public function test expecting any number of invocations of a method works and is the default() {
    /* This won't work because PHPUnit's `any` method is static.
    $mock = $this->getMockBuilder(TestDoubleFactoryTraitConsumerTest::class)
      ->setMethods(['any'])
      ->getMock();

    $mock
      ->expects($this->once())
      ->method('any')
    //*/

    /**
     * @var MockInterface $mock
     */
    //*
    $mock = \Mockery::mock(
      TestDoubleFactoryTraitConsumerTest::class
    )->makePartial();

    $mock
      ->shouldReceive('any')
      ->times(1)
      ->andReturn($this->any());
    //*/

    $returnedMock = $mock->makeMock(DependencyInterface::class, [
      'expectations' => [
        [
          'method' => 'methodReceivingDifferentPHPDataTypesAsParameters',
        ],
      ],
    ]);

    /**
     * This assertion is needed only because Mockery's expectations don't count as PHPUnit assertions.
     */
    $this->assertInstanceOf(DependencyInterface::class, $returnedMock);
  }

  protected function numberOfInvocationsTest($params) {
    [
      'numberOfInvocations' => $numberOfInvocations,
      'numberOfInvocationsMethod' => $numberOfInvocationsMethod,
      'numberOfInvocationsMethodArgs' => $numberOfInvocationsMethodArgs,
    ] = $params + [
      'numberOfInvocationsMethod' => null,
      'numberOfInvocationsMethodArgs' => [],
    ];

    /**
     * @var MockInterface $mock
     */
    $mock = \Mockery::mock(
      TestDoubleFactoryTraitConsumerTest::class
    )->makePartial();

    $numberOfInvocationsMethod =
      $numberOfInvocationsMethod ?? $numberOfInvocations;
    $expectation = $mock->shouldReceive($numberOfInvocationsMethod);
    if (!empty($numberOfInvocationsMethodArgs)) {
      $expectation = $expectation->withArgs($numberOfInvocationsMethodArgs);
    }
    $expectation
      ->times(1)
      ->andReturn(
        $this->{$numberOfInvocationsMethod}(...$numberOfInvocationsMethodArgs)
      );

    $returnedMock = $mock->makeMock(DependencyInterface::class, [
      'expectations' => [
        [
          'numberOfInvocations' => $numberOfInvocations,
          'method' => 'methodReceivingDifferentPHPDataTypesAsParameters',
        ],
      ],
    ]);
    $this->assertInstanceOf(DependencyInterface::class, $returnedMock);
  }

  public function test expecting any number of invocations of a method works when any is passed explicitly() {
    $this->numberOfInvocationsTest([
      'numberOfInvocations' => 'any',
    ]);
  }

  public function test expecting a method is never called works() {
    $this->numberOfInvocationsTest([
      'numberOfInvocations' => 'never',
    ]);
  }

  public function test expecting a method is called at least once works() {
    $this->numberOfInvocationsTest([
      'numberOfInvocations' => 'atLeastOnce',
    ]);
  }

  public function test expecting a method is called at least 3 times works() {
    $this->numberOfInvocationsTest([
      'numberOfInvocations' => 'atLeast 3',
      'numberOfInvocationsMethod' => 'atLeast',
      'numberOfInvocationsMethodArgs' => [3],
    ]);
  }

  public function test expecting a method is called once works() {
    $this->numberOfInvocationsTest([
      'numberOfInvocations' => 'once',
    ]);
  }

  public function test expecting a method is called exactly 3 times works() {
    $this->numberOfInvocationsTest([
      'numberOfInvocations' => 3,
      'numberOfInvocationsMethod' => 'exactly',
      'numberOfInvocationsMethodArgs' => [3],
    ]);
  }

  public function test expecting a method is called exactly 3 times but with numberOfInvocations as an integer string works() {
    $this->numberOfInvocationsTest([
      'numberOfInvocations' => '3',
      'numberOfInvocationsMethod' => 'exactly',
      'numberOfInvocationsMethodArgs' => [3],
    ]);
  }

  public function test args constraints using with method works() {
    /**
     * @var MockInterface $mock
     */
    $mock = \Mockery::mock(TestDoubleFactoryTraitConsumerTest::class)
      ->makePartial()
      ->shouldAllowMockingProtectedMethods();

    $dependencyMock = \Mockery::mock(MockObject::class);
    $mock
      ->shouldReceive('createMock')
      ->times(1)
      ->andReturn($dependencyMock);

    $invocationMockerMock = \Mockery::mock(InvocationMocker::class);
    $invocationMockerMock
      ->shouldReceive('method')
      ->times(1)
      ->andReturn($invocationMockerMock);

    $dependencyMock
      ->shouldReceive('expects')
      ->times(1)
      ->andReturn($invocationMockerMock);

    $invocationMockerMock->shouldReceive('with')->times(1);

    $returnedMock = $mock->makeMock(DependencyInterface::class, [
      'expectations' => [
        [
          'method' => 'methodReceivingDifferentPHPDataTypesAsParameters',
          'args' => ['anything'],
        ],
      ],
    ]);
    $this->assertInstanceOf(MockObject::class, $returnedMock);
  }

  public function test consecutive args constraints using withConsecutive method works() {
    /**
     * @var MockInterface $mock
     */
    $mock = \Mockery::mock(TestDoubleFactoryTraitConsumerTest::class)
      ->makePartial()
      ->shouldAllowMockingProtectedMethods();

    $dependencyMock = \Mockery::mock(MockObject::class);
    $mock
      ->shouldReceive('createMock')
      ->times(1)
      ->andReturn($dependencyMock);

    $invocationMockerMock = \Mockery::mock(InvocationMocker::class);
    $invocationMockerMock
      ->shouldReceive('method')
      ->times(1)
      ->andReturn($invocationMockerMock);

    $dependencyMock
      ->shouldReceive('expects')
      ->times(1)
      ->andReturn($invocationMockerMock);

    $invocationMockerMock->shouldReceive('withConsecutive')->times(1);

    $returnedMock = $mock->makeMock(DependencyInterface::class, [
      'expectations' => [
        [
          'method' => 'methodReceivingDifferentPHPDataTypesAsParameters',
          'consecutiveArgs' => [['anything']],
        ],
      ],
    ]);
    $this->assertInstanceOf(MockObject::class, $returnedMock);
  }

  public function test args constraints are parsed() {
    /**
     * @var MockInterface $mock
     */
    $mock = \Mockery::mock(TestDoubleFactoryTraitConsumerTest::class)
      ->makePartial()
      ->shouldAllowMockingProtectedMethods();

    $dependencyMock = \Mockery::mock(MockObject::class);
    $mock
      ->shouldReceive('createMock')
      ->times(1)
      ->andReturn($dependencyMock);

    $invocationMockerMock = \Mockery::mock(InvocationMocker::class);
    $invocationMockerMock
      ->shouldReceive('method')
      ->times(1)
      ->andReturn($invocationMockerMock);

    $dependencyMock
      ->shouldReceive('expects')
      ->times(1)
      ->andReturn($invocationMockerMock);

    $invocationMockerMock->shouldReceive('with')->times(1);

    $mock
      ->shouldReceive('parseArgsConstraints')
      ->times(1)
      ->andReturn([$this->anything()]);

    $returnedMock = $mock->makeMock(DependencyInterface::class, [
      'expectations' => [
        [
          'method' => 'methodReceivingDifferentPHPDataTypesAsParameters',
          'args' => ['anything'],
        ],
      ],
    ]);
    $this->assertInstanceOf(MockObject::class, $returnedMock);
  }

  public function test consecutive args constraints are parsed() {
    /**
     * @var MockInterface $mock
     */
    $mock = \Mockery::mock(TestDoubleFactoryTraitConsumerTest::class)
      ->makePartial()
      ->shouldAllowMockingProtectedMethods();

    $dependencyMock = \Mockery::mock(MockObject::class);
    $mock
      ->shouldReceive('createMock')
      ->times(1)
      ->andReturn($dependencyMock);

    $invocationMockerMock = \Mockery::mock(InvocationMocker::class);
    $invocationMockerMock
      ->shouldReceive('method')
      ->times(1)
      ->andReturn($invocationMockerMock);

    $dependencyMock
      ->shouldReceive('expects')
      ->times(1)
      ->andReturn($invocationMockerMock);

    $invocationMockerMock->shouldReceive('withConsecutive')->times(1);

    $mock
      ->shouldReceive('parseArgsConstraints')
      ->times(2)
      ->andReturn([$this->anything()]);

    $returnedMock = $mock->makeMock(DependencyInterface::class, [
      'expectations' => [
        [
          'method' => 'methodReceivingDifferentPHPDataTypesAsParameters',
          'consecutiveArgs' => [['anything'], ['anything']],
        ],
      ],
    ]);
    $this->assertInstanceOf(MockObject::class, $returnedMock);
  }

  public function test args constraints are parsed correctly() {
    /**
     * @var MockInterface $mock
     */
    $mock = \Mockery::mock(TestDoubleFactoryTraitConsumerTest::class)
      ->makePartial()
      ->shouldAllowMockingProtectedMethods();

    $dependencyMock = \Mockery::mock(MockObject::class);
    $mock
      ->shouldReceive('createMock')
      ->times(1)
      ->andReturn($dependencyMock);

    $invocationMockerMock = \Mockery::mock(InvocationMocker::class);
    $invocationMockerMock
      ->shouldReceive('method')
      ->times(1)
      ->andReturn($invocationMockerMock);

    $dependencyMock
      ->shouldReceive('expects')
      ->times(1)
      ->andReturn($invocationMockerMock);

    $invocationMockerMock
      ->shouldReceive('with')
      ->times(1)
      ->withArgs(function (
        $maybeIsEqualStringConstraint,
        $maybeIsEqualIntConstraint,
        $maybeIsEqualDoubleConstraint,
        $maybeIsTrueConstraint,
        $maybeIsArrayCallbackConstraint,
        $maybeIsCallableCallbackConstraint,
        $maybeIsNullConstraint,
        $maybeIsFalseConstraint,
        $maybeIsAnythingConstriant,
        $maybeCallbackConstraint
      ) {
        $constraint = $maybeIsEqualStringConstraint;
        if ($constraint instanceof IsEqual) {
          $reflection = new \ReflectionObject($constraint);
          $reflectionProperty = $reflection->getProperty('value');
          $reflectionProperty->setAccessible(true);
          $value = $reflectionProperty->getValue($constraint);
          if ($value !== 'string') {
            return false;
          }
        } else {
          return false;
        }

        $constraint = $maybeIsEqualIntConstraint;
        if ($constraint instanceof IsEqual) {
          $reflection = new \ReflectionObject($constraint);
          $reflectionProperty = $reflection->getProperty('value');
          $reflectionProperty->setAccessible(true);
          $value = $reflectionProperty->getValue($constraint);
          if ($value !== 5) {
            return false;
          }
        } else {
          return false;
        }

        $constraint = $maybeIsEqualDoubleConstraint;
        if ($constraint instanceof IsEqual) {
          $reflection = new \ReflectionObject($constraint);
          $reflectionProperty = $reflection->getProperty('value');
          $reflectionProperty->setAccessible(true);
          $value = $reflectionProperty->getValue($constraint);
          if ($value !== 1.7) {
            return false;
          }
        } else {
          return false;
        }

        $constraint = $maybeIsTrueConstraint;
        if (!($constraint instanceof IsTrue)) {
          return false;
        }

        $constraint = $maybeIsArrayCallbackConstraint;
        if ($constraint instanceof Callback) {
          $reflection = new \ReflectionObject($constraint);
          $reflectionMethod = $reflection->getMethod('matches');
          $reflectionMethod->setAccessible(true);
          $matches = $reflectionMethod->invoke($constraint, []);
          if (!$matches) {
            return false;
          }
        } else {
          return false;
        }

        $constraint = $maybeIsCallableCallbackConstraint;
        if ($constraint instanceof Callback) {
          $reflection = new \ReflectionObject($constraint);
          $reflectionMethod = $reflection->getMethod('matches');
          $reflectionMethod->setAccessible(true);
          $matches = $reflectionMethod->invoke($constraint, function () {});
          if (!$matches) {
            return false;
          }
        } else {
          return false;
        }

        $constraint = $maybeIsNullConstraint;
        if (!($constraint instanceof IsNull)) {
          return false;
        }

        $constraint = $maybeIsFalseConstraint;
        if (!($constraint instanceof IsFalse)) {
          return false;
        }

        $constraint = $maybeIsAnythingConstriant;
        if (!($constraint instanceof IsAnything)) {
          return false;
        }

        $constraint = $maybeCallbackConstraint;
        if (!($constraint instanceof Callback)) {
          return false;
        }

        return true;
      });

    $returnedMock = $mock->makeMock(DependencyInterface::class, [
      'expectations' => [
        [
          'method' => 'methodReceivingDifferentPHPDataTypesAsParameters',
          'args' => [
            ['equalTo', 'string'],
            ['equalTo', 5],
            ['equalTo', 1.7],
            'isTrue',
            'isArray',
            'isCallable',
            'isNull',
            'isFalse',
            'anything',
            function () {
              return true;
            },
          ],
        ],
      ],
    ]);
    $this->assertInstanceOf(MockObject::class, $returnedMock);
  }
}
