<?php

namespace Riddlestone\Brokkr\Users\Mvc\Form;

use Laminas\Filter\StringTrim;
use Laminas\Form\Element\Email;
use Laminas\Form\Element\Submit;
use Laminas\Form\Form;
use Laminas\InputFilter\InputFilterProviderInterface;

class RequestPasswordResetForm extends Form implements InputFilterProviderInterface
{
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
                'name' => 'submit',
                'type' => Submit::class,
                'attributes' => [
                    'value' => 'Request Password Reset',
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
                    ],
                ],
            ],
        ];
    }
}
