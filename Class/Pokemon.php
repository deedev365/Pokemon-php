<?php

declare(strict_types=1);

class Pokemon
{
    private $csvFile = 'Data/pokemon.csv';
    public array $pokemonList;

    private const MULTIPLEX = 'multiplex';
    private const DOUBLE = 2.0;
    private const MUNITE_TEN_PERCENT = 0.9;
    private const PLUS_TEN_PERCENT = 1.1;

    public function __construct()
    {
        $this->parsePokemons(); 
        $this->filterPokemons();   
    }

    public function parsePokemons(): void
    {
        $res = fopen($this->csvFile, "r");
        $firstLine = true;
        while (($pokemon = fgets($res)) !== false) {
            if($firstLine === false) {
                $pokemons[] = str_getcsv($pokemon);
            } else {
                $fields = str_getcsv($pokemon);
                $firstLine = false;
            }
        }
        fclose($res);

        $this->makePokemonList($pokemons, $fields);
    }

    /**
     * @param array $pokemons
     * @param array $fields
     */
    public function makePokemonList(array $pokemons, array $fields): void
    {
        $index = 0;
        foreach($pokemons as $pokemon) {
            for($i = 0; $i < count($fields); $i++) {
                $fieldName = strtolower($fields[$i]);
                $this->pokemonList[$index][$fieldName] = $pokemon[$i];
            } 
            $index++;
        }
    }

    public function filterPokemons(): void
    {
        foreach($this->pokemonList as $index => $pokemon) {
            //Exclude Legendary Pokémon
            if($this->removeLegendaryPokemon($index)) {
                continue;
            }

            //Exclude Pokémon of Type: Ghost
            if($this->removePokemonByType($index, 'Ghost')) {
                continue;
            }
            
            //For Pokémon of Type: Steel, double their HP
            $this->modifyPokemonByType($index, 'Steel', self::MULTIPLEX, 'hp', self::DOUBLE);

            //For Pokémon of Type: Fire, lower their Attack by 10%
            $this->modifyPokemonByType($index, 'Fire', self::MULTIPLEX, 'attack', self::MUNITE_TEN_PERCENT);

            //Increase their Attack Speed by 10% for Bug Pokemons
            $this->modifyPokemonByType($index, 'Bug', self::MULTIPLEX, 'speed', self::PLUS_TEN_PERCENT);

            //Increase their Attack Speed by 10% for Flying Pokemons
            $this->modifyPokemonByType($index, 'Flying', self::MULTIPLEX, 'speed', self::PLUS_TEN_PERCENT);

            //For Pokémon that start with the letter **G**, add +5 Defense for every letter in their name (excluding **G**)
            $this->modifyPokemonByLatter($index, 'G', 'defense', 5.0);
        }
    }

    /**
     * @param int    $index
     */
    public function removeLegendaryPokemon(int $index): bool
    {
        $removeStatus = false;
        if($this->pokemonList[$index]['legendary'] === 'True') {
            unset($this->pokemonList[$index]);
            $removeStatus = true;
        }
        
        return $removeStatus;
    }

    /**
     * @param int    $index
     * @param string $type 
     */
    public function removePokemonByType(int $index, string $type): bool
    {
        $removeStatus = false;
        $pokemon = $this->pokemonList[$index];
        if ($pokemon['type 1'] === $type || $pokemon['type 2'] === $type) {
            unset($this->pokemonList[$index]);
            $removeStatus = true;
        }
        
        return $removeStatus;
    }

    /**
     * @param int $index
     * @param string $type
     * @param string $actionType
     * @param string $field
     * @param float $factor
     */
    private function modifyPokemonByType(int $index, string $type, string $action, string $field = '', float $factor = 0.0): void
    {
        if ($this->pokemonList[$index]['type 1'] === $type || $this->pokemonList[$index]['type 2'] === $type) {
            if($action === self::MULTIPLEX) {
                $this->pokemonList[$index][$field] *= $factor;   
            }  
        }
    }

    /**
     * @param int $index
     * @param string $latter
     * @param string $field
     * @param float $factor
     */
    private function modifyPokemonByLatter(int $index, string $latter, string $field, float $factor): void
    {
        if($this->pokemonList[$index]['name'][0] === $latter) {
            $name = str_replace($latter,'', $this->pokemonList[$index]['name']);
            $addDefense = strlen($this->pokemonList[$index]['name']) * $factor;
            $this->pokemonList[$index][$field] += $addDefense;
        }
    }
}