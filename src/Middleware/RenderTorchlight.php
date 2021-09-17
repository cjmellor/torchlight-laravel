<?php

namespace Torchlight\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Torchlight\Blade\BladeManager;

class RenderTorchlight
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if ($response instanceof JsonResponse && class_exists('\\Livewire\\Livewire') && \Livewire\Livewire::isLivewireRequest()) {
            return $this->handleLivewireRequest($response);
        }

        // Must be a regular, HTML response.
        if (!$response instanceof Response || !Str::contains($response->headers->get('content-type'), 'html')) {
            return $response;
        }

        return BladeManager::renderResponse($response);
    }

    protected function handleLivewireRequest(JsonResponse $response)
    {
        if (!BladeManager::getBlocks()) {
            return $response;
        }

        $data = $response->getData();

        $html = BladeManager::renderContent(data_get($data, 'effects.html'));

        data_set($data, 'effects.html', $html);

        return $response->setData($data);
    }
}
