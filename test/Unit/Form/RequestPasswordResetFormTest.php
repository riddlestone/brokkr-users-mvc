<?php

namespace Riddlestone\Brokkr\Users\Mvc\Test\Unit\Form;

use PHPUnit\Framework\TestCase;
use Riddlestone\Brokkr\Users\Mvc\Form\RequestPasswordResetForm;

class RequestPasswordResetFormTest extends TestCase
{
    public function testInit()
    {
        $form = new RequestPasswordResetForm();
        $form->init();
        $this->assertCount(2, $form->getElements());
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
                ],
                'valid' => true,
            ],
            [
                'data' => [
                    'email_address' => '',
                ],
                'valid' => false,
            ],
            [
                'data' => [
                    'email_address' => 'email-address-without-at',
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
        $form = new RequestPasswordResetForm();
        $form->init();
        $form->setData($data);
        $this->assertEquals($valid, $form->isValid());
    }
}
