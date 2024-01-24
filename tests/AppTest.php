<?php


use PHPUnit\Framework\TestCase;

#[\PHPUnit\Framework\Attributes\CoversClass(App::class)]
class AppTest extends TestCase
{
    public function testInitEnvVariables()
    {
        $result = App::initEnvVariables('.env-dev');
        $env_vars = parse_ini_file('.env-dev');
        foreach ($env_vars as $env_var=>$value) {
            $this->assertArrayHasKey($env_var, $_ENV);
            $this->assertEquals($value, $_ENV[$env_var]);
        }
        $this->assertTrue($result);
    }
}
