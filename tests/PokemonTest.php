<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

spl_autoload_register(function ($className) {
    $newClassName = str_replace("\\", "/", $className) .".php";
    require 'class/' . $newClassName;
  });

final class PokemonTest extends TestCase
{
    public function setUp(): void
    {
        $this->pokemon = new Pokemon();
    }

    /**
     * @dataProvider dataRemoveLegendaryPokemon
     */
    public function testRemoveLegendaryPokemon(array $pokemonList, int $expected): void
    {
        $reflectionClass = new ReflectionClass('Pokemon');
        $method = $reflectionClass->getMethod('removeLegendaryPokemon');
        $method->setAccessible(true);

        $this->pokemon->pokemonList = $pokemonList;
        foreach($pokemonList as $index => $pokemon) {
            $method->invoke($this->pokemon, $index);
        }
        $this->assertSame(count($this->pokemon->pokemonList), $expected);
        foreach($this->pokemon->pokemonList as $pokemon) {
            $this->assertSame($pokemon['legendary'], 'False');    
        }
    }

    public function dataRemoveLegendaryPokemon(): array
    {
        $pokemonList1 = [
            ['legendary' => 'True'],
        ];
        $pokemonList2 = [
            ['legendary' => 'False' ],
        ];
        $pokemonList3 = [
            ['legendary' => 'True'],
            ['legendary' => 'False'],
        ];
        $pokemonList4 = [
            ['legendary' => 'False'],
            ['legendary' => 'False'],
            ['legendary' => 'True'],
        ];
        $pokemonList5 = [
            ['legendary' => 'False'],
            ['legendary' => 'False'],
            ['legendary' => 'True'],
            ['legendary' => 'True'],
            ['legendary' => 'False'],
        ];

        return [
            [$pokemonList1, 0],
            [$pokemonList2, 1],
            [$pokemonList3, 1],
            [$pokemonList4, 2],
            [$pokemonList5, 3],
        ];
    }

    /**
     * @dataProvider dataRemovePokemonByType
     */
    public function testRemovePokemonByType(array $pokemonList, int $expected, string $type): void
    {
        $reflectionClass = new ReflectionClass('Pokemon');
        $method = $reflectionClass->getMethod('removePokemonByType');
        $method->setAccessible(true);

        $this->pokemon->pokemonList = $pokemonList;
        foreach($pokemonList as $index => $pokemon) {
            $method->invoke($this->pokemon, $index, $type);
        }
        $this->assertSame(count($this->pokemon->pokemonList), $expected);
        foreach($this->pokemon->pokemonList as $pokemon) {
            $this->assertNotSame($pokemon['type 1'], $type);    
        }
    }

    public function dataRemovePokemonByType(): array
    {
        $pokemonList = [
            ['type 1' => 'Ghost'],
            ['type 1' => 'Ghost'],
            ['type 1' => 'Ghost'],
            ['type 1' => 'Fire'],
            ['type 1' => 'Fire'],
            ['type 1' => 'Bug'],
        ];

        return [
            '1. Remove Ghost' => [$pokemonList, 3, 'Ghost'],
            '2. Remove Fire' => [$pokemonList, 4, 'Fire'],
            '3. Remove Bug' => [$pokemonList, 5, 'Bug'],
        ];
    }

    /**
     * @dataProvider dataModifyPokemonByType
     */
    public function testModifyPokemonByType(
        array $pokemonList,
        int $expected,
        string $type,
        string $action,
        string $field = '',
        float $factor = 0.0
    ):void
    {
        $reflectionClass = new ReflectionClass('Pokemon');
        $method = $reflectionClass->getMethod('modifyPokemonByType');
        $method->setAccessible(true);

        $this->pokemon->pokemonList = $pokemonList;
        foreach($this->pokemon->pokemonList as $index => $pokemon) {
            $method->invoke($this->pokemon, $index, $type, $action, $field, $factor);
        }

        if($action === 'multiplex') {
            foreach($this->pokemon->pokemonList as $index => $pokemon) {
                if($pokemon['type 1'] === $type || $pokemon['type 2'] === $type) {
                    $this->assertSame($pokemon[$field], $pokemonList[$index][$field] * $factor);
                }    
            }
        }
    }

    public function dataModifyPokemonByType(): array
    {
        $pokemonList = [
            ['type 1' => 'Ghost', 'type 2' => 'Flying', 'hp' => 40, 'speed' => '80', 'attack' => '90'],
            ['type 1' => 'Fire', 'type 2' => 'Ice', 'hp' => 50, 'speed' => '70', 'attack' => '80'],
            ['type 1' => 'Bug', 'type 2' => 'Fire', 'hp' => 60, 'speed' => '60', 'attack' => '70'],
            ['type 1' => 'Water', 'type 2' => 'Electric', 'hp' => 60, 'speed' => '50', 'attack' => '60'],
        ];

        //$pokemonList, $expeced, $type, $action, $field, $factor
        return [
            '1. Increase 10% HP for Ghost' => [$pokemonList, 0, 'Ghost', 'multiplex', 'HP', 1.1],
            '2. Increase 30% Speed for Fire' => [$pokemonList, 0, 'Fire', 'multiplex', 'Speed', 1.3],
            '3. Increase 50% Attack for Bug' => [$pokemonList, 0, 'Bug', 'multiplex', 'Attack', 1.5],

            '4. Decrease 20% HP for Water' => [$pokemonList, 0, 'Water', 'multiplex', 'HP', 0.8],
            '5. Decrease 40% Speed for Electric' => [$pokemonList, 0, 'Electric', 'multiplex', 'Speed', 0.6],
            '6. Decrease 60% Attack for Ice' => [$pokemonList, 0, 'Ice', 'multiplex', 'Attack', 0.4],
        ];
    }
}