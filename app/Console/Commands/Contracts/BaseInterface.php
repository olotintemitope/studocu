<?php


namespace App\Console\Commands\Contracts;


interface BaseInterface
{
    public function insert(array $data): void;
    public function prepareData(array $data): array;
}