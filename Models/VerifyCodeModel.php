<?php
namespace Models;

require_once 'AbstractModel.php';

class VerifyCodeModel extends AbstractModel
{
    protected string $table_name = 'verify_codes';

    public function insert(array $data): int|string|null
    {
        if (array_key_exists('user_id', $data) && array_key_exists('verify_code', $data)) {
            $user_id = $this->select_one(['id'], [
                ['user_id', '=', $data['user_id']]
            ]);
            // If code for user exist, update it
            if (!$user_id === false) {
                $result = $this->update($data, [
                    ['user_id', '=', $user_id]
                ]);
                if ($result) {
                    return $user_id;
                }
                return $result;
            }
            return parent::insert($data);
        }
    }
}