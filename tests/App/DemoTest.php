<?php
/*
 * @Date         : 2022-03-02 14:49:25
 * @LastEditors  : Jack Zhou <jack@ks-it.co>
 * @LastEditTime : 2022-03-02 17:22:16
 * @Description  : 
 * @FilePath     : /recruitment-php-code-test/tests/App/DemoTest.php
 */

namespace Test\App;

use App\App\Demo;
use App\Service\AppLogger;
use App\Util\HttpRequest;
use PHPUnit\Framework\TestCase;


class DemoTest extends TestCase {

    private $oService;
    private $oRes;

    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->oService = new Demo(new AppLogger(), new HttpRequest());
    }

    public function test_foo() {
        $this->oRes = $this->oService->foo();
        $this->assertEquals('bar', $this->oRes);
    }

    public function test_get_user_info() {
        $this->oRes = $this->oService->get_user_info();
        $this->assertIsArray($this->oRes);
        $this->assertArrayHasKey('id', $this->oRes);
        $this->assertArrayHasKey('username', $this->oRes);
        $this->assertIsInt($this->oRes['id']);
        $this->assertIsString($this->oRes['username']);
    }
}