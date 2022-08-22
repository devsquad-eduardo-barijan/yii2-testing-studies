<?php

namespace tests\unit\models;

use app\models\User;
use app\tests\unit\fixtures\UserFixture;
use InvalidArgumentException;
use UnitTester;
use Yii;
use yii\base\InvalidParamException;
use yii\base\NotSupportedException;
use yii\base\Security;
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

    public function testValidatePasswordReturnsTrueIfPasswordIsCorrect()
    {
        $expectedPassword = 'valid password';
        $this->_mockYiiSecurity($expectedPassword);

        $this->_user->password = Yii::$app->getSecurity()->generatePasswordHash($expectedPassword);

        $this->assertTrue($this->_user->validatePassword($expectedPassword));
    }

    public function testValidatePasswordThrowsInvalidParamExceptionIfPasswordIsIncorrect()
    {
        $this->expectException(InvalidArgumentException::class);

        $password = 'some password';
        $wrongPassword = 'some other password';
        $this->_mockYiiSecurity($password, $wrongPassword);

        $this->_user->password = $password;
        $this->_user->validatePassword($wrongPassword);
    }

    /**
     * Mocks the Yii security module, so we can make it return what we need
     *
     * @param string $expectedPassword the password used for encoding and
     * validating if the second parameter is not set
     * 
     * @param mixed $wrongPassword if passed, validatePassword will thrown an
     * InvalidArgumentException when presenting this string
     */
    private function _mockYiiSecurity($expectedPassword, $wrongPassword = false)
    {
        $security = $this->make(Security::class, [
            'validatePassword' => function () use ($wrongPassword) {
                if ($wrongPassword) {
                    throw new InvalidArgumentException();
                }
                return true;
            },
            'generatePasswordHash' => $expectedPassword
        ]);

        Yii::$app->set('security', $security);
    }
}
