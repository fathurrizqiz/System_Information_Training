<?php

namespace Tests\Feature;

use Tests\TestCase;

class DiklatRouteTest extends TestCase
{
    public function test_web_diklat_store_route_uses_web_uri(): void
    {
        $this->assertSame('/Diklat/store', route('diklat.web.store', [], false));
    }
}
