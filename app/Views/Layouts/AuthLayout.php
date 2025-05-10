<?php

namespace App\Views\Layouts;

use App\Views\Components\AuthInputField;
use PHPAether\Views\Layout;

abstract class AuthLayout extends Layout
{

    // Define abstract classes for slots
    public abstract function pageTitle(): string;
    public abstract function inputFields(): string;
    public abstract function formTitle(): string;
    public abstract function formSubtitle(): string;
    public abstract function footnote(): array;

    public function render(): string
    {

        return <<<HTML
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>To-Do List - {$this->pageTitle()}</title>

                <style>
                    html, body {
                        margin: 0;
                        padding: 0;
                    }

                    * {
                        box-sizing: border-box;
                    }

                    :root {
                        --theme-primary: rgb(26, 97, 250);
                    }

                    a { 
                        color: var(--theme-primary);
                        text-decoration: none;
                    }

                    body {
                        display: flex;
                        flex-direction: row;
                        align-items: center;
                        justify-content: center;
                        font-family: "Roboto", sans-serif;
                        font-size: 1rem;
                        height: 100vh;
                    }

                    section {
                        display: block;
                        width: 100%;
                        max-width: 400px;
                        padding: .8rem 1.6rem;
                        border-radius: .48rem;
                        box-shadow: 0 8px 24px 0 rgba(0, 0, 0, 0.08), 0 8px 24px 0 rgba(0, 0, 0, 0.08);
                    }

                    header {
                        text-align: center;
                    }

                    .input-field {
                        margin: .24rem 0;
                        padding: .32rem 0;
                    }

                    .input-field label {
                        display: block;
                        margin: .32rem 0;
                    }

                    .input-field input {
                        display: block;
                        width: 100%;
                        font-size: .98rem;
                        padding: .64rem .4rem;
                        background-color: transparent;
                        outline: none;
                        border: 1px solid rgba(0, 0, 0, 0.2);
                        border-radius: 0.4rem;
                    }

                    .input-field input:focus {
                        border-color: rgba(0, 0, 0, 0.4);
                    }

                    .submit-field {
                        text-align: center;
                        margin: .32rem 0;
                        margin-top: 1.2rem;
                        padding: .32rem 0;
                    }

                    .submit-field button[type="submit"] {
                        display: block;
                        width: 100%;
                        border: none;
                        color: white;
                        background-color:rgb(26, 97, 250);
                        font-size: 1rem;
                        padding: .24rem .4rem;
                        border-radius: 0.4rem;
                        padding: .64rem;
                        cursor: pointer;
                    }

                    .submit-field button[type="submit"]:hover {
                        background-color:rgb(24, 91, 235);
                    }


                </style>
            </head>
            <body>
                <section>
                    <!-- Header -->
                    <header>
                        <div>
                            <h2>{$this->formTitle()}</h2>
                            <p>{$this->formSubtitle()}</p>
                        </div>
                    </header>

                    <!-- Main -->
                    <main>
                        <form action="/login" method="POST">
                            <!-- Input Fields slots -->
                            {$this->inputFields()}

                            <!-- Submit -->
                            <div class="submit-field">
                                <button type="submit">{$this->pageTitle()}</button>
                                <div>
                                    <p>{$this->footnote()[0]} <a href="{$this->footnote()[1]}">{$this->footnote()[2]}</a></p>
                                </div>
                            </div>
                        </form>
                    </main>

                    <!-- Footer -->
                    <footer>
                        
                    </footer>
                </section>
            </body>
            </html>
        HTML;
    }
}
