<?php

use PHPUnit\Framework\TestCase;
use BeAmado\OjsMigrator\Registry;

class ArrayHandlerTest extends TestCase
{
    public function __construct()
    {
        parent::__construct();
        $this->ah = Registry::get('ArrayHandler');
    }
    
    public function testUnionBetween2Arrays()
    {
        $arr1 = array(1, 2, 3);
        $arr2 = array(7, 8, 9);

        $expected = array(1, 2, 3, 7, 8, 9);
        $result = $this->ah->union($arr1, $arr2);

        $this->assertEquals(
            $expected,
            $result
        );
    }

    public function testUnionBetween2ArraysWithRepeatedValues()
    {
        $arr1 = array(1, 1, 2, 3, 3, 2, 5);
        $arr2 = array(1, 2, 9, 78, 4, 5, 6, 3);

        $expected = array(1, 2, 3, 4, 5, 6, 78, 9);
        $result = $this->ah->union($arr1, $arr2);

        sort($expected);
        sort($result);

        $this->assertEquals(
            $expected,
            $result
        );
    }

    public function testUnionBetweenMoreThan2Arrays()
    {
        $arrays = array(
            array(1, 2, 3, 4),
            5, 
            array(9, 78, 23, 5, 1, 2, 7),
            'Vakatawa'
        );

        $expected = array(1, 2, 3, 4, 5, 9, 78, 23, 7, 'Vakatawa');
        sort($expected);

        $result = Registry::get('ArrayHandler')->unionN($arrays);
        sort($result);

        $this->assertEquals($expected, $result);
    }

    public function testCheckIfAnElementIsTheLastOfAnArray()
    {
        $arr = [1, 'goat', 'maori'];

        $ah = Registry::get('ArrayHandler');

        $this->assertTrue(
            $ah->isLast('maori', $arr) && !$ah->isLast('goat', $arr) &&
            !$ah->isLast(1, $arr) && !$ah->isLast('will', $arr)
        );
    }

    public function testAVectorAndAMatrixAreNotEquivalent()
    {
        $vec = array(1, 2, 3);
        $mat = array(
            array(1, 2, 3),
            array(1, 2, 3),
            array(1, 2, 3),
        );

        $this->assertFalse($this->ah->areEquivalent($vec, $mat));
    }

    public function testPermutationsOfTheIdentityMatrixAreEquivalent()
    {
        $id = array(
            array(1, 0, 0),
            array(0, 1, 0),
            array(0, 0, 1),
        );

        $perm1 = array(
            array(0, 1, 0),
            array(1, 0, 0),
            array(0, 0, 1),
        );

        $perm2 = array(
            array(0, 1, 0),
            array(0, 0, 1),
            array(1, 0, 0),
        );

        $perm3 = array(
            array(1, 0, 0),
            array(0, 0, 1),
            array(0, 1, 0),
        );

        $perm4 = array(
            array(0, 0, 1),
            array(1, 0, 0),
            array(0, 1, 0),
        );

        $anti = array(
            array(0, 0, 1),
            array(0, 1, 0),
            array(1, 0, 0),
        );

        $equivalent = true;

        $permutations = array(
            $id,
            $perm1,
            $perm2,
            $perm3,
            $perm4,
            $anti,
        );

        for ($i = 0; $i < \count($permutations); $i++) {
            for ($j = $i; $j < \count($permutations); $j++) {
                $equivalent = $equivalent && $this->ah->areEquivalent(
                    $permutations[$i],
                    $permutations[$j]
                );

                if (!$equivalent)
                    break;
            }

            if (!$equivalent)
                break;
        }

        $this->assertTrue($equivalent);
    }

    public function testAssociativeArrayThatHaveTheSameValuesAndTheSameKeyAreEqual()
    {
        $arr1 = array(
            'prenom' => 'James',
            'nom' => 'Bond',
            'metier' => 'tueur',
        );

        $arr2 = array(
            'nom' => 'Bond',
            'metier' => 'tueur',
            'prenom' => 'James',
        );

        $this->assertTrue($this->ah->equals($arr1, $arr2));
    }

    public function testMatrixesWithEqualAssociativeArraysAreEquivalent()
    {
        $arr1 = array(
            'prenom' => 'James',
            'nom' => 'Bond',
            'metier' => 'tueur',
        );

        $arr2 = array(
            'prenom' => 'Alec',
            'nom' => 'Trevelyan',
            'metier' => 'tueur',
        );

        $arr3 = array(
            'prenom' => 'Vesper',
            'nom' => 'Lynd',
            'metier' => 'comptable',
        );

        $mat1 = array($arr1, $arr2, $arr3);
        $mat2 = array($arr1, $arr3, $arr2);
        $mat3 = array($arr2, $arr3, $arr1);
        $mat4 = array($arr2, $arr1, $arr3);
        $mat5 = array($arr3, $arr1, $arr2);
        $mat6 = array($arr3, $arr2, $arr1);

        $matrixes = array($mat1, $mat2, $mat3, $mat4, $mat5, $mat6);

        $eq = true;

        for ($i = 0; $i < count($matrixes); $i++) {
            for ($j = $i; $j < count($matrixes); $j++) {
                $eq = $eq && $this->ah->areEquivalent(
                    $matrixes[$i],
                    $matrixes[$j]
                );

                if (!$eq)
                    break;
            }
            if (!$eq)
                break;
        }

        $this->assertTrue($eq);
    }
    
    public function testMatrixesWithDifferentAssociativeArraysAreNotEquivalent()
    {
        $arr1 = array(
            'prenom' => 'James',
            'nom' => 'Bond',
            'metier' => 'tueur',
        );

        $arr2 = array(
            'prenom' => 'Alec',
            'nom' => 'Trevelyan',
            'metier' => 'tueur',
        );

        $arr3 = array(
            'prenom' => 'Vesper',
            'nom' => 'Lynd',
            'metier' => 'comptable',
        );

        $mat12 = array(
            $arr1,
            $arr2,
        );

        $mat21 = array(
            $arr2,
            $arr1,
        );

        $mat13 = array(
            $arr1,
            $arr3,
        );

        $mat31 = array(
            $arr3,
            $arr1,
        );

        $mat23 = array(
            $arr2,
            $arr3,
        );

        $mat32 = array(
            $arr3,
            $arr2,
        );

        $this->assertSame(
            '1-0-0-0-0-1-0-0-0-1',
            implode('-', array(
                (int) $this->ah->areEquivalent($mat12, $mat21),
                (int) $this->ah->areEquivalent($mat12, $mat13),
                (int) $this->ah->areEquivalent($mat12, $mat31),
                (int) $this->ah->areEquivalent($mat12, $mat23),
                (int) $this->ah->areEquivalent($mat12, $mat32),
                (int) $this->ah->areEquivalent($mat13, $mat31),
                (int) $this->ah->areEquivalent($mat13, $mat23),
                (int) $this->ah->areEquivalent($mat13, $mat32),
                (int) $this->ah->areEquivalent($mat13, $mat21),
                (int) $this->ah->areEquivalent($mat23, $mat32),
            ))
        );
    }
}
