<?php

declare(strict_types=1);

class Api
{
    public array $apiQueries;
    public array $apiFields;
    public array $pokemonList;
    public int   $paginationPage = 1;
    public const POKEMONS_PER_PAGE = 10;

    public function __construct($pokemonList)
    {
        $this->pokemonList = $pokemonList;
        $this->checkApiPage();
    }

    private function checkApiPage(): void
    {
        $this->getApiQueries();
        $this->getApiFields();
        $this->getApiPokemons();
        $this->printApiResult();
    }

    public function getApiQueries(): void
    {
        $url = $_SERVER['REQUEST_URI'];
        $this->apiQueries = [];
        if(strstr($url, '?')) {
            $url = explode('?', $url);
            $this->apiQueries = explode('&', $url[1]);
        }
    }

    private function getApiFields(): void
    {
        $apiFields = [];
        foreach($this->apiQueries as $apiQuery) {
            $field = explode('=', $apiQuery);

            if($field[0] === 'page') {
                $this->paginationPage = (int) $field[1];
                continue;
            }

            $name = explode('[', $field[0]);
            $param = '';
            if(strstr($field[0],'[') && strstr($field[0],']')) {
                preg_match_all('/\\[(.*?)\\]/', $field[0], $param);
                $param = $param[1][0];
            } 

            $value = $field[1];
            $apiFields[] = [
                'name' => $name[0],
                'param' => $param,
                'value' => $value,    
            ];
        }

        $this->apiFields = $apiFields;
    }

    private function getApiPokemons(): void
    {
        foreach($this->pokemonList as $index => $pokemon) { 
            foreach($this->apiFields as $apiField) {
                $paramName = $apiField['name'];

                if($apiField['param'] == 'gte') {
                    if($pokemon[$paramName] < $apiField['value']) {
                        unset($this->pokemonList[$index]);
                    } 
                } elseif($apiField['param'] == 'lte') {
                    if($pokemon[$paramName] > $apiField['value']) {
                        unset($this->pokemonList[$index]);
                    } 
                } 
            }
        }
        sort($this->pokemonList);
    }

    private function printApiResult(): void
    {
        echo '<h2>Pokemon World!</h2>';
        $this->printApiLinkExamples();
        
        echo '<h4>Pokemon Info</h4>';
        echo '<ul>';
            echo '<li>Found pokemons: ' . count($this->pokemonList) . '</li>';
            echo '<li>Pages: '. round(count($this->pokemonList) / self::POKEMONS_PER_PAGE) .'</li>';
            echo '<li>Pokemons per page: ' . self::POKEMONS_PER_PAGE . '</li>';
        echo '</ul>';

        $fistIndex = ($this->paginationPage - 1) * self::POKEMONS_PER_PAGE;
        $lastIndex = $fistIndex + self::POKEMONS_PER_PAGE - 1;
        
        if($lastIndex >= count($this->pokemonList)) {
            $lastIndex = count($this->pokemonList) - 1;   
        }

        for($index = $fistIndex; $index <= $lastIndex; $index++ ) {
            echo '<pre>';
            var_dump($this->pokemonList[$index]);
            echo '</pre>';
        }
    }

    public function printApiLinkExamples()
    {
        $links = [
            'hp>=100' => '/pokemon?hp[gte]=100&page=1',
            'hp>=50 & attack<=100' => '/pokemon?hp[gte]=50&attack[lte]=100',
            'speed>=50 & defense<=100' => '/pokemon?speed[gte]=150&defense[lte]=100&page=1',
        ];

        echo '<h4>Api Link Examples</h4>';
        echo '<ul>';
            foreach($links as $name => $url) {
                echo '<li><a href="'. $url .'">'. $name .'</a></li>';
            }
        echo '</ul>';
    }
}