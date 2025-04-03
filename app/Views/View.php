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
        $viewFile = ("$defaultDir/" ?? "/app/views/") . static::DEFAULT_DIR . "/$viewName.view.php";
        if (! file_exists($viewFile)) {
            throw new FileNotFoundException(
                "The provided view file: $viewFile is not found"
            );
        }

        $this->viewFile = $viewFile;
    }

    protected static function parseVariables(string $template, array $props): string
    {

        // Matches
        $abc        = "[a-zA-Z]";
        $abc_       = "[a-zA-Z_]";
        $abc123_    = "[a-zA-Z0-9_]";
        $varName    = "({$abc}{$abc123_}+)";

        $regex = "/\{\{ *$varName(\.$varName)? *\}\}/";
        $replace_callback = function (array $match) use ($props) {
            if (count($match) === 4) {
                [ , $propName, , $propProperty] = $match;
                $value = $props[$propName][$propProperty] ?? null;
            } else {
                [ , $propName] = $match;
                $value = $props[$propName] ?? null;
            }

            if ($value === null && false) {
                throw new ViewException("Undefined prop: {$match[1]}");
            }

            return $value;
            return "<?php echo '$value'; ?>";
        };

        return preg_replace_callback($regex, $replace_callback, $template);
    }

    protected static function parseForLoops(string $template, array $props): string
    {

        // Matches
        $abc        = "[a-zA-Z]";
        $abc_       = "[a-zA-Z_]";
        $abc123_    = "[a-zA-Z0-9_]";
        $varName    = "({$abc}{$abc123_}*)";

        $regex = "/@for *\(($varName:)? $varName of $varName*\) \{.+\}/";
        $regex = "/@for *\(($varName\:)?/";
        $replace_callback = function (array $match) use ($props) {
            var_dump($match);
            if (count($match) === 4) {
                [ , $propName, , $propProperty] = $match;
                $value = $props[$propName][$propProperty] ?? null   ;
            } else {
                [ , $propName] = $match;
                $value = $props[$propName] ?? null;
            }

            if ($value === null && false) {
                throw new ViewException("Undefined prop: {$match[1]}");
            }

            return $value;
            return "<?php echo '$value'; ?>";
        };

        return preg_replace_callback($regex, $replace_callback, $template);

    }

    public function render(array $props=[]): string
    {
        // Get the content of the file
        $view = file_get_contents($this->viewFile);

        // Extract props
        $view = static::parseForLoops($view, $props);
        // $view = static::parseVariables($view, $props);

        return $view;
    }
}
