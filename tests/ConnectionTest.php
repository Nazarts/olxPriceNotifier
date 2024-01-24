<?php

namespace DB;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Connection::class)]
class ConnectionTest extends TestCase
{
    private Connection $connection;
    protected function setUp(): void
    {
        $this->connection = new Connection();
    }

    public function testInit()
    {
        $this->assertNotEmpty($this->connection);
    }

    public function testCRUD()
    {
        $email = 'new_email';
        $result = $this->connection->insert('users', ['email' => $email]);
        $this->assertTrue($result);
        $email_filter = [['email', '=', $email]];
        $result = $this->connection->update('users', ['is_verified' => 1], $email_filter);
        $this->assertTrue($result);
        $user = $this->connection->select_one('users', ['id', 'email', 'is_verified'], $email_filter);
        $this->assertEquals($user['email'], $email);
        $this->assertTrue((bool)$user['is_verified']);
        $result = $this->connection->delete('users', $email_filter);
        $this->assertTrue($result);
        $user = $this->connection->select_one('users', ['id', 'email', 'is_verified'], $email_filter);
        $this->assertEmpty($user);

        // Testing of select all
        $test_mail1 = 'test_email1';
        $test_mail2 = 'test_email2';
        $user_id = $this->connection->insert('users', ['email' => $test_mail1]);
        $this->assertEquals($user_id, $this->connection->last_insert_id());
        $this->connection->insert('users', ['email' => $test_mail2]);
        $user = $this->connection->select_all('users', ['email',]);
        $this->assertIsArray($user);
        $this->assertGreaterThan(1, sizeof($user));
        $this->connection->delete('users', [['email', 'IN', [$test_mail1, $test_mail2]]]);
    }

}
