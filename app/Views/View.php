<?php

declare(strict_types=1);

namespace PHPAether\Views;

use PHPAether\Exceptions\FileNotFoundException;

class View
{

    private const DEFAULT_DIR = "pages";

    protected string $viewFile;

    public function __construct(string $viewName, ?string $defaultDir=null)
    {
        $viewFile = ($defaultDir ?? "/app/views/") . static::DEFAULT_DIR . "/$viewName.view.php";
        if (! file_exists($viewFile)) {
            throw new FileNotFoundException(
                "The provided view file: $viewFile is not found"
            );
        }

        $this->viewFile = $viewFile;
    }

    public function render(array $props=[]): string
    {
        // Get the content of the file
        $view = file_get_contents($this->viewFile);

        // Extract props
        $view = preg_replace_callback(
            "/\{ *([a-zA-Z_][a-zA-Z0-9_]+) *\}/",
            fn(array $match) => $props[$match[1]] ?? throw new ViewException(
                "Undefined prop: {$match[1]}"
            ),
            $view
        );

        return $view;
    }
}
