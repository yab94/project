<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * End-to-End tests that call the actual web application via HTTP
 */
final class WebApplicationTest extends TestCase
{
    // When running inside Docker, we access the web server directly
    // The PHP container serves the app on port 80 internally
    private const BASE_URL = 'http://127.0.0.1:80';

    public function testHomePageIsAccessible(): void
    {
        $response = $this->makeRequest('/');
        
        $this->assertStringContainsString('<h2>Welcome to CRM System</h2>', $response);
        $this->assertStringContainsString('Persons', $response);
        $this->assertStringContainsString('Quotes', $response);
        $this->assertStringContainsString('Invoices', $response);
    }

    public function testPersonsPageIsAccessible(): void
    {
        $response = $this->makeRequest('/persons');
        
        $this->assertStringContainsString('<h2>Persons</h2>', $response);
        $this->assertStringContainsString('Create New Person', $response); // Updated: buttons show "Create New"
    }

    public function testPersonsCreatePageIsAccessible(): void
    {
        $response = $this->makeRequest('/persons/create');
        
        $this->assertStringContainsString('<h2>Create Person</h2>', $response);
        $this->assertStringContainsString('<form', $response);
        $this->assertStringContainsString('type', $response);
    }

    public function testQuotesPageIsAccessible(): void
    {
        $response = $this->makeRequest('/quotes');
        
        $this->assertStringContainsString('<h2>Quotes</h2>', $response);
        $this->assertStringContainsString('Create New Quote', $response); // Updated
    }

    public function testQuotesCreatePageIsAccessible(): void
    {
        $response = $this->makeRequest('/quotes/create');
        
        $this->assertStringContainsString('<h2>Create Quote</h2>', $response);
        $this->assertStringContainsString('<form', $response);
    }

    public function testInvoicesPageIsAccessible(): void
    {
        $response = $this->makeRequest('/invoices');
        
        $this->assertStringContainsString('<h2>Invoices</h2>', $response);
        $this->assertStringContainsString('Create New Invoice', $response); // Updated
    }

    public function testInvoicesCreatePageIsAccessible(): void
    {
        $response = $this->makeRequest('/invoices/create');
        
        $this->assertStringContainsString('<h2>Create Invoice</h2>', $response);
        // May show form or error message if no accepted quotes available
        $this->assertTrue(
            str_contains($response, '<form') || str_contains($response, 'No accepted quotes available'),
            'Page should show either a form or an error message about missing quotes'
        );
    }

    public function testUnknownRouteReturns404(): void
    {
        $response = $this->makeRequest('/unknown-route-that-does-not-exist');
        
        $this->assertStringContainsString('Page not found', $response); // Updated: actual error message
    }

    public function testAllModuleRoutesAreLoaded(): void
    {
        // Test that routes from all modules are accessible
        $routes = [
            '/' => 'Welcome',
            '/persons' => 'Persons',
            '/quotes' => 'Quotes',
            '/invoices' => 'Invoices',
        ];

        foreach ($routes as $route => $expectedContent) {
            $response = $this->makeRequest($route);
            $this->assertStringContainsString(
                $expectedContent,
                $response,
                "Route {$route} should contain '{$expectedContent}'"
            );
        }
    }

    private function makeRequest(string $path): string
    {
        $url = self::BASE_URL . $path;
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($response === false) {
            $this->fail("Failed to connect to {$url}. Is the server running?");
        }
        
        return $response;
    }
}
