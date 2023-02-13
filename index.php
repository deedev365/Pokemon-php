<?php

spl_autoload_register(function ($className) {
    $newClassName = str_replace("\\", "/", $className) .".php";
    require 'class/' . $newClassName;
  });

$pokemon = new Pokemon();
$api = new Api($pokemon->pokemonList);