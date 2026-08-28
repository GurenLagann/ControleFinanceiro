<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Garante um banco Mongo de teste limpo a cada teste, evitando que
     * registros de execucoes anteriores (sem rollback automatico no Mongo)
     * vazem entre testes e quebrem asserts que dependem do estado do banco.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $database = $this->app['db']->connection('mongodb')->getDatabase();

        foreach ($database->listCollectionNames() as $collection) {
            $database->selectCollection($collection)->deleteMany([]);
        }
    }
}
