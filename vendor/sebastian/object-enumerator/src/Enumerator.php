<?php declare(strict_types=1);
/*
 * This file is part of sebastian/object-enumerator.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace SebastianBergmann\ObjectEnumerator;

use function array_merge;
use function assert;
use function func_get_args;
use function is_array;
use function is_object;
use SebastianBergmann\ObjectReflector\ObjectReflector;
use SebastianBergmann\RecursionContext\Context;

final class Enumerator
{
    /**
     * @psalm-return list<object>
     */
    public function enumerate(array|object $variable): array
    {
        if (isset(func_get_args()[1])) {
            if (!func_get_args()[1] instanceof Context) {
                throw new InvalidArgumentException;
            }

            $processed = func_get_args()[1];
        } else {
            $processed = new Context;
        }

        assert($processed instanceof Context);

        $objects = [];

        if ($processed->contains($variable)) {
            return $objects;
        }

        $array = $variable;

        /* @noinspection UnusedFunctionResultInspection */
        $processed->add($variable);

        if (is_array($variable)) {
            foreach ($array as $element) {
                if (!is_array($element) && !is_object($element)) {
                    continue;
                }

                /** @noinspection SlowArrayOperationsInLoopInspection */
                $objects = array_merge(
                    $objects,
                    $this->enumerate($element, $processed)
                );
            }
        } else {
            $objects[] = $variable;

            foreach ((new ObjectReflector)->getProperties($variable) as $value) {
                if (!is_array($value) && !is_object($value)) {
                    continue;
                }

                /** @noinspection SlowArrayOperationsInLoopInspection */
                $objects = array_merge(
                    $objects,
                    $this->enumerate($value, $processed)
                );
            }
        }

        return $objects;
    }
}
