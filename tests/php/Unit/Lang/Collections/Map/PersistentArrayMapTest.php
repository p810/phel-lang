<?php

declare(strict_types=1);

namespace PhelTest\Unit\Lang\Collections\Map;

use Phel\Lang\Collections\Map\PersistentArrayMap;
use Phel\Lang\Collections\Map\PersistentHashMap;
use PhelTest\Unit\Lang\Collections\ModuloHasher;
use PhelTest\Unit\Lang\Collections\SimpleEqualizer;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class PersistentArrayMapTest extends TestCase
{
    public function testEmpty(): void
    {
        $h = PersistentArrayMap::empty(new ModuloHasher(), new SimpleEqualizer());

        self::assertEquals(0, $h->count());
        self::assertFalse($h->contains('test'));
        self::assertFalse($h->contains(null));
        self::assertNull($h->find('test'));
    }

    public function testCanNotCreateFromArrayWithUnevenValues(): void
    {
        $this->expectException(RuntimeException::class);
        PersistentArrayMap::fromArray(new ModuloHasher(), new SimpleEqualizer(), ['test']);
    }

    public function testAddNullKey(): void
    {
        $h = PersistentArrayMap::empty(new ModuloHasher(), new SimpleEqualizer());
        $h2 = $h->put(null, 'test');

        self::assertEquals(null, $h->find(null));
        self::assertEquals(0, $h->count());
        self::assertFalse($h->contains(null));
        self::assertEquals('test', $h2->find(null));
        self::assertEquals(1, $h2->count());
        self::assertTrue($h2->contains(null));
    }

    public function testPutKeyValue(): void
    {
        $h = PersistentArrayMap::empty(new ModuloHasher(), new SimpleEqualizer())
            ->put(1, 'test');

        self::assertEquals(1, $h->count());
        self::assertTrue($h->contains(1));
        self::assertEquals('test', $h->find(1));
    }

    public function testPutSameKeyValueTwice(): void
    {
        $h = PersistentArrayMap::empty(new ModuloHasher(), new SimpleEqualizer())
            ->put(1, 'test')
            ->put(1, 'test');

        self::assertEquals(1, $h->count());
        self::assertTrue($h->contains(1));
        self::assertEquals('test', $h->find(1));
    }

    public function testPutSameKeyDifferentValue(): void
    {
        $h = PersistentArrayMap::empty(new ModuloHasher(), new SimpleEqualizer())
            ->put(1, 'test')
            ->put(1, 'foo');

        self::assertEquals(1, $h->count());
        self::assertTrue($h->contains(1));
        self::assertEquals('foo', $h->find(1));
    }

    public function testPutNullTwice(): void
    {
        $h = PersistentArrayMap::empty(new ModuloHasher(), new SimpleEqualizer())
            ->put(null, 'test')
            ->put(null, 'test');

        self::assertEquals(1, $h->count());
        self::assertTrue($h->contains(null));
        self::assertEquals('test', $h->find(null));
    }

    public function testMerge(): void
    {
        $h1 = PersistentArrayMap::empty(new ModuloHasher(), new SimpleEqualizer())
            ->put(1, 'test');

        $h2 = PersistentArrayMap::empty(new ModuloHasher(), new SimpleEqualizer())
            ->put(2, 'bar');

        $expected = PersistentArrayMap::empty(new ModuloHasher(), new SimpleEqualizer())
            ->put(1, 'test')
            ->put(2, 'bar');

        $this->assertEquals($expected, $h1->merge($h2));
    }

    public function testConvertToPersistentHashMap(): void
    {
        $h = PersistentArrayMap::empty(new ModuloHasher(), new SimpleEqualizer());
        for ($i = 0; $i < PersistentArrayMap::MAX_SIZE + 1; $i++) {
            $h = $h->put($i, 'foo');
        }

        $this->assertInstanceOf(PersistentHashMap::class, $h);
    }

    public function testRemoveExistingNullKey(): void
    {
        $h = PersistentArrayMap::empty(new ModuloHasher(), new SimpleEqualizer())
            ->put(null, 'test')
            ->remove(null);

        self::assertEquals(0, $h->count());
        self::assertFalse($h->contains(null));
        self::assertNull($h->find(null));
    }

    public function testRemoveNonExistingNullKey(): void
    {
        $h = PersistentArrayMap::empty(new ModuloHasher(), new SimpleEqualizer())
            ->remove(null);

        self::assertEquals(0, $h->count());
        self::assertFalse($h->contains(null));
        self::assertNull($h->find(null));
    }

    public function testRemoveNonExistingKey(): void
    {
        $h = PersistentArrayMap::empty(new ModuloHasher(), new SimpleEqualizer())
            ->remove(1);

        self::assertEquals(0, $h->count());
        self::assertFalse($h->contains(1));
        self::assertNull($h->find(1));
    }

    public function testRemoveNonExistingKeyInChild(): void
    {
        $h = PersistentArrayMap::empty(new ModuloHasher(), new SimpleEqualizer())
            ->put(2, 'test')
            ->remove(1);

        self::assertEquals(1, $h->count());
        self::assertTrue($h->contains(2));
        self::assertEquals('test', $h->find(2));
        self::assertFalse($h->contains(1));
        self::assertNull($h->find(1));
    }

    public function testRemoveExistingKey(): void
    {
        $h = PersistentArrayMap::empty(new ModuloHasher(), new SimpleEqualizer())
            ->put(1, 'test')
            ->remove(1);

        self::assertEquals(0, $h->count());
        self::assertFalse($h->contains(1));
        self::assertNull($h->find(1));
    }

    public function testEquals(): void
    {
        $h1 = PersistentArrayMap::empty(new ModuloHasher(), new SimpleEqualizer())
            ->put(1, 'foo')
            ->put(2, 'bar');

        $h2 = PersistentArrayMap::empty(new ModuloHasher(), new SimpleEqualizer())
            ->put(2, 'bar')
            ->put(1, 'foo');

        $this->assertTrue($h1->equals($h2));
        $this->assertTrue($h2->equals($h1));
    }

    public function testEqualsDifferentKeys(): void
    {
        $h1 = PersistentArrayMap::empty(new ModuloHasher(), new SimpleEqualizer())
            ->put(1, 'foo')
            ->put(2, 'bar');

        $h2 = PersistentArrayMap::empty(new ModuloHasher(), new SimpleEqualizer())
            ->put(3, 'bar')
            ->put(4, 'foo');

        $this->assertFalse($h1->equals($h2));
        $this->assertFalse($h2->equals($h1));
    }

    public function testEqualsDifferentLength(): void
    {
        $h1 = PersistentArrayMap::empty(new ModuloHasher(), new SimpleEqualizer())
            ->put(1, 'foo')
            ->put(2, 'bar')
            ->put(3, 'foobar');

        $h2 = PersistentArrayMap::empty(new ModuloHasher(), new SimpleEqualizer())
            ->put(2, 'bar')
            ->put(1, 'foo');

        $this->assertFalse($h1->equals($h2));
        $this->assertFalse($h2->equals($h1));
    }

    public function testEqualsDifferentValues(): void
    {
        $h1 = PersistentArrayMap::empty(new ModuloHasher(), new SimpleEqualizer())
            ->put(1, 'foo')
            ->put(2, 'bar');

        $h2 = PersistentArrayMap::empty(new ModuloHasher(), new SimpleEqualizer())
            ->put(1, 'bar')
            ->put(2, 'foo');

        $this->assertFalse($h1->equals($h2));
        $this->assertFalse($h2->equals($h1));
    }

    public function testEqualsDifferentType(): void
    {
        $h1 = PersistentArrayMap::empty(new ModuloHasher(), new SimpleEqualizer())
            ->put(1, 'foo')
            ->put(2, 'bar');

        $this->assertFalse($h1->equals([1 => 'foo', 2 => 'bar']));
    }

    public function testIteratable(): void
    {
        $h = PersistentArrayMap::empty(new ModuloHasher(), new SimpleEqualizer())
            ->put(1, 'foo')
            ->put(2, 'bar')
            ->put(3, 'foobar');

        $result = [];
        foreach ($h as $k => $v) {
            $result[$k] = $v;
        }

        $this->assertEquals([1 => 'foo', 2 => 'bar', 3 => 'foobar'], $result);
    }

    public function testIteratableOnEmpty(): void
    {
        $h = PersistentArrayMap::empty(new ModuloHasher(), new SimpleEqualizer());

        $result = [];
        foreach ($h as $k => $v) {
            $result[$k] = $v;
        }

        $this->assertEquals([], $result);
    }

    public function testHashOnEmptyMap(): void
    {
        $h = PersistentArrayMap::empty(new ModuloHasher(), new SimpleEqualizer());

        $this->assertEquals(1, $h->hash());
    }

    public function testHashOnSingleEntryyMap(): void
    {
        $h = PersistentArrayMap::empty(new ModuloHasher(), new SimpleEqualizer())
            ->put(1, 10);

        $this->assertEquals(1 + (1 ^ 10), $h->hash());
    }

    public function testAddMetaData(): void
    {
        $meta = PersistentArrayMap::empty(new ModuloHasher(), new SimpleEqualizer());
        $h = PersistentArrayMap::empty(new ModuloHasher(), new SimpleEqualizer())
            ->withMeta($meta);

        $this->assertEquals($meta, $h->getMeta());
    }
}
