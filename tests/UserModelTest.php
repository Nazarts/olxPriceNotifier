<?php

namespace tests;

use Models\UserModel;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(UserModel::class)]
class UserModelTest extends TestCase
{
    public function testActivateUser()
    {
        $user_model = new UserModel();
        $user_id = $user_model->insert(['email' => 'test']);
        $result = $user_model->activate_user($user_id);
        $user = $user_model->select_one(null, [['id', '=', $user_id]]);
        $this->assertTrue((bool)$user['is_verified']);
        $this->assertTrue($result);
        $user_model->delete([['id', '=', $user_id]]);
    }
}
