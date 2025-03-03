<?php

declare(strict_types=1);

namespace Utils\Collections;

use PHPAether\Utils\Collections\ArrayList;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ArrayListTest extends TestCase
{

    protected ArrayList $list;

    protected function setUp(): void
    {
        parent::setUp();

        $this->list = new ArrayList([1, 2, 3, 4]);
    }

    #[Test]
    public function it_gets_size_of_list()
    {
        $this->assertEquals(4, $this->list->size());
    }

    #[Test]
    public function it_maps_list()
    {
        $this->assertEquals(
            [0, 2, 6, 12],
            $this->list->map(fn($value, $index) => $value * $index)->toArray()
        );
    }

    #[Test]
    public function it_reduces_list()
    {
        $this->assertEquals(
            20,
            $this->list->reduce(fn($carry, $value, $key) => $carry + ($value * $key), 0)
        );
    }

    public static function it_creates_copy_test_cases(): array
    {
        return [
            // [$offset, $length, $expected]
            [1, 2, [2, 3]],
            ['expected' => [1, 2, 3, 4]],
            [-1, 'expected' => [4, 3, 2, 1]]
        ];
    }

    #[Test]
    #[DataProvider('it_creates_copy_test_cases')]
    public function it_creates_copy(
        int $offset=0,
        ?int $length=null,
        array $expected=[]
    )
    {
        $actual = $this->list->copy($offset, $length)->toArray();

        print_r($expected);
        print_r($actual);

        $this->assertEquals($expected, $actual);
    }

    #[Test]
    public function it_checks_items_for_condition()
    {
        $this->assertTrue(
            $this->list->every(fn($value) => is_int($value))
        );

        $this->assertNotTrue(
            $this->list->some(fn($v, $i) => $v * $i > 20)
        );

        $this->assertFalse(
            $this->list->includes('1')
        );
    }

    #[Test]
    public function it_filters_list()
    {
        $this->assertSame(
            [1, 3],
            $this->list->filter(fn($v) => $v % 2)->toArray()
        );
    }

    #[Test]
    public function it_gets_value_at_index()
    {
        $this->assertEquals(2, $this->list->get(1));

        // get at the index
        $this->assertNull($this->list->get(10));

        // get at index which is out of range
        $this->expectException(\OutOfRangeException::class);
        $this->list->get(5, function ($list, $index) {
            throw new \OutOfRangeException("List does not contain index: $index");
        });

        // get at negative index
        $this->assertEquals(-2, $this->list->get(-3));
    }

    #[Test]
    public function it_adds_item_to_list()
    {

        // append
        $this->assertEquals(
            [1, 2, 3, 4, 6, 0],
            $this->list->append(6, 0)->toArray()
        );

        // prepend
        $this->assertEquals(
            [7, -3, 1, 2, 3, 4, 6, 0],
            $this->list->prepend(7, -3)->toArray()
        );

        // set
        $this->assertEquals(
            [7, -3, 0, 2, 3, 4, 6, 0],
            $this->list->set(2, 0)->toArray()
        );

        // insert
        $actual = $this->list->insert(5, 8)->toArray();
        print_r($actual);
        $this->assertEquals(
            [7, -3, 0, 2, 3, 8, 4, 6, 0],
            $actual
        );
    }

    #[Test]
    public function it_removes_item_from_list()
    {
        // shift
        $this->assertEquals(1, $this->list->shift());
        $this->assertEquals([2, 3, 4], $this->list->toArray());

        // pop
        $this->assertEquals(4, $this->list->pop());
        $this->assertEquals([2, 3], $this->list->toArray());

        // remove
        $this->assertEquals(3, $this->list->remove(-1));
        $this->assertEquals([2], $this->list->toArray());


        $this->expectException(\OutOfRangeException::class);
        $this->list->remove(4);
    }

    #[Test]
    public function it_gets_slice_from_list()
    {
        $this->assertEquals(
            [1, 2],
            $this->list->slice(end: 2)->toArray()
        );

        $this->assertEquals(
            [1, 2, 3, 4],
            $this->list->slice()->toArray()
        );

        $this->assertEquals(
            [3, 4],
            $this->list->slice(2)->toArray()
        );
    }

    #[Test]
    public function it_gets_splice_from_list()
    {
        $this->assertEquals(
            [2, 3, 4],
            $this->list->splice(1, replacement: [7, 8])->toArray()
        );

        $this->assertEquals(
            [1, 7, 8],
            $this->list->toArray()
        );
    }

}

