<?php

declare(strict_types=1);

namespace OCA\FlzOrganization\Permission;

use PHPUnit\Framework\TestCase;

/**
 * @covers \OCA\FlzOrganization\Permission\PermissionProvider
 */
final class PermissionProviderTest extends TestCase {

    private PermissionProvider $provider;

    protected function setUp(): void {
        $this->provider = new PermissionProvider();
    }

    public function testGetPermissionDefinitionsReturnsEmptyArray(): void {
        $definitions = $this->provider->getPermissionDefinitions();
        self::assertIsArray($definitions);
        self::assertEmpty($definitions);
    }

    public function testGetPermissionMappingReturnsEmptyArray(): void {
        $mapping = $this->provider->getPermissionMapping();
        self::assertIsArray($mapping);
        self::assertEmpty($mapping);
    }

    public function testImplementsPermissionProviderInterface(): void {
        self::assertInstanceOf(
            \OCP\Permission\IPermissionProvider::class,
            $this->provider
        );
    }
}
