<?php

declare(strict_types=1);

namespace OCA\FlzOrganization\Controller;

use OCA\FlzOrganization\Service\OrganizationAdminService;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IRequest;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OCA\FlzOrganization\Controller\AdminController
 */
final class AdminControllerTest extends TestCase {

    private AdminController $controller;
    private OrganizationAdminService $adminService;
    private IRequest $request;

    protected function setUp(): void {
        $this->request = $this->createMock(IRequest::class);
        $this->adminService = $this->createMock(OrganizationAdminService::class);
        
        $this->controller = new AdminController(
            $this->request,
            $this->adminService
        );
    }

    public function testIndexReturnsTemplateResponse(): void {
        $status = ['valid' => true, 'message' => 'OK'];
        $this->adminService->method('getConfigurationStatus')
            ->willReturn($status);

        $response = $this->controller->index();

        self::assertInstanceOf(TemplateResponse::class, $response);
        self::assertSame('flz_organization', $response->getApp());
        self::assertSame('admin/index', $response->getTemplateName());
    }

    public function testOrganizationFormReturnsTemplateResponse(): void {
        $definition = $this->createMock(\OCA\FlzOrganization\Organization\OrganizationDefinition::class);
        $definition->method('toArray')->willReturn(['roles' => []]);
        
        $this->adminService->method('getOrganizationDefinition')
            ->willReturn($definition);

        $response = $this->controller->organizationForm();

        self::assertInstanceOf(TemplateResponse::class, $response);
        self::assertSame('flz_organization', $response->getApp());
        self::assertSame('admin/organization-form', $response->getTemplateName());
    }

    public function testCalendarFormReturnsTemplateResponse(): void {
        $context = $this->createMock(\OCA\FlzOrganization\Calendar\CalendarContext::class);
        $context->method('toArray')->willReturn(['country' => 'DE']);
        
        $this->adminService->method('getCalendarContext')
            ->willReturn($context);

        $response = $this->controller->calendarForm();

        self::assertInstanceOf(TemplateResponse::class, $response);
        self::assertSame('flz_organization', $response->getApp());
        self::assertSame('admin/calendar-form', $response->getTemplateName());
    }

    public function testSaveOrganizationWithSuccess(): void {
        $data = ['roles' => ['admin' => 'Administrator']];
        $definition = $this->createMock(\OCA\FlzOrganization\Organization\OrganizationDefinition::class);
        $definition->method('toArray')->willReturn($data);
        
        $this->adminService->method('saveOrganizationDefinition')
            ->with($data)
            ->willReturn(['success' => true, 'definition' => $definition]);

        $response = $this->controller->saveOrganization($data);

        self::assertInstanceOf(TemplateResponse::class, $response);
        self::assertSame('admin/organization-form', $response->getTemplateName());
    }

    public function testSaveOrganizationWithErrors(): void {
        $data = ['roles' => []];
        $errors = ['roles' => 'Cannot be empty'];
        
        $this->adminService->method('saveOrganizationDefinition')
            ->with($data)
            ->willReturn(['success' => false, 'errors' => $errors]);

        $response = $this->controller->saveOrganization($data);

        self::assertInstanceOf(TemplateResponse::class, $response);
        self::assertSame('admin/organization-form', $response->getTemplateName());
    }

    public function testSaveCalendarWithSuccess(): void {
        $data = ['country' => 'DE', 'timezone' => 'Europe/Berlin'];
        $context = $this->createMock(\OCA\FlzOrganization\Calendar\CalendarContext::class);
        $context->method('toArray')->willReturn($data);
        
        $this->adminService->method('saveCalendarContext')
            ->with($data)
            ->willReturn(['success' => true, 'context' => $context]);

        $response = $this->controller->saveCalendar($data);

        self::assertInstanceOf(TemplateResponse::class, $response);
        self::assertSame('admin/calendar-form', $response->getTemplateName());
    }

    public function testSaveCalendarWithErrors(): void {
        $data = ['country' => ''];
        $errors = ['country' => 'Required'];
        
        $this->adminService->method('saveCalendarContext')
            ->with($data)
            ->willReturn(['success' => false, 'errors' => $errors]);

        $response = $this->controller->saveCalendar($data);

        self::assertInstanceOf(TemplateResponse::class, $response);
        self::assertSame('admin/calendar-form', $response->getTemplateName());
    }

    public function testControllerHasCorrectAppId(): void {
        $reflection = new \ReflectionClass($this->controller);
        $appIdProperty = $reflection->getProperty('appName');
        $appIdProperty->setAccessible(true);
        
        self::assertSame('flz_organization', $appIdProperty->getValue($this->controller));
    }
}
