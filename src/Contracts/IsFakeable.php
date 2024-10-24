<?php

namespace BradieTilley\Actions\Contracts;

/**
 * This interface provides a `handleFake` method which is designed to produce a
 * suitable value that replicates the return value from the `handle` method and
 * is used in place of running the `handle` method when the Action is fake with
 * execution disabled. The return type of `handleFake` should match `handle`.
 *
 * An example of this is when an action accepts an image in the construct, then
 * returns a resized image in the response of the handle method. When faking in
 * tests you may wish to return the original unresized image resource, allowing
 * your workflow to continue unaffected.
 *
 * Example:
 * ```
 *      public function __construct(public readonly Image $image)
 *      {}
 *
 *      public function handle(Resizer $resizer): Image
 *      {
 *          $image = $resizer->resize($this->image->path);
 *
 *          return Image::createFromPath($image->getPath());
 *      }
 *
 *      public function handleFake(): Image
 *      {
 *          return $this->image;
 *      }
 * ```
 *
 * @method mixed handleFake(...$arguments)
 */
interface IsFakeable
{
}
