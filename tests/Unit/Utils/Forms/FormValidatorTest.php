<?php

declare(strict_types=1);

namespace PHPAether\Tests\Unit\Utils\Forms;

use PHPAether\Exceptions\FormValidatorException;
use PHPAether\Utils\Forms\FormValidator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class FormValidatorTest extends TestCase
{
    protected FormValidator $formValidator;
    protected array $FORM = [
        'username'  => 'JohnDoe',
        'email'     => 'example@gmail.com',
        'phone'     => '08134560923',
        'url'       => 'https://www.example.com',
        'desc'      => 'dkdkc fkfk fkfkfk',
        'password'  => 'glddkf#(3',
        'confirm-password'  => 'glddkf#(3',
        'dob'       => '23-08-2012'
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->formValidator = new FormValidator($this->FORM);
    }

    protected function setUpFormFieldValidatorTest(string $field, array $values, array $invalid=[]): array
    {
        $form = [];
        $expected = [];
        foreach($values as $index => $value) {
            $count = $index + 1;
            $key = "{$field}_{$count}";

            $form[$key] = $value;

            if (in_array($index, $invalid)) {
                $expected[$key] = "Invalid $key";
            }
        }

        return [
            new FormValidator($form),
            array_keys($form),
            $expected
        ];
    }

    #[Test]
    public function it_validates_email(): void
    {
        [$formValidator, $fields, $expected] = $this->setUpFormFieldValidatorTest('email',
            [
                'example@gmail.com',
                'example iat com'
            ],
            [1]
        );

        $result = $formValidator->validate(function (FormValidator $validator) use ($fields) {
            $validator->for(...$fields)->email();
        });

        $this->assertSame($expected, $result);
    }

    #[Test]
    public function it_validates_text(): void
    {
        [$formValidator, $fields, $expected] = $this->setUpFormFieldValidatorTest('text',
            [
                'Lorem ipsum some bla bla goes ehre and here too!',
                'Hello, world!'
            ],
            [0, 1]
        );

        $result = $formValidator->validate(function (FormValidator $validator) use ($fields) {
            $validator->for(...$fields)->text(15, 45);
        });

        $this->assertSame($expected, $result);
    }

    #[Test]
    public function it_validates_tel(): void
    {
        [$formValidator, $fields, $expected] = $this->setUpFormFieldValidatorTest('tel',
            [
                '08012345678',
                '+234 813 456 7890',
                '445678'
            ],
            [2]
        );
        $result = $formValidator->validate(function (FormValidator $validator) use ($fields) {
            $validator->for(...$fields)->tel();
        });
        $expected = [
            'tel_3' => 'Invalid tel_3'
        ];

        $this->assertSame($result, $expected);
    }

    #[Test]
    public function it_validates_url(): void
    {
        [$formValidator, $fields, $expected] = $this->setUpFormFieldValidatorTest('url',
            [
                'https://www.example.com',
                'www.example.com'
            ],
            [1]
        );
        $result = $formValidator->validate(function (FormValidator $v) use ($fields) {
            $v->for(...$fields)->url();
        });

        $this->assertSame($expected, $result);
    }

    #[Test]
    public function it_validates_regex(): void
    {
        [$formValidator, $fields, $expected] = $this->setUpFormFieldValidatorTest('regex',
            [
                'Some 20 yrs old',
                '5yr old',
                'hello, world'
            ],
            [2]
        );

        $result = $formValidator->validate(function (FormValidator $validator) use ($fields) {
            $validator->for(...$fields)->regex('/\d ?yrs?/');
        });

        $this->assertSame($expected, $result);
    }

    #[Test]
    public function it_validates_password(): void
    {
        [$formValidator, $fields, $expected] = $this->setUpFormFieldValidatorTest('password',
            [
                'hello',
                'admin123',
                'si$^qw@!--dera'
            ],
            [0]
        );

        $result = $formValidator->validate(function (FormValidator $validator) use ($fields) {
            $validator->for(...$fields)->password();
        });

        $this->assertSame($expected, $result);
    }

    #[Test]
    public function it_validates_matches(): void
    {
        [$formValidator, $fields, $expected] = $this->setUpFormFieldValidatorTest('matches',
            [
                'admin123',
                'Admin123',
            ],
            [0]
        );

        $result = $formValidator->validate(function (FormValidator $validator) {
           $validator->for('matches_1')->matches('matches_2');
        });

        $this->assertSame($expected, $result);
    }

    public function it_validates_date(): void
    {
        [$formValidator, $fields, $expected] = $this->setUpFormFieldValidatorTest('date',
            [
                '2024-12-13',
                '13-12-2024',
                '15-08-2001',
                '08-06-2012',
                '03-04-2005',
                '05-04-1980'
            ],
            [1, 3, 5]
        );

        $result = $formValidator->validate(function (FormValidator $validator) {
           $validator->for('date_1', 'date_2')->date();
           $validator->for('date_3', 'date_4')->date('d-m-Y', before: '01-01-2015', after: '01-01-2010');
           $validator->for('date_5', 'date_6')->date(minAge: 18, maxAge: 40);
        });

        $this->assertSame($expected, $result);
    }

    public function it_validates_form(): void
    {
        
        $form = [
            'username'  => 'JohnDoe',
            'email'     => 'example@gmail.com',
            'phone'     => '08134560923',
            'url'       => 'https://www.example.com',
            'desc'      => 'dkdkc fkfk fkfkfk',
            'password'  => 'glddkf#(3',
            'confirm-password'  => 'glddkf#(3',
            'dob'       => '23-08-2012'
        ];

        $validationResult = (new FormValidator($form))->validate(function (FormValidator $validator) {

            try {
                $validator->for('email')->email()
                    ->for('username')->text(3, 15)
                    ->for('phone')->tel(errorMsg: "Invalid phone number provided")
                    ->for('url')->url(errorMsg: fn ($value, $field) => "URL ($value) provided is not valid $field")
                    ->for('desc', 'description')->regex("/[a-zA-Z0-9 ]+/")->text(20)
                    ->for('password')->password()->matches('confirm-password', regex: null)
                    ->for('dob')->date('d-m-Y');
            } catch (FormValidatorException $e) {


            }

        });

        $expected = [];
        $this->assertSame($expected, $validationResult);

    }
    
}

