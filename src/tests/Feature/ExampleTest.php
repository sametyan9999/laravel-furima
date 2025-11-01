<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_example(): void
    {
        $res = $this->get('/login');
        $res->assertOk();
    }
}
