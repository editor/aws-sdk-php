<?php

namespace Aws\Tests\S3\Model;

use Aws\S3\Model\Grantee;
use Aws\S3\Enum\Group;
use Aws\S3\Enum\GranteeType;

/**
 * @covers Aws\S3\Model\Grantee
 */
class GranteeTest extends \Guzzle\Tests\GuzzleTestCase
{
    public function testCanCreateCanonicalUserGrantee()
    {
        $grantee = new Grantee('1234567890', 'foo');

        $this->assertEquals('1234567890', $grantee->getId());
        $this->assertEquals('foo', $grantee->getDisplayName());
        $this->assertTrue($grantee->isCanonicalUser());
        $this->assertFalse($grantee->isAmazonCustomerByEmail());
        $this->assertFalse($grantee->isGroup());
        $this->assertEquals(GranteeType::USER, $grantee->getType());
    }

    public function testCanCreateEmailAddressGrantee()
    {
        $grantee = new Grantee('foo@example.com');

        $this->assertEquals('foo@example.com', $grantee->getId());
        $this->assertEquals('foo@example.com', $grantee->getEmailAddress());
        $this->assertNull($grantee->getDisplayName());
        $this->assertFalse($grantee->isCanonicalUser());
        $this->assertTrue($grantee->isAmazonCustomerByEmail());
        $this->assertFalse($grantee->isGroup());
        $this->assertEquals(GranteeType::EMAIL, $grantee->getType());
    }

    public function testCanCreateGroupGrantee()
    {
        $grantee = new Grantee(Group::ALL_USERS);

        $this->assertEquals(Group::ALL_USERS, $grantee->getId());
        $this->assertEquals(Group::ALL_USERS, $grantee->getGroupUri());
        $this->assertNull($grantee->getDisplayName());
        $this->assertFalse($grantee->isCanonicalUser());
        $this->assertFalse($grantee->isAmazonCustomerByEmail());
        $this->assertTrue($grantee->isGroup());
        $this->assertEquals(GranteeType::GROUP, $grantee->getType());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testThrowsExceptionWhenGranteeIdNotValid()
    {
        $grantee = new Grantee(100);
    }

    /**
     * @expectedException \UnexpectedValueException
     */
    public function testThrowsExceptionWhenTypeDoesntMatch()
    {
        $grantee = new Grantee('foo@example.com', null, GranteeType::GROUP);
    }

    /**
     * @expectedException \LogicException
     */
    public function testThrowsExceptionWhenSettingDisplayNameForWrongTypes()
    {
        $grantee = new Grantee('foo@example.com');
        $grantee->setDisplayName('FooBar');
    }

    public function testDisplayNameSetToIdWhenNotSpecified()
    {
        $grantee = new Grantee('1234567890');
        $this->assertEquals('1234567890', $grantee->getId());
        $this->assertEquals('1234567890', $grantee->getDisplayName());
    }

    public function getDataForToStringTest()
    {
        $cases = array();
        $cases[] = array('1234567890', '<Grantee xmlns:xsi="http://www.w3.org/2'
            . '001/XMLSchema-instance" xsi:type="CanonicalUser"><ID>1234567890<'
            . '/ID><DisplayName>1234567890</DisplayName></Grantee>');
        $cases[] = array('foo@example.com', '<Grantee xmlns:xsi="http://www.w3.'
            . 'org/2001/XMLSchema-instance" xsi:type="AmazonCustomerByEmail"><E'
            . 'mailAddress>foo@example.com</EmailAddress></Grantee>');
        $cases[] = array(Group::ALL_USERS, '<Grantee xmlns:xsi="http://www.w3.o'
            . 'rg/2001/XMLSchema-instance" xsi:type="Group"><URI>'
            . Group::ALL_USERS . '</URI></Grantee>');

        return $cases;
    }

    /**
     * @dataProvider getDataForToStringTest
     */
    public function testToStringProducesExpectedXml($id, $xml)
    {
        $grantee = new Grantee($id);
        $this->assertEquals($xml, (string) $grantee);
    }

    public function getDataForHeaderValueTest()
    {
        return array(
            array('user-id',         'id="user-id"'),
            array('foo@example.com', 'emailAddress="foo@example.com"'),
            array(GROUP::ALL_USERS,  'uri="' . GROUP::ALL_USERS . '"'),
        );
    }

    /**
     * @dataProvider getDataForHeaderValueTest
     */
    public function testGetHeaderValueProducesExpectedResult($id, $value)
    {
        $grant = new Grantee($id);
        $this->assertSame($value, $grant->getHeaderValue());
    }
}