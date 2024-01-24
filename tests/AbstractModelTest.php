<?php

namespace tests;
require_once 'vendor/autoload.php';
require_once 'Models/UserModel.php';
require_once 'App.php';

use Models\AbstractModel;
use Models\UserModel;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(AbstractModel::class)]
#[UsesClass(UserModel::class)]
#[UsesClass(\App::class)]
class AbstractModelTest extends TestCase
{
    private static AbstractModel $model;
    private string $user_email='test_email';
    public static function setUpBeforeClass(): void
    {
        \App::initEnvVariables('.env-dev');
        self::$model = new UserModel();
    }

    /*
     * @covers AbstractModel::insert
     * */
    public function testInsert()
    {
        $user_id = self::$model->insert(['email' => 'test_email']);
        $this->assertIsInt($user_id);
        $user = self::$model->select_one(['email'], [
            ['id', '=', $user_id]
        ]);
        $this->assertIsArray($user);
        $this->assertArrayHasKey('email', $user);
        $this->assertEquals($this->user_email, $user['email']);
        $user_id = self::$model->insertOrIgnore(['email' => 'test_email']);
        $this->assertEmpty($user_id);
        $user_id = self::$model->insert(['not_found_column' => 'test_email']);
        $this->assertEmpty($user_id);
    }

    public function testUpdate()
    {
        self::$model->update(['is_verified' => 1], [['email', '=', $this->user_email]]);
        $user = self::$model->select_one(['email', 'is_verified'], [
            ['email', '=', $this->user_email]
        ]);
        $this->assertTrue((bool)$user['is_verified']);
        $user_id = self::$model->insertOrIgnore(['email' => 'test_email']);
        $this->assertEmpty($user_id);
    }

    public function testSelectAll()
    {
        $user_id_second = self::$model->insert(['email' => 'test_email2']);
        $user_id_third = self::$model->insert(['email' => 'test_email3']);
        $user = self::$model->select_all(['email',]);
        $this->assertIsArray($user);
        $this->assertGreaterThan(1, sizeof($user));
        self::$model->delete([['email', 'IN', ["test_email2", "test_email3"]]]);
    }

    public function testDelete()
    {
        $result = self::$model->delete([['email', '=', $this->user_email]]);
        $this->assertTrue($result);
        $user = self::$model->select_one(['id'], [
            ['email', '=', $this->user_email]
        ]);
        $this->assertIsNotArray($user);
    }
}
