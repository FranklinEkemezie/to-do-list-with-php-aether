<?php
declare(strict_types=1);

namespace PHPAether\Views;

use PHPAether\Exceptions\ViewException;
use PHPAether\Interfaces\RenderableBodyInterface;

abstract class View extends RenderableBodyInterface
{
    private const DEFAULT_DIR = 'pages';

    private const ASSETS = [
        'css'   => [],
        'js'    => []
    ];

    public function __construct(
        public readonly array $props=[]
    )
    {
    }

    abstract function render(): string;

    public function htmlHead(): string
    {
        $cssTags = join("\n", array_map(function (string $cssFile): string {
            return "<link type='text/css' rel='stylesheet' href='$cssFile' />";
        }, self::ASSETS['css']));
        $jsTags = join("\n", array_map(function (string $jsFile): string {
            return "<script type='text/javascript' src='$jsFile' defer></script>";
        }, self::ASSETS['js']));

        return '';
    }

    public function toString(): string
    {
        return $this->render();
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    /**
     * @throws ViewException
     */
    public function __get(string $name)
    {
        if (empty($this->props[$name])) {
            throw new ViewException('Invalid prop: ' . $name);
        }

        return $this->props[$name];
    }
}