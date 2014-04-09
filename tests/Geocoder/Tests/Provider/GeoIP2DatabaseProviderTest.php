<?php

namespace Geocoder\Tests\Provider;

use Geocoder\HttpAdapter\CurlHttpAdapter;
use Geocoder\HttpAdapter\GeoIP2DatabaseAdapter;
use Geocoder\Provider\GeoIP2DatabaseProvider;
use Geocoder\Tests\TestCase;
use Geocoder\Exception\RuntimeException;

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
        $this->provider = new GeoIP2DatabaseProvider($this->getDatabaseAdapterMock());
    }

    /**
     * @expectedException \Geocoder\Exception\InvalidArgumentException
     * @expectedExceptionMessage GeoIP2DatabaseAdapter is needed in order to access the GeoIP2 databases.
     */
    public function testWrongAdapterLeadsToException()
    {
        new GeoIP2DatabaseProvider(new CurlHttpAdapter());
    }

    public function testGetName()
    {
        $expectedName = 'geoip2_database';
        $this->assertEquals($expectedName, $this->provider->getName());
    }

    /**
     * @expectedException \Geocoder\Exception\UnsupportedException
     * @expectedExceptionMessage The Geocoder\Provider\GeoIP2DatabaseProvider is not able to do reverse geocoding.
     */
    public function testQueryingReversedDataLeadToException()
    {
        $this->provider->getReversedData(array(50, 9));
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
     * @expectedExceptionMessage The Geocoder\Provider\GeoIP2DatabaseProvider does not support street addresses.
     */
    public function testOnlyIpAddressesCouldBeResolved()
    {
        $this->provider->getGeocodedData('Street 123, Somewhere');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject | GeoIP2DatabaseAdapter
     */
    public function getDatabaseAdapterMock()
    {
        $mock = $this->getMockBuilder('\Geocoder\HttpAdapter\GeoIP2DatabaseAdapter')->disableOriginalConstructor()->getMock();

        return $mock;
    }
}
