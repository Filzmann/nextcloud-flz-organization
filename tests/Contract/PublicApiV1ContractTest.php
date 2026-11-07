<?php

declare(strict_types=1);

use OCA\FlzOrganization\PublicApi\V1\AbsenceInterval;
use OCA\FlzOrganization\PublicApi\V1\AbsenceQueryEvent;
use OCA\FlzOrganization\PublicApi\V1\CalendarContextSnapshot;
use OCA\FlzOrganization\PublicApi\V1\Contract;
use OCA\FlzOrganization\PublicApi\V1\HolidayCalendarSnapshot;
use OCA\FlzOrganization\PublicApi\V1\IntegrationCapabilityQueryEvent;
use OCA\FlzOrganization\PublicApi\V1\OrganizationSnapshot;
use OCA\FlzOrganization\PublicApi\V1\ScheduleConflict;
use OCA\FlzOrganization\PublicApi\V1\ScheduleConflictQueryEvent;

assertSame('flz_organization', Contract::APP_ID, 'Consumers need a stable provider app ID.');
assertTrue(Contract::supports('1.0'), 'The provider must advertise V1 compatibility.');
assertTrue(!Contract::supports('2.0'), 'Unknown major versions must fail closed.');

$invalidOrganization = new OrganizationSnapshot(false, 5, ['office' => ['groupId' => 'office', 'label' => 'Office']], []);
assertSame([], $invalidOrganization->roles(), 'An invalid organization snapshot must expose no roles.');
assertSame([], $invalidOrganization->identityGroups(), 'An invalid organization snapshot must expose no identity groups.');
assertTrue(!$invalidOrganization->isValid(), 'An invalid organization snapshot must remain explicitly invalid.');

$organization = new OrganizationSnapshot(
    true,
    5,
    ['office' => ['groupId' => 'office', 'label' => 'Office']],
    [],
    ['works_council_member' => ['groupId' => 'BR-Members', 'label' => 'Works council members']],
    [
        'version' => 5,
        'staffBlockLabel' => 'Leadership and staff',
        'roles' => ['office' => ['groupId' => 'office', 'label' => 'Office', 'calendarVisible' => true]],
        'areas' => [],
        'hierarchy' => [],
    ],
);
assertSame('BR-Members', $organization->identityGroupId('works_council_member'), 'Consumers need the canonical works-council membership identity from the organization snapshot.');
assertSame('Leadership and staff', $organization->definition()['staffBlockLabel'] ?? null, 'Calendar consumers need the validated public organization definition without private AppConfig access.');

assertThrows(
    static fn(): OrganizationSnapshot => new OrganizationSnapshot(true, 5, ['office' => ['groupId' => 'duplicate', 'label' => 'Office']], ['west' => ['groupId' => 'duplicate', 'label' => 'West']]),
    InvalidArgumentException::class,
    'Duplicate role and area groups must be rejected.',
);
assertThrows(
    static fn(): OrganizationSnapshot => new OrganizationSnapshot(true, 5, ['office' => ['groupId' => 'duplicate', 'label' => 'Office']], [], ['works_council_member' => ['groupId' => 'duplicate', 'label' => 'Works council members']]),
    InvalidArgumentException::class,
    'Identity groups must not duplicate role or area group IDs.',
);

$context = new CalendarContextSnapshot('DE', 'DE-BE', 'Europe/Berlin');
assertSame('Europe/Berlin', $context->timezone(), 'The public context must expose the organization timezone.');

$unavailable = new HolidayCalendarSnapshot(2027, 'Europe/Berlin', 'unavailable', ['2027-01-01'], null);
assertSame([], $unavailable->publicHolidayDates(), 'Unavailable holiday data must not masquerade as a usable list.');
assertTrue(!$unavailable->isUsable(), 'Unavailable holiday data must be explicitly unusable.');

$holiday = new HolidayCalendarSnapshot(
    2027,
    'Europe/Berlin',
    'fresh',
    ['2027-01-01'],
    '2026-12-01T00:00:00+00:00',
    [['startDate' => '2027-01-01', 'endDate' => '2027-01-01', 'name' => 'New Year']],
);
assertSame('New Year', $holiday->publicHolidays()[0]['name'] ?? null, 'Calendar consumers need public holiday labels as well as dates.');
assertSame('1.0', $holiday->toArray()['contractVersion'] ?? null, 'Holiday projections need a stable serializable V1 payload.');

$start = new DateTimeImmutable('2027-02-01T00:00:00+00:00');
$end = new DateTimeImmutable('2027-02-08T00:00:00+00:00');
$absenceQuery = new AbsenceQueryEvent($start, $end, ['user-a']);
$absenceQuery->add(new AbsenceInterval('user-a', $start, $end, AbsenceInterval::STATUS_APPROVED));
$absenceQuery->add(new AbsenceInterval('user-b', $start, $end, AbsenceInterval::STATUS_APPROVED));
assertSame(1, count($absenceQuery->absences()), 'Absence providers may only return requested subjects.');

$conflicts = new ScheduleConflictQueryEvent('user-a', $start, $end, 'flzcalendar');
$conflicts->add(new ScheduleConflict('appointment', $start, $end, 'Own', 'flzcalendar'));
$conflicts->add(new ScheduleConflict('shift', $start, $end, 'External', 'flzplaner'));
assertSame(1, count($conflicts->conflicts()), 'Conflict queries must suppress requester self-echoes.');

$capabilities = new IntegrationCapabilityQueryEvent(['absence.read']);
$capabilities->provide('flzurlaub', ['absence.read', 'unknown.write']);
assertSame(['flzurlaub'], $capabilities->providersFor('absence.read'), 'Capabilities must be limited to explicitly requested keys.');
assertSame([], $capabilities->providersFor('unknown.write'), 'Unrequested capabilities must remain invisible.');

