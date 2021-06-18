<?php

namespace Riddlestone\Brokkr\Users\Mvc\Test\Unit\Form;

use PHPUnit\Framework\TestCase;
use Riddlestone\Brokkr\Users\Mvc\Form\PasswordResetForm;

class PasswordResetFormTest extends TestCase
{
    public function testInit()
    {
        $form = new PasswordResetForm(null, ['email_address' => 'someone@example.com']);
        $form->init();
        $this->assertCount(4, $form->getElements());
        $this->assertEquals('someone@example.com', $form->getRequiredEmailAddress());
    }

    public function isValidData()
    {
        return [
            'no data' => [
                'options' => [
                    'email_address' => 'someone@example.com',
                ],
                'data' => [],
                'valid' => false,
                'errors' => [
                    'email_address' => [
                        'isEmpty' => 'Value is required and can\'t be empty',
                    ],
                    'password' => [
                        'isEmpty' => 'Value is required and can\'t be empty',
                    ],
                    'repeat_password' => [
                        'isEmpty' => 'Value is required and can\'t be empty',
                    ],
                ],
            ],
            'missing email address' => [
                'options' => [
                    'email_address' => 'someone@example.com',
                ],
                'data' => [
                    'password' => 'MySuperS3cretP@ssword',
                    'repeat_password' => 'MySuperS3cretP@ssword',
                ],
                'valid' => false,
                'errors' => [
                    'email_address' => [
                        'isEmpty' => 'Value is required and can\'t be empty',
                    ],
                ],
            ],
            'missing repeat password' => [
                'options' => [
                    'email_address' => 'someone@example.com',
                ],
                'data' => [
                    'email_address' => 'someone@example.com',
                    'password' => 'MySuperS3cretP@ssword',
                ],
                'valid' => false,
                'errors' => [
                    'repeat_password' => [
                        'isEmpty' => 'Value is required and can\'t be empty',
                    ],
                ],
            ],
            'mismatched passwords' => [
                'options' => [
                    'email_address' => 'someone@example.com',
                ],
                'data' => [
                    'email_address' => 'someone@example.com',
                    'password' => 'MySuperS3cretP@ssword',
                    'repeat_password' => 'ADifferentSuperS3cretP@ssword',
                ],
                'valid' => false,
                'errors' => [
                    'repeat_password' => [
                        'notSame' => 'The two given tokens do not match',
                    ],
                ],
            ],
            'short password' => [
                'options' => [
                    'email_address' => 'someone@example.com',
                ],
                'data' => [
                    'email_address' => 'someone@example.com',
                    'password' => 'short',
                    'repeat_password' => 'short',
                ],
                'valid' => false,
                'errors' => [
                    'password' => [
                        'stringLengthTooShort' => 'The input is less than 8 characters long',
                    ],
                ],
            ],
            'wrong password' => [
                'options' => [
                    'email_address' => 'someone@example.com',
                ],
                'data' => [
                    'email_address' => 'someone.else@example.com',
                    'password' => 'MySuperS3cretP@ssword',
                    'repeat_password' => 'MySuperS3cretP@ssword',
                ],
                'valid' => false,
                'errors' => [
                    'email_address' => [
                        'notSame' => 'The two given tokens do not match',
                    ],
                ],
            ],
            'valid' => [
                'options' => [
                    'email_address' => 'someone@example.com',
                ],
                'data' => [
                    'email_address' => 'someone@example.com',
                    'password' => 'MySuperS3cretP@ssword',
                    'repeat_password' => 'MySuperS3cretP@ssword',
                ],
                'valid' => true,
                'errors' => [],
            ],
        ];
    }

    /**
     * @dataProvider isValidData
     * @param array $options
     * @param array $data
     * @param bool $valid
     */
    public function testIsValid(array $options, array $data, bool $valid, array $errors)
    {
        $form = new PasswordResetForm(null, $options);
        $form->init();
        $form->setData($data);
        $this->assertEquals($valid, $form->isValid());
        if (!$valid) {
            $this->assertEquals($errors, $form->getMessages());
        }
    }
}
