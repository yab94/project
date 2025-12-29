<?php

declare(strict_types=1);

namespace App\Core\Infrastructure\Web\View;

/**
 * View class handles template rendering with output buffering
 * 
 * Usage:
 * 
 * // Simple view without layout
 * $view = new View('crm/person/index', ['persons' => $persons]);
 * echo $view->render();
 * 
 * // View with layout composition
 * $contentView = new View('crm/person/index', ['persons' => $persons]);
 * $layoutView = new View('layout/default', ['content' => $contentView]);
 * echo $layoutView->render();
 */
final class View
{
    private string $viewPath;
    private array $data;
    private string $rootDir;

    public function __construct(string $view, array $data = [])
    {
        $this->data = $data;
        $this->rootDir = __DIR__ . '/../../../../../';  // Go to project root
        $this->viewPath = $this->resolveViewPath($view);
    }

    /**
     * Render the view and return the output as string
     */
    public function render(): string
    {
        if (!file_exists($this->viewPath)) {
            return "View not found: {$this->viewPath}";
        }

        // Extract data to make variables available in the view
        extract($this->data);

        // Start output buffering
        ob_start();
        
        try {
            require $this->viewPath;
            return ob_get_clean();
        } catch (\Throwable $e) {
            ob_end_clean();
            throw $e;
        }
    }

    /**
     * Resolve view path from view name
     */
    private function resolveViewPath(string $view): string
    {
        // Check if it's a module view pattern: 'module/resource/action'
        if (preg_match('#^([a-z]+)/([a-z_]+)/([a-z_]+)$#i', $view, $matches)) {
            [, $modulePrefix, $resource, $action] = $matches;
            
            // Convert module prefix to PascalCase
            $module = ucfirst(strtolower($modulePrefix));
            // Special handling for acronyms (all uppercase if 3 chars or less)
            if (strlen($modulePrefix) <= 3) {
                $module = strtoupper($modulePrefix);
            }
            
            return $this->rootDir . "src/{$module}/Infrastructure/Web/View/{$resource}/{$action}.php";
        }
        
        // Check if it's a layout view: 'layout/name'
        if (preg_match('#^layout/([a-z_]+)$#i', $view, $matches)) {
            [, $layoutName] = $matches;
            return $this->rootDir . "src/Layout/Infrastructure/Web/View/layout/{$layoutName}.php";
        }
        
        // Fallback for other views in Layout module
        return $this->rootDir . 'src/Layout/Infrastructure/Web/View/' . $view . '.php';
    }

    /**
     * Magic method to output view directly
     */
    public function __toString(): string
    {
        try {
            return $this->render();
        } catch (\Throwable $e) {
            return "Error rendering view: " . $e->getMessage();
        }
    }
}
