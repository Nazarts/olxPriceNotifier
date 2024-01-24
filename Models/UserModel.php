<?php

namespace Models;

require_once 'AbstractModel.php';

class UserModel extends AbstractModel
{
    protected string $table_name = 'users';

    public function activate_user(int $user_id): \mysqli_result|bool
    {
        return $this->update(['is_verified' => 1], [['id', '=', $user_id]]);
    }
}