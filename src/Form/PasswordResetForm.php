<?php

namespace Riddlestone\Brokkr\Users\Mvc\Form;

use Laminas\Filter\StringTrim;
use Laminas\Form\Element\Email;
use Laminas\Form\Element\Password;
use Laminas\Form\Element\Submit;
use Laminas\Form\Form;
use Laminas\InputFilter\InputFilterProviderInterface;
use Laminas\Validator\Identical;
use Laminas\Validator\StringLength;

class PasswordResetForm extends Form implements InputFilterProviderInterface
{
    protected $requiredEmailAddress;

    public function __construct($name = null, $options = [])
    {
        parent::__construct($name, $options);
        $this->requiredEmailAddress = $options['email_address'] ?? null;
    }

    public function getRequiredEmailAddress()
    {
        return $this->requiredEmailAddress;
    }

    public function init()
    {
        $this->add(
            [
                'name' => 'email_address',
                'type' => Email::class,
                'options' => [
                    'label' => 'Email Address',
                ],
            ]
        );

        $this->add(
            [
                'name' => 'password',
                'type' => Password::class,
                'options' => [
                    'label' => 'New Password',
                ],
            ]
        );

        $this->add(
            [
                'name' => 'repeat_password',
                'type' => Password::class,
                'options' => [
                    'label' => 'Retype New Password',
                ],
            ]
        );

        $this->add(
            [
                'name' => 'submit',
                'type' => Submit::class,
                'attributes' => [
                    'value' => 'Reset Password',
                ],
            ]
        );
    }

    /**
     * @inheritDoc
     */
    public function getInputFilterSpecification()
    {
        return [
            'email_address' => [
                'required' => true,
                'filters' => [
                    [
                        'name' => StringTrim::class,
                    ]
                ],
                'validators' => [
                    [
                        'name' => Identical::class,
                        'options' => [
                            'token' => $this->getRequiredEmailAddress(),
                        ],
                    ],
                ],
            ],
            'password' => [
                'required' => true,
                'filters' => [
                    [
                        'name' => StringTrim::class,
                    ],
                ],
                'validators' => [
                    [
                        'name' => StringLength::class,
                        'options' => [
                            'min' => 8,
                        ],
                    ],
                ],
            ],
            'repeat_password' => [
                'required' => true,
                'filters' => [
                    [
                        'name' => StringTrim::class,
                    ],
                ],
                'validators' => [
                    [
                        'name' => Identical::class,
                        'options' => [
                            'token' => 'password',
                        ],
                    ],
                ],
            ],
        ];
    }
}
