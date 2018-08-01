<?php

namespace Recurr\Test;

use Recurr\DateExclusion;
use Recurr\DateInclusion;
use Recurr\Frequency;
use Recurr\Rule;

class RuleTest extends \PHPUnit_Framework_TestCase
{
	/** @var Rule */
	protected $rule;

	public function setUp()
	{
		$this->rule = new Rule();
	}

	public function testDefaultTimezone()
	{
		$this->assertSame(date_default_timezone_get(), $this->rule->getTimezone());
	}

	public function testTimezoneObtainedFromStartDate()
	{
		$startDate = new \DateTime('2014-01-25 05:20:30', new \DateTimeZone('America/Los_Angeles'));

		$this->rule = new Rule(null, $startDate);
		$this->assertSame($startDate->getTimezone()->getName(), $this->rule->getTimezone());
	}

	/**
	 * @expectedException \Recurr\Exception\InvalidRRule
	 */
	public function testLoadFromStringWithMissingFreq()
	{
		$this->rule->loadFromString('COUNT=2');
	}

	/**
	 * @expectedException \Recurr\Exception\InvalidRRule
	 */
	public function testLoadFromStringWithBothCountAndUntil()
	{
		$this->rule->loadFromString('FREQ=DAILY;COUNT=2;UNTIL=20130510');
	}

	public function testLoadFromString()
	{
		$string = 'FREQ=YEARLY;';
		$string .= 'COUNT=2;';
		$string .= 'INTERVAL=2;';
		$string .= 'BYSECOND=30;';
		$string .= 'BYMINUTE=10;';
		$string .= 'BYHOUR=5,15;';
		$string .= 'BYDAY=SU,WE;';
		$string .= 'BYMONTHDAY=16,22;';
		$string .= 'BYYEARDAY=201,203;';
		$string .= 'BYWEEKNO=29,32;';
		$string .= 'BYMONTH=7,8;';
		$string .= 'BYSETPOS=1,3;';
		$string .= 'WKST=TU;';
		$string .= 'RDATE=20151210,20151214T020000,20151215T210000Z;';
		$string .= 'EXDATE=20140607,20140620T010000,20140620T160000Z;';

		$this->rule->loadFromString($string);

		$this->assertSame(Frequency::YEARLY, $this->rule->getFreq());
		$this->assertSame(2, $this->rule->getCount());
		$this->assertSame(2, $this->rule->getInterval());
		$this->assertSame([30], $this->rule->getBySecond());
		$this->assertSame([10], $this->rule->getByMinute());
		$this->assertSame([5, 15], $this->rule->getByHour());
		$this->assertSame(['SU', 'WE'], $this->rule->getByDay());
		$this->assertSame([16, 22], $this->rule->getByMonthDay());
		$this->assertSame([201, 203], $this->rule->getByYearDay());
		$this->assertSame([29, 32], $this->rule->getByWeekNumber());
		$this->assertSame([7, 8], $this->rule->getByMonth());
		$this->assertSame([1, 3], $this->rule->getBySetPosition());
		$this->assertSame('TU', $this->rule->getWeekStart());
		$this->assertSame(
			[
				new DateInclusion(new \DateTime(20151210), false),
				new DateInclusion(new \DateTime('20151214T020000'), true),
				new DateInclusion(new \DateTime('20151215 21:00:00 UTC'), true, true)
			],
			$this->rule->getRDates()
		);
		$this->assertSame(
			[
				new DateExclusion(new \DateTime(20140607), false),
				new DateExclusion(new \DateTime('20140620T010000'), true),
				new DateExclusion(new \DateTime('20140620 16:00:00 UTC'), true, true)
			],
			$this->rule->getExDates()
		);
	}

	public function testLoadFromArray()
	{
		$this->rule->loadFromArray([
			'FREQ'=>'YEARLY',
			'COUNT'=>'2',
			'INTERVAL'=>'2',
			'BYSECOND'=>'30',
			'BYMINUTE'=>'10',
			'BYHOUR'=>'5,15',
			'BYDAY'=>'SU,WE',
			'BYMONTHDAY'=>'16,22',
			'BYYEARDAY'=>'201,203',
			'BYWEEKNO'=>'29,32',
			'BYMONTH'=>'7,8',
			'BYSETPOS'=>'1,3',
			'WKST'=>'TU',
			'RDATE'=>'20151210,20151214T020000,20151215T210000Z',
			'EXDATE'=>'20140607,20140620T010000,20140620T160000Z',
		]);

		$this->assertSame(Frequency::YEARLY, $this->rule->getFreq());
		$this->assertSame(2, $this->rule->getCount());
		$this->assertSame(2, $this->rule->getInterval());
		$this->assertSame([30], $this->rule->getBySecond());
		$this->assertSame([10], $this->rule->getByMinute());
		$this->assertSame([5, 15], $this->rule->getByHour());
		$this->assertSame(['SU', 'WE'], $this->rule->getByDay());
		$this->assertSame([16, 22], $this->rule->getByMonthDay());
		$this->assertSame([201, 203], $this->rule->getByYearDay());
		$this->assertSame([29, 32], $this->rule->getByWeekNumber());
		$this->assertSame([7, 8], $this->rule->getByMonth());
		$this->assertSame([1, 3], $this->rule->getBySetPosition());
		$this->assertSame('TU', $this->rule->getWeekStart());
		$this->assertSame(
			[
				new DateInclusion(new \DateTime(20151210), false),
				new DateInclusion(new \DateTime('20151214T020000'), true),
				new DateInclusion(new \DateTime('20151215 21:00:00 UTC'), true, true)
			],
			$this->rule->getRDates()
		);
		$this->assertSame(
			[
				new DateExclusion(new \DateTime(20140607), false),
				new DateExclusion(new \DateTime('20140620T010000'), true),
				new DateExclusion(new \DateTime('20140620 16:00:00 UTC'), true, true)
			],
			$this->rule->getExDates()
		);
	}

	public function testLoadFromStringWithDtstart()
	{
		$defaultTimezone = date_default_timezone_get();
		date_default_timezone_set('America/Chicago');

		$string = 'FREQ=MONTHLY;DTSTART=20140222T073000';

		$this->rule->setTimezone('America/Los_Angeles');
		$this->rule->loadFromString($string);

		$expectedStartDate = new \DateTime('2014-02-22 05:30:00', new \DateTimeZone('America/Los_Angeles'));

		$this->assertSame(Frequency::MONTHLY, $this->rule->getFreq());
		$this->assertSame($expectedStartDate, $this->rule->getStartDate());

		date_default_timezone_set($defaultTimezone);
	}

	public function testLoadFromStringWithDtend()
	{
		$defaultTimezone = date_default_timezone_get();
		date_default_timezone_set('America/Chicago');
		$string = 'FREQ=MONTHLY;DTEND=20140422T140000';

		$this->rule->setTimezone('America/Los_Angeles');
		$this->rule->loadFromString($string);

		$expectedEndDate = new \DateTime('2014-04-22 12:00:00', new \DateTimeZone('America/Los_Angeles'));

		$this->assertSame(Frequency::MONTHLY, $this->rule->getFreq());
		$this->assertSame($expectedEndDate, $this->rule->getEndDate());

		date_default_timezone_set($defaultTimezone);
	}

	/**
	 * @expectedException \Recurr\Exception\InvalidRRule
	 */
	public function testLoadFromStringFails()
	{
		$this->rule->loadFromString('IM AN INVALID RRULE');
	}

	public function testGetString()
	{
		$this->rule->setFreq('YEARLY');
		$this->rule->setCount(2);
		$this->rule->setInterval(2);
		$this->rule->setBySecond([30]);
		$this->rule->setByMinute([10]);
		$this->rule->setByHour([5, 15]);
		$this->rule->setByDay(['SU', 'WE']);
		$this->rule->setByMonthDay([16, 22]);
		$this->rule->setByYearDay([201, 203]);
		$this->rule->setByWeekNumber([29, 32]);
		$this->rule->setByMonth([7, 8]);
		$this->rule->setBySetPosition([1, 3]);
		$this->rule->setWeekStart('TU');
		$this->rule->setRDates(['20151210', '20151214T020000Z', '20151215T210000']);
		$this->rule->setExDates(['20140607', '20140620T010000Z', '20140620T010000']);

		$this->assertSame(
			'FREQ=YEARLY;COUNT=2;INTERVAL=2;BYSECOND=30;BYMINUTE=10;BYHOUR=5,15;BYDAY=SU,WE;BYMONTHDAY=16,22;BYYEARDAY=201,203;BYWEEKNO=29,32;BYMONTH=7,8;BYSETPOS=1,3;WKST=TU;RDATE=20151210,20151214T020000Z,20151215T210000;EXDATE=20140607,20140620T010000Z,20140620T010000',
			$this->rule->getString()
		);
	}

	public function testGetStringWithUTC()
	{
		$this->rule->setFreq('DAILY');
		$this->rule->setInterval(1);
		$this->rule->setUntil(new \DateTime('2015-07-10 04:00:00', new \DateTimeZone('America/New_York')));

		$this->assertNotSame(
			'FREQ=DAILY;UNTIL=20150710T040000Z;INTERVAL=1',
			$this->rule->getString()
		);

		$this->assertSame(
			'FREQ=DAILY;UNTIL=20150710T080000Z;INTERVAL=1',
			$this->rule->getString(Rule::TZ_FIXED)
		);
	}

	public function testGetStringWithDtstart()
	{
		$string = 'FREQ=MONTHLY;DTSTART=20140210T163045;INTERVAL=1;WKST=MO';

		$this->rule->loadFromString($string);

		$this->assertSame($string, $this->rule->getString());
	}

	public function testGetStringWithDtend()
	{
		$string = 'FREQ=MONTHLY;DTEND=20140410T163045;INTERVAL=1;WKST=MO';

		$this->rule->loadFromString($string);

		$this->assertSame($string, $this->rule->getString());
	}

	public function testGetStringWithUntil()
	{
		$string = 'FREQ=MONTHLY;UNTIL=20140410T163045;INTERVAL=1;WKST=MO';

		$this->rule->loadFromString($string);

		$this->assertSame($string, $this->rule->getString());
	}

	public function testGetStringWithUntilUsingZuluTime()
	{
		$string = 'FREQ=MONTHLY;UNTIL=20170331T040000Z;INTERVAL=1;WKST=MO';

		$this->rule->loadFromString($string);

		$this->assertSame($string, $this->rule->getString(Rule::TZ_FIXED));
	}

	public function testGetStringWithoutExplicitWkst()
	{
		$string = 'FREQ=MONTHLY;COUNT=2;INTERVAL=1';

		$this->rule->loadFromString($string);

		$this->assertNotContains('WKST', $this->rule->getString());
	}

	public function testGetStringWithExplicitWkst()
	{
		$string = 'FREQ=MONTHLY;COUNT=2;INTERVAL=1;WKST=TH';

		$this->rule->loadFromString($string);

		$this->assertContains('WKST=TH', $this->rule->getString());
	}

	public function testSetStartDateAffectsStringOutput()
	{
		$this->rule->loadFromString('FREQ=MONTHLY;COUNT=2');
		$this->assertSame('FREQ=MONTHLY;COUNT=2', $this->rule->getString());

		$this->rule->setStartDate(new \DateTime('2015-12-10'));
		$this->assertSame('FREQ=MONTHLY;COUNT=2', $this->rule->getString());

		$this->rule->setStartDate(new \DateTime('2015-12-10'), true);
		$this->assertSame('FREQ=MONTHLY;COUNT=2;DTSTART=20151210T000000', $this->rule->getString());

		$this->rule->setStartDate(new \DateTime('2015-12-10'), false);
		$this->assertSame('FREQ=MONTHLY;COUNT=2', $this->rule->getString());
	}

	/**
	 * @expectedException \Recurr\Exception\InvalidArgument
	 */
	public function testBadInterval()
	{
		$this->rule->setInterval('six');
	}

	/**
	 * @expectedException \Recurr\Exception\InvalidRRule
	 */
	public function testEmptyByDayThrowsException()
	{
		$this->rule->setByDay([]);
	}

	/**
	 * @expectedException \Recurr\Exception\InvalidRRule
	 */
	public function testEmptyByDayFromStringThrowsException()
	{
		$this->rule->loadFromString('FREQ=WEEKLY;BYDAY=;INTERVAL=1;UNTIL=20160725');
	}

	/**
	 * @expectedException \Recurr\Exception\InvalidArgument
	 */
	public function testBadWeekStart()
	{
		$this->rule->setWeekStart('monday');
	}

	/**
	 * @dataProvider exampleRruleProvider
	 */
	public function testRepeatsIndefinitely($string, $expected)
	{
		$this->assertSame($expected, $this->rule->loadFromString($string)->repeatsIndefinitely());
	}

	/**
	 * Taken from https://tools.ietf.org/html/rfc5545#section-3.8.5.3.
	 *
	 * @return array
	 */
	public function exampleRruleProvider()
	{
		return [
			['FREQ=DAILY;COUNT=10', false],
			['FREQ=DAILY;UNTIL=19971224T000000Z', false],
			['FREQ=DAILY;INTERVAL=2', true],
			['FREQ=DAILY;INTERVAL=10;COUNT=5', false],
			['FREQ=YEARLY;UNTIL=20000131T140000Z', false],
			['FREQ=DAILY;UNTIL=20000131T140000Z;BYMONTH=1', false],
			['FREQ=WEEKLY;COUNT=10', false],
			['FREQ=WEEKLY;UNTIL=19971224T000000Z', false],
			['FREQ=WEEKLY;INTERVAL=2;WKST=SU', true],
			['FREQ=WEEKLY;UNTIL=19971007T000000Z;WKST=SU;BYDAY=TU,TH', false],
			['FREQ=WEEKLY;COUNT=10;WKST=SU;BYDAY=TU,TH', false],
			['FREQ=WEEKLY;INTERVAL=2;UNTIL=19971224T000000Z;WKST=SU', false],
			['FREQ=WEEKLY;INTERVAL=2;COUNT=8;WKST=SU;BYDAY=TU,TH', false],
			['FREQ=MONTHLY;COUNT=10;BYDAY=1FR', false],
			['FREQ=MONTHLY;UNTIL=19971224T000000Z;BYDAY=1FR', false],
			['FREQ=MONTHLY;INTERVAL=2;COUNT=10;BYDAY=1SU,-1SU', false],
			['FREQ=MONTHLY;COUNT=6;BYDAY=-2MO', false],
			['FREQ=MONTHLY;BYMONTHDAY=-3', true],
			['FREQ=MONTHLY;COUNT=10;BYMONTHDAY=2,15', false],
			['FREQ=MONTHLY;COUNT=10;BYMONTHDAY=1,-1', false],
			['FREQ=MONTHLY;INTERVAL=18;COUNT=10;BYMONTHDAY=10,11,12,', false],
			['FREQ=MONTHLY;INTERVAL=2;BYDAY=TU', true],
			['FREQ=YEARLY;COUNT=10;BYMONTH=6,7', false],
			['FREQ=YEARLY;INTERVAL=2;COUNT=10;BYMONTH=1,2,3', false],
			['FREQ=YEARLY;INTERVAL=3;COUNT=10;BYYEARDAY=1,100,200', false],
			['FREQ=YEARLY;BYDAY=20MO', true],
			['FREQ=YEARLY;BYWEEKNO=20;BYDAY=MO', true],
			['FREQ=YEARLY;BYMONTH=3;BYDAY=TH', true],
			['FREQ=YEARLY;BYDAY=TH;BYMONTH=6,7,8', true],
			['FREQ=MONTHLY;BYDAY=FR;BYMONTHDAY=13', true],
			['FREQ=MONTHLY;BYDAY=SA;BYMONTHDAY=7,8,9,10,11,12,13', true],
			['FREQ=YEARLY;INTERVAL=4;BYMONTH=11;BYDAY=TU', true],
			['FREQ=MONTHLY;COUNT=3;BYDAY=TU,WE,TH;BYSETPOS=3', false],
			['FREQ=MONTHLY;BYDAY=MO,TU,WE,TH,FR;BYSETPOS=-2', true],
			['FREQ=HOURLY;INTERVAL=3;UNTIL=19970902T170000Z', false],
			['FREQ=MINUTELY;INTERVAL=15;COUNT=6', false],
			['FREQ=MINUTELY;INTERVAL=90;COUNT=4', false],
			['FREQ=DAILY;BYHOUR=9,10,11,12,13,14,15,16;BYMINUTE=0,20,40', true],
			['FREQ=MINUTELY;INTERVAL=20;BYHOUR=9,10,11,12,13,14,15,16', true],
			['FREQ=WEEKLY;INTERVAL=2;COUNT=4;BYDAY=TU,SU;WKST=MO', false],
			['FREQ=WEEKLY;INTERVAL=2;COUNT=4;BYDAY=TU,SU;WKST=SU', false],
			['FREQ=MONTHLY;BYMONTHDAY=15,30;COUNT=5', false],
		];
	}
}