<?php

namespace Riddlestone\Brokkr\Users\Mvc\Test\Unit\Form;

use PHPUnit\Framework\TestCase;
use Riddlestone\Brokkr\Users\Mvc\Form\LoginForm;

class LoginFormTest extends TestCase
{
    public function testInit()
    {
        $form = new LoginForm();
        $form->init();
        $this->assertCount(3, $form->getElements());
    }

    public function isValidData()
    {
        return [
            [
                'data' => [],
                'valid' => false,
            ],
            [
                'data' => [
                    'email_address' => 'someone@example.com',
                    'password' => 'foobar',
                ],
                'valid' => true,
            ],
            [
                'data' => [
                    'email_address' => '',
                    'password' => 'foobar',
                ],
                'valid' => false,
            ],
            [
                'data' => [
                    'email_address' => 'email-address-without-at',
                    'password' => 'foobar',
                ],
                'valid' => false,
            ],
            [
                'data' => [
                    'email_address' => 'someone@example.com',
                    'password' => '',
                ],
                'valid' => false,
            ],
        ];
    }

    /**
     * @dataProvider isValidData
     * @param array $data
     * @param bool $valid
     */
    public function testIsValid(array $data, bool $valid)
    {
        $form = new LoginForm();
        $form->init();
        $form->setData($data);
        $this->assertEquals($valid, $form->isValid());
    }
}
