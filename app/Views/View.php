<?php

declare(strict_types=1);

namespace PHPAether\Views;

use PHPAether\Exceptions\FileNotFoundException;
use PHPAether\Exceptions\NotFoundException\ConfigNotFoundException;
use PHPAether\Exceptions\ViewException;
use PHPAether\Utils\Config;

class View
{

    private const DEFAULT_DIR = "pages";

    protected string $viewFile;

    /**
     * @throws FileNotFoundException When the view file specified cannot be found
     * @throws ViewException When the default view directory is not found in the app config
     */
    public function __construct(string $viewName)
    {
        try { $defaultViewDir = Config::get('DEFAULT_VIEW_DIR'); }
        catch (ConfigNotFoundException) {
            throw new ViewException("Default view directory not found in app configuration");
        }

        $viewSubDir = static::DEFAULT_DIR;
        $viewFile = "{$defaultViewDir}/{$viewSubDir}/{$viewName}.view.php";
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
