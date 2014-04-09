<?php

namespace Geocoder\Tests\Provider;

use Geocoder\Provider\GeoIP2DatabaseProvider;
use Geocoder\Tests\TestCase;
use Geocoder\Exception\RuntimeException;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamFile;

class GeoIP2DatabaseProviderTest extends TestCase
{
    /**
     * @var GeoIP2DatabaseProvider
     */
    protected $provider;

    /**
     * @inheritdoc
     * @throws RuntimeException
     */
    public static function setUpBeforeClass()
    {
        if (false === class_exists('\GeoIp2\Database\Reader')) {
            throw new RuntimeException("The maxmind's lib 'geoip2/geoip2' is required to run this test.");
        }
    }

    public function setUp()
    {
        $dbFile = new vfsStreamFile('database.mmdb', 0644);
        $this->provider = new MaxMindBinary2Provider($dbFile->getName());
    }

    /**
     * @expectedException \Geocoder\Exception\InvalidArgumentException
     * @expectedExceptionMessage Given MaxMind dat file "not_exist.dat" does not exist.
     */
    public function testNotExistingDatabaseFileLeadsToException()
    {
        new MaxMindBinary2Provider('/not_exists.mmdb');
    }

    /**
     * @expectedException \Geocoder\Exception\InvalidArgumentException
     * @expectedExceptionMessage Given MaxMind dat file "not_exist.dat" does not exist.
     */
    public function testNotReadableDatabaseFileLeadsToException()
    {
        $dbFile = new vfsStreamFile('database.mmdb', 0644);
        $dbFile->chown(vfsStream::OWNER_ROOT);

        new MaxMindBinary2Provider($dbFile->getName());
    }

    public function testGetName()
    {
        $expectedName = 'maxmind_binary_2';
        $this->assertEquals($expectedName, $this->provider->getName());
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedException
     * @expectedExceptionMessage Given MaxMind dat file "not_exist.dat" does not exist.
     */
    public function testOnlyIpAddressesCouldBeResolved()
    {
        $this->provider->getGeocodedData('Street 123, Somewhere');
    }

    public function testLocalhostDefaults()
    {
        $expectedResult = array(
            'city'      => 'localhost',
            'region'    => 'localhost',
            'county'    => 'localhost',
            'country'   => 'localhost',
        );

        $actualResult = $this->provider->getGeocodedData('127.0.0.1');

        $this->assertSame($expectedResult, $actualResult);
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedException
     * @expectedExceptionMessage Given MaxMind dat file "not_exist.dat" does not exist.
     */
    public function testQueryingReversedDataLeadToException()
    {
        $this->provider->getReversedData(array(50, 9));
    }

    /**
     * @dataProvider provideIps
     */
    public function testLocationResultContainsExpectedFields($ip)
    {
        $this->markTestSkipped();

        $results  = $this->provider->getGeocodedData($ip);

        $this->assertInternalType('array', $results);
        $this->assertCount(1, $results);

        $result = $results[0];
        $this->assertInternalType('array', $result);

        $this->assertArrayHasKey('country', $result);
        $this->assertArrayHasKey('countryCode', $result);
        $this->assertArrayHasKey('regionCode', $result);
        $this->assertArrayHasKey('city', $result);
        $this->assertArrayHasKey('latitude', $result);
        $this->assertArrayHasKey('longitude', $result);
        $this->assertArrayHasKey('zipcode', $result);
        $this->assertArrayHasKey('bounds', $result);
        $this->assertArrayHasKey('streetNumber', $result);
        $this->assertArrayHasKey('streetName', $result);
        $this->assertArrayHasKey('cityDistrict', $result);
        $this->assertArrayHasKey('county', $result);
        $this->assertArrayHasKey('countyCode', $result);
        $this->assertArrayHasKey('region', $result);
        $this->assertArrayHasKey('timezone', $result);
    }

    /**
     * @dataProvider provideIps
     */
    public function testFindLocationByIp($ip, $expectedCity, $expectedCountry)
    {
        $this->markTestSkipped();

        $result   = $this->provider->getGeocodedData($ip);

        $this->assertInternalType('array', $result);
        $this->assertCount(1, $result);

        $result = $result[0];
        $this->assertInternalType('array', $result);

        $this->assertArrayHasKey('city', $result);
        $this->assertEquals($expectedCity, $result['city']);
        $this->assertArrayHasKey('country', $result);
        $this->assertEquals($expectedCountry, $result['country']);
    }
}
