<?php

namespace Tests\Feature;

use Tests\TestCase;

class ElasticsearchImportTest extends TestCase
{
    /**
     * Test elasticsearch:import command finishes successfully.
     *
     * @return void
     */
    public function test_console_command_finishes_successfully()
    {
        $this->artisan('elasticsearch:import')->assertSuccessful();
    }
}
