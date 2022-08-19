<?php

namespace tests\unit\models;

use app\models\User;
use app\tests\unit\fixtures\UserFixture;
use UnitTester;
use yii\base\NotSupportedException;
use yii\web\IdentityInterface;

class UserTest extends \Codeception\Test\Unit
{
    /** @var User */
    private $_user = null;

    protected UnitTester $tester;

    protected function setUp(): void
    {
        parent::setUp();

        // setup global user
        $this->_user = new User();
    }

    public function _fixtures()
    {
        return [
            'user' => UserFixture::class
        ];
    }

    public function testValidateReturnsFalseIfParametersAreNotSet()
    {
        $this->assertFalse($this->_user->validate(), 'New User should not validate');
    }

    public function testValidateReturnsTrueIfParametersAreSet()
    {
        $configurationParams = [
            'username' => 'a valid username',
            'password' => 'a valid password',
            'authkey' => 'a valid authkey'
        ];

        $user = new User($configurationParams);
        $this->assertTrue($user->validate(), 'User with set parameters should validate');
    }

    public function testFindIdentityByAccessTokenReturnsTheExpectedObject()
    {
        $this->expectException(NotSupportedException::class);

        User::findIdentityByAccessToken('anyAccessToken');
    }

    public function testGetIdReturnsTheExpectedId()
    {
        $this->_user->id = 2;

        $this->assertEquals(2, $this->_user->getId());
    }

    public function testGetAuthkeyReturnsTheExpectedAuthkey()
    {
        $this->_user->authkey = 'someauth';

        $this->assertEquals('someauth', $this->_user->getAuthKey());
    }

    public function testFindIdentityReturnsTheExpectedObject()
    {
        $expectedAttrs = $this->tester->grabFixture('user', 'admin');

        /** @var User $user */
        $user = User::findIdentity($expectedAttrs['id']);

        $this->assertNotNull($user);
        $this->assertInstanceOf(IdentityInterface::class, $user);
        $this->assertEquals($expectedAttrs['username'], $user->username);
        $this->assertEquals($expectedAttrs['password'], $user->password);
        $this->assertEquals($expectedAttrs['authkey'], $user->authkey);
    }

    /** 
     * @dataProvider nonExistingIdsDataProvider
     */
    public function testFindIdentityReturnsNullIfUserIsNotFound($invalidId)
    {
        $this->assertNull(User::findIdentity($invalidId));
    }

    public function nonExistingIdsDataProvider()
    {
        return [[-1], [null], [30]];
    }

    public function testFindByUsernameReturnsTheExpectedObject()
    {
        $expectedAttrs = $this->tester->grabFixture('user', 'admin');

        /** @var User $user */
        $user = User::findByUsername($expectedAttrs['username']);

        $this->assertNotNull($user);
        $this->assertInstanceOf(IdentityInterface::class, $user);
        $this->assertEquals($expectedAttrs['username'], $user->username);
        $this->assertEquals($expectedAttrs['password'], $user->password);
        $this->assertEquals($expectedAttrs['authkey'], $user->authkey);
    }

    /** 
     * @dataProvider nonExistingNamesDataProvider
     */
    public function testFindByUsernameReturnsNullIfUserIsNotFound($invalidNames)
    {
        $this->assertNull(User::findByUsername($invalidNames));
    }

    public function nonExistingNamesDataProvider()
    {
        return [[''], [null], ['somename']];
    }

    // public function testFindUserById()
    // {
    //     expect_that($user = User::findIdentity(100));
    //     expect($user->username)->equals('admin');

    //     expect_not(User::findIdentity(999));
    // }

    // public function testFindUserByAccessToken()
    // {
    //     expect_that($user = User::findIdentityByAccessToken('100-token'));
    //     expect($user->username)->equals('admin');

    //     expect_not(User::findIdentityByAccessToken('non-existing'));
    // }

    // public function testFindUserByUsername()
    // {
    //     expect_that($user = User::findByUsername('admin'));
    //     expect_not(User::findByUsername('not-admin'));
    // }

    // /**
    //  * @depends testFindUserByUsername
    //  */
    // public function testValidateUser($user)
    // {
    //     $user = User::findByUsername('admin');
    //     expect_that($user->validateAuthKey('test100key'));
    //     expect_not($user->validateAuthKey('test102key'));

    //     expect_that($user->validatePassword('admin'));
    //     expect_not($user->validatePassword('123456'));
    // }
}
