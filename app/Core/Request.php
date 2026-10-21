<?php
namespace App\Core;

class Request
{
    private string $method;
    private string $uri;
    private array $query;
    private array $body;
    private array $files;
    private array $server;

    public function __construct()
    {
        $this->server = $_SERVER;
        $this->method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        
        $rawUri = $_SERVER['REQUEST_URI'] ?? '/';
        $parsedUri = parse_url($rawUri, PHP_URL_PATH) ?? '/';
        $this->uri = '/' . trim($parsedUri, '/');
        
        $this->query = $_GET;
        $this->body = $_POST;
        $this->files = $_FILES;

        // Support JSON requests if Content-Type is application/json
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (str_contains($contentType, 'application/json')) {
            $rawBody = file_get_contents('php://input');
            $jsonData = json_decode($rawBody, true);
            if (is_array($jsonData)) {
                $this->body = array_merge($this->body, $jsonData);
            }
        }
    }

    public function method(): string
    {
        return $this->method;
    }

    public function uri(): string
    {
        return $this->uri;
    }

    public function isPost(): bool
    {
        return $this->method === 'POST';
    }

    public function isGet(): bool
    {
        return $this->method === 'GET';
    }

    public function isAjax(): bool
    {
        return (!empty($this->server['HTTP_X_REQUESTED_WITH']) && 
            strtolower($this->server['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') ||
            (str_contains($this->server['HTTP_ACCEPT'] ?? '', 'application/json'));
    }

    public function input(string $key, mixed $default = null): mixed
    {
        return $this->body[$key] ?? $this->query[$key] ?? $default;
    }

    public function all(): array
    {
        return array_merge($this->query, $this->body);
    }

    public function only(array $keys): array
    {
        $all = $this->all();
        $filtered = [];
        foreach ($keys as $k) {
            $filtered[$k] = $all[$k] ?? null;
        }
        return $filtered;
    }

    public function file(string $key): ?array
    {
        return $this->files[$key] ?? null;
    }

    public function clientIp(): string
    {
        return $this->server['HTTP_X_FORWARDED_FOR'] ?? $this->server['REMOTE_ADDR'] ?? '127.0.0.1';
    }
}
