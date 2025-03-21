<?php

declare(strict_types=1);

namespace PHPAether\Tests\Unit\Utils\Forms;

use PHPAether\Utils\Forms\FormValidator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class FormValidatorTest extends TestCase
{

    protected function setUp(): void
    {
        parent::setUp();
    }

    #[Test]
    public function it_validates_form(): void
    {
        
        $form = [
            'username'  => 'JohnDoe',
            'email'     => 'example@gmail.com',
            'phone'     => '08134560923',
            'url'       => 'www.example.com',
            'desc'      => 'dkdkc fkfk fkfkfk',
            'password'  => 'glddkf#(3',
            'confirm-password'  => 'glddkf#(3',
            'dob'       => '23-08-2012'
        ];

        $validationResult = (new FormValidator($form))->validate(function (FormValidator $validator) {

            $validator->for('email')->email();
            $validator->for('username')->text(3, 15);
            $validator->for('phone')->tel(errorMsg: "Invalid phone number provided");
            $validator->for('url')->url(errorMsg: fn ($value, $field) => "URL ($value) provided is not valid $field");
            $validator->for('desc', 'description')->regex("/[a-zA-Z0-9 ]+/")->text(20);
            $validator->for('password')->password()->matches('confirm-password', regex: null);

        });

        $expected = [];
        $this->assertSame($expected, $validationResult);

    }
    
}

