<?php

namespace Tests\ShopBundle\Smoke;

use Tests\ShopBundle\Test\FunctionalTestCase;

class AdminAccessRightsTest extends FunctionalTestCase
{
    public function adminAccessDeniedProvider()
    {
        return [
            ['admin/superadmin/modules/'],
            ['admin/superadmin/errors/'],
            ['admin/superadmin/pricing/'],
        ];
    }

    /**
     * @dataProvider adminAccessDeniedProvider
     */
    public function testAdminAccessDenied($route)
    {
        $client = $this->getClient(true, 'admin', 'admin123');
        $client->request('GET', $route);
        $this->assertSame(404, $client->getResponse()->getStatusCode());
    }
}
